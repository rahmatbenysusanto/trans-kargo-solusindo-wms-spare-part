<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use App\Models\Inbound;
use App\Models\InboundDetail;
use App\Models\Inventory;
use App\Models\InventoryDetail;
use App\Models\Product;
use App\Models\ProductGroup;
use App\Models\Client;
use App\Models\StorageZone;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\View\View;

class InboundController extends Controller
{
    public function receiving(Request $request): View
    {
        $inbound = Inbound::latest()
            ->when($request->client_id, function ($query) use ($request) {
                return $query->where('client_id', $request->client_id);
            })
            ->when($request->category, function ($query) use ($request) {
                return $query->where('category', $request->category);
            })
            ->when($request->request_type, function ($query) use ($request) {
                return $query->where('request_type', $request->request_type);
            })
            ->when($request->search, function ($query) use ($request) {
                return $query->where(function ($q) use ($request) {
                    $q->where('number', 'like', '%' . $request->search . '%')
                        ->orWhere('rma_number', 'like', '%' . $request->search . '%')
                        ->orWhere('itsm_number', 'like', '%' . $request->search . '%')
                        ->orWhere('vendor', 'like', '%' . $request->search . '%');
                });
            })
            ->paginate(10);

        $categories = ['New PO', 'Spare from/to Replacement', 'Spare from/to Loan', 'Faulty', 'RMA', 'Spare Write-off', 'Spare Migration'];
        $requestTypes = ['New PO', 'RMA', 'Loan', 'Spare Write Off', 'Spare Migration'];
        $clients = Client::all();
        $title = 'Receiving';

        return view('inbound.receiving.index', compact('title', 'inbound', 'categories', 'requestTypes', 'clients'));
    }

    public function staging(): View
    {
        $title = 'Staging';
        return view('inbound.staging.index', compact('title'));
    }

    public function show($id): View
    {
        $inbound = Inbound::with([
            'details.brand',
            'details.storageLevel.zone',
            'details.storageLevel.rak',
            'details.storageLevel.bin',
            'client',
            'invoices'
        ])->findOrFail($id);
        $title = 'Receiving';
        return view('inbound.receiving.show', compact('title', 'inbound'));
    }

    public function showPutAway($id): View
    {
        $inbound = Inbound::with([
            'details.brand',
            'details.storageLevel.zone',
            'details.storageLevel.rak',
            'details.storageLevel.bin',
            'client',
            'invoices'
        ])->findOrFail($id);
        $title = 'Put Away';
        return view('inbound.put-away.show', compact('title', 'inbound'));
    }

    public function approve(Request $request): \Illuminate\Http\JsonResponse
    {
        try {
            $inbound = Inbound::findOrFail($request->post('id'));
            $inbound->update(['status' => 'process qc']);

            return response()->json(['status' => true]);
        } catch (\Throwable $err) {
            return response()->json(['status' => false, 'message' => $err->getMessage()]);
        }
    }

    public function cancel(Request $request): \Illuminate\Http\JsonResponse
    {
        try {
            $inbound = Inbound::findOrFail($request->post('id'));
            $inbound->update(['status' => 'cancel']);

            return response()->json(['status' => true]);
        } catch (\Throwable $err) {
            return response()->json(['status' => false, 'message' => $err->getMessage()]);
        }
    }

    public function putAway(Request $request): View
    {
        $inbound = Inbound::where('status', 'process qc')
            ->when($request->client_id, function ($query) use ($request) {
                return $query->where('client_id', $request->client_id);
            })
            ->when($request->category, function ($query) use ($request) {
                return $query->where('category', $request->category);
            })
            ->when($request->request_type, function ($query) use ($request) {
                return $query->where('request_type', $request->request_type);
            })
            ->when($request->search, function ($query) use ($request) {
                return $query->where(function ($q) use ($request) {
                    $q->where('number', 'like', '%' . $request->search . '%')
                        ->orWhere('receiving_note', 'like', '%' . $request->search . '%')
                        ->orWhere('vendor', 'like', '%' . $request->search . '%');
                });
            })
            ->latest()->paginate(10);

        $categories = ['New PO', 'Spare from/to Replacement', 'Spare from/to Loan', 'Faulty', 'RMA', 'Spare Write-off', 'Spare Migration'];
        $requestTypes = ['New PO', 'RMA', 'Loan', 'Spare Write Off', 'Spare Migration'];
        $clients = Client::all();
        $title = 'Put Away';
        return view('inbound.put-away.index', compact('title', 'inbound', 'categories', 'requestTypes', 'clients'));
    }

