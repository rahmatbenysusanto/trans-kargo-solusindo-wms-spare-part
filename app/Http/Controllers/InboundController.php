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
    public function receiving(): View
    {
        $inbound = Inbound::latest()->paginate(10);
        $title = 'Receiving';
        return view('inbound.receiving.index', compact('title', 'inbound'));
    }

    public function show($id): View
    {
        $inbound = Inbound::with('details')->findOrFail($id);
        $title = 'Receiving';
        return view('inbound.receiving.show', compact('title', 'inbound'));
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

    public function putAway(): View
    {
        $inbound = Inbound::where('status', 'process qc')->latest()->paginate(10);
        $title = 'Put Away';
        return view('inbound.put-away.index', compact('title', 'inbound'));
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

    public static function generateUniqueId(string $brand, string $group): string
    {
        return sprintf(
            '%s-%s-%s',
            self::makeCode($brand),
            self::makeCode($group),
            strtoupper(Str::random(5))
        );
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
                        'status'            => 'available'
                    ]);
                } else {
                    $brand = Brand::find($inboundDetail->brand_id);
                    $productGroup = ProductGroup::find($inboundDetail->product_group_id);

                    $createInventory = Inventory::create([
                        'unique_id'         => $this->generateUniqueId($brand->name, $productGroup->name),
                        'client_id'         => $inbound->client_id,
                        'storage_level_id'  => $storageLevelId,
                        'qty'               => 1,
                        'part_name'         => $inboundDetail->part_name,
                        'part_number'       => $inboundDetail->part_number,
                        'part_description'  => $inboundDetail->part_description,
                        'serial_number'     => $inboundDetail->serial_number,
                        'status'            => 'available',
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
                $locationName = $storage ? $storage->bin->rak->zone->name . ' - ' . $storage->name : 'N/A';

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

    public function createSpare(): View
    {
        $brand = Brand::all();
        $productGroup = ProductGroup::all();
        $client = Client::all();

        $title = "Receiving";
        return view('inbound.receiving.spare.create', compact('title', 'brand', 'productGroup', 'client'));
    }

    public function createFaulty(): View
    {
        $brand = Brand::all();
        $productGroup = ProductGroup::all();
        $client = Client::all();

        $title = "Receiving";
        return view('inbound.receiving.faulty.create', compact('title', 'brand', 'productGroup', 'client'));
    }

    public function createRma(): View
    {
        $brand = Brand::all();
        $productGroup = ProductGroup::all();
        $client = Client::all();

        $title = "Receiving";
        return view('inbound.receiving.rma.create', compact('title', 'brand', 'productGroup', 'client'));
    }

    public function createNewPO(): View
    {
        $brand = Brand::all();
        $productGroup = ProductGroup::all();
        $client = Client::all();

        $title = "Receiving";
        return view('inbound.receiving.new-po.create', compact('title', 'brand', 'productGroup', 'client'));
    }

    /**
     * @throws \Throwable
     */
    public function storeNewPO(Request $request): \Illuminate\Http\JsonResponse
    {
        $request->validate([
            'client_id'      => 'required',
            'number'         => 'required',
            'vendor'         => 'required',
            'receivedDate'  => 'required',
            'receivedBy'    => 'required',
            'products'      => 'required|array|min:1',
        ]);

        try {
            DB::beginTransaction();

            $inbound = Inbound::create([
                'category'       => 'New PO',
                'client_id'      => $request->post('client_id'),
                'number'         => $request->post('number'),
                'receiving_note' => $request->post('receivingNote'),
                'vendor'         => $request->post('vendor'),
                'qty'            => count($request->post('products')),
                'received_date'  => $request->post('receivedDate'),
                'received_by'    => $request->post('receivedBy'),
                'status'         => 'new'
            ]);

            $this->storeDetails($inbound, $request->post('products'));

            DB::commit();
            return response()->json(['status' => true]);
        } catch (\Throwable $err) {
            DB::rollBack();
            return response()->json(['status' => false, 'message' => $err->getMessage()]);
        }
    }

    public function storeSpare(Request $request): \Illuminate\Http\JsonResponse
    {
        $request->validate([
            'category'        => 'required',
            'client_id'       => 'required',
            'number'          => 'required', // NTT RN#
            'sttb'            => 'required',
            'receivedDate'   => 'required',
            'receivedBy'     => 'required',
            'products'       => 'required|array|min:1',
        ]);

        try {
            DB::beginTransaction();

            $inbound = Inbound::create([
                'category'              => $request->post('category'),
                'client_id'             => $request->post('client_id'),
                'number'                => $request->post('po_number'), // PO#
                'receiving_note'        => $request->post('number'), // NTT RN#
                'sttb'                  => $request->post('sttb'),
                'courier_delivery_note' => $request->post('delivery_note'),
                'courier_invoice'       => $request->post('courier_invoice'),
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

    public function storeFaulty(Request $request): \Illuminate\Http\JsonResponse
    {
        $request->validate([
            'client_id'       => 'required',
            'number'          => 'required', // NTT RN#
            'sttb'            => 'required',
            'receivedDate'   => 'required',
            'receivedBy'     => 'required',
            'products'       => 'required|array|min:1',
        ]);

        try {
            DB::beginTransaction();

            $inbound = Inbound::create([
                'category'              => 'Faulty',
                'client_id'             => $request->post('client_id'),
                'number'                => $request->post('po_number'), // PO# (optional references)
                'receiving_note'        => $request->post('number'), // NTT RN#
                'sttb'                  => $request->post('sttb'),
                'courier_delivery_note' => $request->post('delivery_note'),
                'courier_invoice'       => $request->post('courier_invoice'),
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

    public function storeRma(Request $request): \Illuminate\Http\JsonResponse
    {
        $request->validate([
            'client_id'       => 'required',
            'number'          => 'required', // RMA#
            'receivedDate'   => 'required',
            'receivedBy'     => 'required',
            'products'       => 'required|array|min:1',
        ]);

        try {
            DB::beginTransaction();

            $inbound = Inbound::create([
                'category'              => 'RMA',
                'client_id'             => $request->post('client_id'),
                'number'                => $request->post('number'), // RMA#
                'itsm_number'           => $request->post('itsm_number'),
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
                'inbound_id'    => $inbound->id,
                'product_id'    => $productId,
                'part_name'     => $product['partName'],
                'part_number'   => $product['partNumber'],
                'description'   => $product['partDescription'] ?? '',
                'qty'           => 1,
                'serial_number' => $product['serialNumber'],
                'old_serial_number' => $product['oldSerialNumber'] ?? null,
                'condition'     => $product['condition'],
                'brand_id'      => $brand->id,
                'product_group_id' => $productGroup->id,
            ]);

            \App\Models\InventoryHistory::create([
                'inventory_id' => null, // Linked later during Put Away
                'serial_number' => $product['serialNumber'],
                'type' => 'Inbound',
                'category' => $inbound->category,
                'reference_number' => $inbound->number,
                'description' => "Received item via {$inbound->category} (Ref: {$inbound->number})",
                'user' => $inbound->received_by,
            ]);
        }
    }
}
