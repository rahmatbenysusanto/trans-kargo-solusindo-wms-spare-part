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
        $search = $request->get('search');
        $title = 'Outbound';
        $data = Outbound::with('client')
            ->where('category', '!=', 'Write-off')
            ->latest()
            ->get();
        return view('outbound.index', compact('title', 'search', 'data'));
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
        $outbound = Outbound::with(['client', 'details'])->findOrFail($id);
        $title = 'Outbound Detail';
        return view('outbound.show', compact('title', 'outbound'));
    }

    public function printPdf($id): View
    {
        $outbound = Outbound::with(['client', 'details'])->findOrFail($id);
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
                'client_id' => $request->post('client_id'),
                'number' => $request->post('number'), // PO#
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
                    'description' => "Item shipped out via {$outbound->category} to " . ($outbound->client->name ?? 'Client'),
                    'user' => $outbound->outbound_by,
                ]);

                // Update Inventory
                if ($inventory) {
                    $inventoryStatus = 'Shipped / Outbound'; // Default fallback

                    switch ($outbound->category) {
                        case 'Spare from Replacement':
                        case 'Spare to Replacement':
                            $inventoryStatus = 'Out for Replacement/ Support';
                            break;
                        case 'Spare from Loan':
                        case 'Spare to Loan':
                            $inventoryStatus = 'Out for Loan';
                            break;
                        case 'Faulty':
                        case 'RMA':
                            $inventoryStatus = 'Out for Return';
                            break;
                        case 'Write-off':
                        case 'Spare Write-off':
                            $inventoryStatus = 'Write-off';
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
    public function getInventory(Request $request)
    {
        $clientId = $request->get('client_id');
        $query = \App\Models\Inventory::with(['storageLevel.bin.rak.zone'])
            ->where('qty', '>', 0)
            ->whereNotIn('status', [
                'Shipped / Outbound',
                'Out for Replacement/ Support',
                'Out for Loan',
                'Out for Return',
                'Write-off'
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

        if ($request->exclude_ids) {
            $excludeIds = explode(',', $request->exclude_ids);
            $query->whereNotIn('id', $excludeIds);
        }

        $data = $query->latest()->limit(50)->get()->map(function ($item) {
            return [
                'id' => $item->id,
                'unique_id' => $item->unique_id,
                'part_name' => $item->part_name,
                'part_number' => $item->part_number,
                'part_description' => $item->description,
                'serial_number' => $item->serial_number,
                'brand' => $item->brand,
                'product_group' => $item->product_group,
                'condition' => $item->condition,
                'location' => $item->storageLevel ? $item->storageLevel->bin->rak->zone->name . ' - ' . $item->storageLevel->name : 'N/A'
            ];
        });

        return response()->json($data);
    }
}