    public function processPutAway($id): View
    {
        $inbound = Inbound::with(['details' => function ($query) {
            $query->whereNull('storage_level_id');
        }])->findOrFail($id);

        $storageZone = StorageZone::all();
        $title = 'Put Away';
        return view('inbound.put-away.process', compact('title', 'inbound', 'storageZone'));
    }

    private static function makeCode(string $text, int $length = 3): string
    {
        return strtoupper(substr(Str::slug($text, ''), 0, $length));
    }

    public static function generateUniqueId(): string
    {
        return date('YmdHi') . str_pad(mt_rand(0, 999), 3, '0', STR_PAD_LEFT);
    }

    public static function generateInboundNumber(string $prefix): string
    {
        $date = date('ymd');
        $lastInbound = Inbound::where('number', 'like', "$prefix-$date-%")->latest()->first();
        $lastSerial = 0;
        if ($lastInbound) {
            $lastSerial = (int) substr($lastInbound->number, -3);
        }
        $newSerial = str_pad($lastSerial + 1, 3, '0', STR_PAD_LEFT);
        return "$prefix-$date-$newSerial";
    }

    /**
     * @throws \Throwable
     */
    public function updatePutAway(Request $request): \Illuminate\Http\JsonResponse
    {
        try {
            DB::beginTransaction();
            $products = $request->post('products');
            $storageLevelId = $request->post('storage_level_id');

            foreach ($products as $id) {
                InboundDetail::where('id', $id)->update([
                    'storage_level_id' => $storageLevelId
                ]);

                $inboundDetail = InboundDetail::find($id);
                $inbound = Inbound::find($inboundDetail->inbound_id);
                // Update Inventory Data
                $checkInventory = Inventory::where('serial_number', $inboundDetail->serial_number)->first();
                if ($checkInventory) {
                    $inventoryId = $checkInventory->id;
                    Inventory::where('serial_number', $inboundDetail->serial_number)->update([
                        'storage_level_id'  => $storageLevelId,
                        'qty'               => 1,
                        'status'            => 'available',
                        'condition'         => $inboundDetail->condition,
                        'parent_serial_number' => $inboundDetail->parent_sn ?? ($inboundDetail->old_serial_number ?? $checkInventory->parent_serial_number)
                    ]);
                } else {
                    $brand = Brand::find($inboundDetail->brand_id);
                    $productGroup = ProductGroup::find($inboundDetail->product_group_id);

                    $createInventory = Inventory::create([
                        'unique_id'         => $this->generateUniqueId(),
                        'client_id'         => $inbound->client_id,
                        'storage_level_id'  => $storageLevelId,
                        'product_id'        => $inboundDetail->product_id,
                        'brand_id'          => $inboundDetail->brand_id,
                        'product_group_id'  => $inboundDetail->product_group_id,
                        'qty'               => 1,
                        'part_name'         => $inboundDetail->part_name,
                        'part_number'       => $inboundDetail->part_number,
                        'part_description'  => $inboundDetail->part_description,
                        'serial_number'     => $inboundDetail->serial_number,
                        'parent_serial_number' => $inboundDetail->parent_sn ?? $inboundDetail->old_serial_number,
                        'status'            => 'available',
                        'condition'         => $inboundDetail->condition,
                    ]);
                    $inventoryId = $createInventory->id;
                }

                InventoryDetail::create([
                    'inventory_id'      => $inventoryId,
                    'inbound_detail_id' => $id,
                ]);

                // Record History for Put Away
                \App\Models\InventoryMovement::create([
                    'inventory_id' => $inventoryId,
                    'from_storage_level_id' => null, // Initial placement from staging
                    'to_storage_level_id' => $storageLevelId,
                    'user_id' => Auth::id(),
                    'type' => 'Put Away',
                    'description' => 'Initial Put Away from Staging by ' . Auth::user()->name
                ]);

                // Record to Unified History
                $storage = \App\Models\StorageLevel::with('bin.rak.zone')->find($storageLevelId);
                $locationName = $storage ? "{$storage->bin->rak->zone->name}-{$storage->bin->rak->name}-{$storage->bin->name}-{$storage->name}" : 'N/A';

                \App\Models\InventoryHistory::create([
                    'inventory_id' => $inventoryId,
                    'serial_number' => $inboundDetail->serial_number,
                    'type' => 'Movement',
                    'category' => 'Put Away',
                    'reference_number' => $inbound->number,
                    'description' => 'Item moved from Receiving Staging to ' . $locationName,
                    'user' => Auth::user()->name,
                    'to_location' => $locationName
                ]);
            }

            // Check if all products in this inbound are already put away
            $detail = InboundDetail::findOrFail($products[0]);
            $inboundId = $detail->inbound_id;

            $remaining = InboundDetail::where('inbound_id', $inboundId)
                ->whereNull('storage_level_id')
                ->count();

            if ($remaining === 0) {
                Inbound::where('id', $inboundId)->update(['status' => 'close']);
            }

            DB::commit();
            return response()->json(['status' => true]);
        } catch (\Throwable $err) {
            DB::rollBack();
            Log::info($err->getMessage());
            Log::info($err->getLine());
            return response()->json(['status' => false, 'message' => $err->getMessage()]);
        }
    }

