<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use App\Models\Client;
use App\Models\Outbound;
use App\Models\OutboundDetail;
use App\Models\ProductGroup;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class OutboundController extends Controller
{
    public function index(Request $request): View
    {
        $title = 'Outbound';
        $data = Outbound::with('client')
            ->when($request->client_id, function ($query) use ($request) {
                return $query->where('client_id', $request->client_id);
            })
            ->when($request->category, function ($query) use ($request) {
                return $query->where('category', $request->category);
            })
            ->when($request->search, function ($query) use ($request) {
                return $query->where(function ($q) use ($request) {
                    $q->where('number', 'like', '%' . $request->search . '%')
                        ->orWhere('sap_po_number', 'like', '%' . $request->search . '%')
                        ->orWhere('ntt_dn_number', 'like', '%' . $request->search . '%')
                        ->orWhere('tks_dn_number', 'like', '%' . $request->search . '%')
                        ->orWhere('tks_invoice_number', 'like', '%' . $request->search . '%')
                        ->orWhere('rma_number', 'like', '%' . $request->search . '%')
                        ->orWhere('itsm_number', 'like', '%' . $request->search . '%');
                });
            })
            ->latest()
            ->paginate(15);

        $clients = Client::all();
        $categories = ['Spare to Replacement', 'Spare from Replacement', 'Spare to Loan', 'Spare from Loan', 'Faulty', 'RMA', 'Spare Write-off', 'Spare Migration'];

        return view('outbound.index', compact('title', 'data', 'clients', 'categories'));
    }

    public function createSpare(): View
    {
        $title = 'Create Outbound Spare';
        $client = Client::all();
        $productGroup = ProductGroup::all();
        $brand = Brand::all();
        return view('outbound.spare.create', compact('title', 'client', 'productGroup', 'brand'));
    }

    public function createFaulty(): View
    {
        $title = 'Create Outbound Faulty';
        $client = Client::all();
        $productGroup = ProductGroup::all();
        $brand = Brand::all();
        return view('outbound.faulty.create', compact('title', 'client', 'productGroup', 'brand'));
    }

    public function createRma(): View
    {
        $title = 'Create Outbound RMA';
        $client = Client::all();
        $productGroup = ProductGroup::all();
        $brand = Brand::all();
        return view('outbound.rma.create', compact('title', 'client', 'productGroup', 'brand'));
    }

    public function createWriteOff(): View
    {
        $title = 'Create Outbound Write-off';
        $client = Client::all();
        $productGroup = ProductGroup::all();
        $brand = Brand::all();
        return view('outbound.write-off.create', compact('title', 'client', 'productGroup', 'brand'));
    }

    public function show($id): View
    {
        $outbound = Outbound::with(['client', 'details', 'invoices'])->findOrFail($id);
        $title = 'Outbound Detail';
        return view('outbound.show', compact('title', 'outbound'));
    }

    public function printPdf($id): View
    {
        $outbound = Outbound::with(['client', 'details.inventory'])->findOrFail($id);
        // We will just render a view for printing
        $title = 'Outbound Report';
        return view('outbound.pdf', compact('title', 'outbound'));
    }

    public function storeSpare(Request $request)
    {
        return $this->storeOutbound($request, 'Spare');
    }

    public function storeFaulty(Request $request)
    {
        return $this->storeOutbound($request, 'Faulty');
    }

    public function storeRma(Request $request)
    {
        return $this->storeOutbound($request, 'RMA');
    }

    public function storeWriteOff(Request $request)
    {
        return $this->storeOutbound($request, 'Write-off');
    }

    private function storeOutbound(Request $request, $defaultCategory)
    {
        $request->validate([
            'client_id' => 'required',
            'outbound_date' => 'required',
            'outbound_by' => 'required',
            'products' => 'required|array|min:1',
        ]);

        try {
            DB::beginTransaction();

            $outbound = Outbound::create([
                'category' => $request->post('category') ?? $defaultCategory,
                'request_type' => $request->post('request_type'),
                'ntt_requestor' => $request->post('ntt_requestor'),
                'request_date' => $request->post('request_date'),
                'sap_po_number' => $request->post('sap_po_number'),
                'client_id' => $request->post('client_id'),
                'client_contact' => $request->post('client_contact'),
                'pickup_address' => $request->post('pickup_address'),
                'number' => $request->post('number'), // PO# system ref
                'ntt_dn_number' => $request->post('ntt_dn_number'),
                'tks_dn_number' => $request->post('tks_dn_number'),
                'tks_invoice_number' => $request->post('tks_invoice_number'),
                'rma_number' => $request->post('rma_number'),
                'itsm_number' => $request->post('itsm_number'),
                'qty' => count($request->post('products')),
                'status' => 'new',
                'outbound_date' => $request->post('outbound_date'),
                'outbound_by' => $request->post('outbound_by'),
            ]);

            foreach ($request->post('products') as $product) {
                $inventory = \App\Models\Inventory::where('serial_number', $product['serialNumber'])->first();
                $inventoryId = $inventory ? $inventory->id : null;

                OutboundDetail::create([
                    'outbound_id' => $outbound->id,
                    'product_id' => $product['product_id'] ?? 0,
                    'part_name' => $product['partName'],
                    'part_number' => $product['partNumber'],
                    'description' => $product['partDescription'] ?? '',
                    'qty' => 1,
                    'serial_number' => $product['serialNumber'],
                    'old_serial_number' => $product['oldSerialNumber'] ?? null,
                    'condition' => $product['condition'] ?? ($inventory->condition ?? 'Good'),
                ]);

                // Record Unified History

                \App\Models\InventoryHistory::create([
                    'inventory_id' => $inventoryId,
                    'serial_number' => $product['serialNumber'],
                    'type' => 'Outbound',
                    'category' => $outbound->category,
                    'reference_number' => $outbound->number ?? $outbound->tks_dn_number,
                    'description' => "Item shipped out via {$outbound->category} to " . ($outbound->client->name ?? 'Client') . (isset($product['oldSerialNumber']) && $product['oldSerialNumber'] ? " - Replacing SN: {$product['oldSerialNumber']}" : ""),
                    'user' => $outbound->outbound_by,
                ]);

                // Update Inventory
                if ($inventory) {
                    $inventoryStatus = 'Shipped / Outbound'; // Default fallback

                    switch ($outbound->category) {
                        case 'Replacement':
                        case 'Spare from Replacement':
                        case 'Spare to Replacement':
                            $inventoryStatus = 'Out for Replacement/ Support';
                            break;
                        case 'Spare from Loan':
                        case 'Spare to Loan':
                        case 'Loan':
                            $inventoryStatus = 'Out for Loan';
                            break;
                        case 'Faulty':
                        case 'RMA':
                        case 'Out for Return':
                            $inventoryStatus = 'Out for Return';
                            break;
                        case 'Write-off':
                        case 'Spare Write Off':
                        case 'Spare Write-off':
                            $inventoryStatus = 'Write-off';
                            break;
                        case 'Spare Migration':
                        case 'New PO':
                            $inventoryStatus = 'Shipped / Outbound';
                            break;
                    }

                    $inventory->update([
                        'qty' => 0,
                        'status' => $inventoryStatus,
                        'last_movement_date' => now()
                    ]);
                }
            }

            DB::commit();
            return response()->json(['status' => true]);
        } catch (\Throwable $err) {
            DB::rollBack();
            return response()->json(['status' => false, 'message' => $err->getMessage()]);
        }
    }
    public function cancel(Request $request): \Illuminate\Http\JsonResponse
    {
        try {
            DB::beginTransaction();

            $outbound = Outbound::with('details')->findOrFail($request->post('id'));

            // Check if already cancelled
            if ($outbound->status === 'cancel') {
                return response()->json(['status' => false, 'message' => 'This outbound is already cancelled.']);
            }

            foreach ($outbound->details as $detail) {
                // Find inventory record
                $inventory = \App\Models\Inventory::where('serial_number', $detail->serial_number)->first();

                if ($inventory) {
                    // Update Inventory
                    $inventory->update([
                        'qty' => 1,
                        'status' => 'available',
                        'last_movement_date' => now()
                    ]);

                    // Record unified history for the cancellation
                    \App\Models\InventoryHistory::create([
                        'inventory_id' => $inventory->id,
                        'serial_number' => $detail->serial_number,
                        'type' => 'Movement',
                        'category' => 'Cancel Outbound',
                        'reference_number' => $outbound->number ?? $outbound->tks_dn_number,
                        'description' => "Item returned to inventory due to Outbound cancellation ({$outbound->category})",
                        'user' => \Illuminate\Support\Facades\Auth::user()->name,
                    ]);
                }
            }

            // Update Outbound status
            $outbound->update(['status' => 'cancel']);

            DB::commit();
            return response()->json(['status' => true]);
        } catch (\Throwable $err) {
            DB::rollBack();
            return response()->json(['status' => false, 'message' => $err->getMessage()]);
        }
    }

    public function getInventory(Request $request)
    {
        try {
            $clientId = $request->get('client_id');
            $query = \App\Models\Inventory::with(['storageLevel.bin.rak.zone', 'brand', 'productGroup'])
                ->where('qty', '>', 0)
                ->whereNotIn('status', [
                    'Shipped / Outbound',
                    'Out for Replacement/ Support',
                    'Out for Loan',
                    'Out for Return',
                    'Write-off',
                    'staging'
                ]);

            if ($clientId) {
                $query->where('client_id', $clientId);
            }

            if ($request->search) {
                $s = $request->search;
                $query->where(function ($q) use ($s) {
                    $q->where('unique_id', 'like', "%$s%")
                        ->orWhere('serial_number', 'like', "%$s%")
                        ->orWhere('part_name', 'like', "%$s%")
                        ->orWhere('part_number', 'like', "%$s%");
                });
            }

            if ($request->exclude_ids && $request->exclude_ids !== '') {
                $excludeIds = explode(',', $request->exclude_ids);
                $query->whereNotIn('id', $excludeIds);
            }

            $data = $query->latest()->limit(50)->get()->map(function ($item) {
                $location = 'N/A';
                if ($item->storageLevel && $item->storageLevel->bin && $item->storageLevel->bin->rak && $item->storageLevel->bin->rak->zone) {
                    $location = $item->storageLevel->bin->rak->zone->name . '-' .
                        $item->storageLevel->bin->rak->name . '-' .
                        $item->storageLevel->bin->name . '-' .
                        $item->storageLevel->name;
                }

                return [
                    'id' => $item->id,
                    'unique_id' => $item->unique_id,
                    'part_name' => $item->part_name,
                    'part_number' => $item->part_number,
                    'part_description' => $item->part_description,
                    'serial_number' => $item->serial_number,
                    'brand' => ($item->brand ? $item->brand->name : '-'),
                    'product_group' => ($item->productGroup ? $item->productGroup->name : '-'),
                    'condition' => $item->condition,
                    'location' => $location
                ];
            });

            return response()->json($data);
        } catch (\Throwable $e) {
            return response()->json([
                'error' => true,
                'message' => $e->getMessage(),
                'line' => $e->getLine()
            ], 500);
        }
    }
}