    public function cancelPutAway(Request $request): \Illuminate\Http\JsonResponse
    {
        try {
            DB::beginTransaction();
            $inboundId = $request->post('id');
            $inbound = Inbound::with('details')->findOrFail($inboundId);

            // Fetch items that have NOT been put away
            $pendingDetails = InboundDetail::where('inbound_id', $inboundId)
                ->whereNull('storage_level_id')
                ->get();

            $finishedDetailsCount = InboundDetail::where('inbound_id', $inboundId)
                ->whereNotNull('storage_level_id')
                ->count();

            if ($pendingDetails->isEmpty()) {
                return response()->json(['status' => false, 'message' => 'No pending items found to cancel.']);
            }

            if ($finishedDetailsCount === 0) {
                // If no items were processed yet, cancel the entire Inbound record
                $inbound->update(['status' => 'cancel']);
            } else {
                // Partial cancel: Splitting Inbound (PO/PA)
                $cancelledInbound = $inbound->replicate();
                $cancelledInbound->number = $inbound->number . '-CANCEL';
                $cancelledInbound->qty = $pendingDetails->count();
                $cancelledInbound->status = 'cancel';
                $cancelledInbound->save();

                // Move pending details to the new cancelled inbound
                foreach ($pendingDetails as $detail) {
                    $detail->update(['inbound_id' => $cancelledInbound->id]);

                    // Add historical record to Unified History
                    \App\Models\InventoryHistory::create([
                        'inventory_id' => null,
                        'serial_number' => $detail->serial_number,
                        'type' => 'Inbound',
                        'category' => $cancelledInbound->category,
                        'reference_number' => $cancelledInbound->number,
                        'description' => "Put Away cancelled partially. Moved from {$inbound->number} to {$cancelledInbound->number}",
                        'user' => Auth::user()->name,
                    ]);
                }

                // Update original inbound to reflect only the finished items count
                $inbound->update([
                    'qty' => $finishedDetailsCount,
                    'status' => 'close'
                ]);
            }

            DB::commit();
            return response()->json(['status' => true]);
        } catch (\Throwable $err) {
            DB::rollBack();
            Log::error("Cancel Put Away Error: " . $err->getMessage());
            return response()->json(['status' => false, 'message' => $err->getMessage()]);
        }
    }

    public function create(): View
    {
        $brand = Brand::all();
        $productGroup = ProductGroup::all();
        $client = Client::all();

        $title = "Receiving";
        return view('inbound.receiving.create', compact('title', 'brand', 'productGroup', 'client'));
    }

    public function createSpare(): View
    {
        return $this->create();
    }
    public function createFaulty(): View
    {
        return $this->create();
    }
    public function createRma(): View
    {
        return $this->create();
    }
    public function createRelokasi(): View
    {
        return $this->create();
    }
    public function createNewPO(): View
    {
        return $this->create();
    }

    public function store(Request $request): \Illuminate\Http\JsonResponse
    {
        $request->validate([
            'category'        => 'required',
            'request_type'    => 'nullable',
            'client_id'       => 'required',
            'number'          => 'required', // NTT RN#
            'po_number'       => 'nullable', // Transkargo SN / PO
            'sap_po_number'   => 'nullable',
            'ecapex_number'   => 'nullable',
            'sttb'            => 'required',
            'receivedDate'    => 'required',
            'receivedBy'      => 'required',
            'products'        => 'required|array|min:1',
        ]);

        try {
            DB::beginTransaction();

            $inbound = Inbound::create([
                'category'              => $request->post('category'),
                'request_type'          => $request->post('request_type'),
                'ntt_requestor'         => $request->post('ntt_requestor'),
                'request_date'          => $request->post('request_date'),
                'client_id'             => $request->post('client_id'),
                'client_contact'        => $request->post('client_contact'),
                'pickup_address'        => $request->post('pickup_address'),
                'number'                => $request->post('po_number') ?? self::generateInboundNumber('SPR'),
                'receiving_note'        => $request->post('number'), // NTT RN#
                'sttb'                  => $request->post('sttb'),
                'courier_delivery_note' => $request->post('delivery_note'),
                'courier_invoice'       => $request->post('courier_invoice'),
                'rma_number'            => $request->post('rma_number'),
                'itsm_number'           => $request->post('itsm_number'),
                'sap_po_number'         => $request->post('sap_po_number'),
                'ecapex_number'         => $request->post('ecapex_number'),
                'vendor_dn_number'      => $request->post('vendor_dn_number'),
                'tks_dn_number'         => $request->post('tks_dn_number'),
                'tks_invoice_number'    => $request->post('tks_invoice_number'),
                'vendor'                => $request->post('vendor') ?? 'Internal',
                'qty'                   => count($request->post('products')),
                'received_date'         => $request->post('receivedDate'),
                'received_by'           => $request->post('receivedBy'),
                'status'                => 'new'
            ]);

            $this->storeDetails($inbound, $request->post('products'));

            DB::commit();
            return response()->json(['status' => true]);
        } catch (\Throwable $err) {
            DB::rollBack();
            return response()->json(['status' => false, 'message' => $err->getMessage()]);
        }
    }

    public function storeSpare(Request $request)
    {
        return $this->store($request);
    }
    public function storeNewPO(Request $request)
    {
        return $this->store($request);
    }
    public function storeFaulty(Request $request)
    {
        return $this->store($request);
    }
    public function storeRma(Request $request)
    {
        return $this->store($request);
    }
    public function storeRelokasi(Request $request)
    {
        return $this->store($request);
    }

    private function storeDetails($inbound, $products)
    {
        foreach ($products as $product) {
            $brand = Brand::firstOrCreate(['name' => $product['brand']]);
            $productGroup = ProductGroup::firstOrCreate(['name' => $product['productGroup']]);

            $findProduct = Product::where('part_name', $product['partName'])
                ->where('brand_id', $brand->id)
                ->where('product_group_id', $productGroup->id)
                ->first();

            if ($findProduct) {
                $productId = $findProduct->id;
            } else {
                $createProduct = Product::create([
                    'part_name'         => $product['partName'],
                    'brand_id'          => $brand->id,
                    'product_group_id'  => $productGroup->id,
                ]);
                $productId = $createProduct->id;
            }

            InboundDetail::create([
                'inbound_id'        => $inbound->id,
                'product_id'        => $productId,
                'part_name'         => $product['partName'],
                'part_number'       => $product['partNumber'],
                'description'       => $product['partDescription'] ?? '',
                'qty'               => 1,
                'wh_asset_number'   => $product['whAssetNumber'] ?? null,
                'serial_number'     => $product['serialNumber'],
                'old_serial_number' => $product['oldSerialNumber'] ?? null,
                'parent_sn'         => $product['parentSn'] ?? null,
                'condition'         => $product['condition'],
                'stock_status'      => $product['stockStatus'] ?? 'Available',
                'staging_date'      => $product['stagingDate'] ?? null,
                'storage_level_id'  => null,
                'brand_id'          => $brand->id,
                'product_group_id' => $productGroup->id,
            ]);

            \App\Models\InventoryHistory::create([
                'inventory_id' => null, // Linked later during Put Away
                'serial_number' => $product['serialNumber'],
                'type' => 'Inbound',
                'category' => $inbound->category,
                'reference_number' => $inbound->number,
                'description' => "Received item via {$inbound->category} (Ref: {$inbound->number})" . (isset($product['oldSerialNumber']) && $product['oldSerialNumber'] ? " - Linked to SN: {$product['oldSerialNumber']}" : ""),
                'user' => $inbound->received_by,
            ]);
        }
    }
}
