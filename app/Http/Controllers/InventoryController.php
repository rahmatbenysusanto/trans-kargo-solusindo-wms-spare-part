<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class InventoryController extends Controller
{
    public function scan($unique_id)
    {
        $inventory = \App\Models\Inventory::with(['storageLevel.bin.rak.zone', 'client', 'product.brand', 'product.productGroup'])
            ->where('unique_id', $unique_id)
            ->firstOrFail();

        $sn = $inventory->serial_number;

        $history = \App\Models\InventoryHistory::where('serial_number', $sn)
            ->latest()
            ->get();

        return view('inventory.scan', compact('inventory', 'history'));
    }

    public function index(Request $request): View
    {
        $title = 'Inventory List';
        $inventory = \App\Models\Inventory::with(['storageLevel.bin.rak.zone', 'client', 'product.brand', 'product.productGroup'])
            ->when($request->status, function ($query) use ($request) {
                return $query->where('status', $request->status);
            })
            ->when($request->client_id, function ($query) use ($request) {
                return $query->where('client_id', $request->client_id);
            })
            ->when($request->condition, function ($query) use ($request) {
                return $query->where('condition', $request->condition);
            })
            ->when($request->search, function ($query) use ($request) {
                return $query->where(function ($q) use ($request) {
                    $q->where('unique_id', 'like', '%' . $request->search . '%')
                        ->orWhere('part_name', 'like', '%' . $request->search . '%')
                        ->orWhere('serial_number', 'like', '%' . $request->search . '%')
                        ->orWhere('part_number', 'like', '%' . $request->search . '%');
                });
            })
            ->latest()
            ->paginate(15);

        $statuses = \App\Models\Inventory::select('status')->distinct()->pluck('status');
        $conditions = \App\Models\Inventory::select('condition')->distinct()->pluck('condition');
        $clients = \App\Models\Client::all();

        return view('inventory.inventory-list.index', compact('title', 'inventory', 'statuses', 'conditions', 'clients'));
    }

    public function exportPdf(Request $request): View
    {
        $inventory = \App\Models\Inventory::with(['storageLevel.bin.rak.zone', 'client', 'product.brand', 'product.productGroup'])
            ->when($request->status, function ($query) use ($request) {
                return $query->where('status', $request->status);
            })
            ->when($request->client_id, function ($query) use ($request) {
                return $query->where('client_id', $request->client_id);
            })
            ->when($request->condition, function ($query) use ($request) {
                return $query->where('condition', $request->condition);
            })
            ->when($request->search, function ($query) use ($request) {
                return $query->where(function ($q) use ($request) {
                    $q->where('unique_id', 'like', '%' . $request->search . '%')
                        ->orWhere('part_name', 'like', '%' . $request->search . '%')
                        ->orWhere('serial_number', 'like', '%' . $request->search . '%')
                        ->orWhere('part_number', 'like', '%' . $request->search . '%');
                });
            })
            ->latest()
            ->get();

        $title = 'Inventory List Report';
        return view('inventory.inventory-list.pdf', compact('title', 'inventory'));
    }

    public function exportExcel(Request $request)
    {
        $inventory = \App\Models\Inventory::with(['storageLevel.bin.rak.zone', 'client', 'product.brand', 'product.productGroup'])
            ->when($request->status, function ($query) use ($request) {
                return $query->where('status', $request->status);
            })
            ->when($request->client_id, function ($query) use ($request) {
                return $query->where('client_id', $request->client_id);
            })
            ->when($request->condition, function ($query) use ($request) {
                return $query->where('condition', $request->condition);
            })
            ->when($request->search, function ($query) use ($request) {
                return $query->where(function ($q) use ($request) {
                    $q->where('unique_id', 'like', '%' . $request->search . '%')
                        ->orWhere('part_name', 'like', '%' . $request->search . '%')
                        ->orWhere('serial_number', 'like', '%' . $request->search . '%')
                        ->orWhere('part_number', 'like', '%' . $request->search . '%');
                });
            })
            ->latest()
            ->get();

        $filename = "inventory-list-" . date('Y-m-d') . ".xls";

        header("Content-Type: application/vnd.ms-excel");
        header("Content-Disposition: attachment; filename=\"$filename\"");

        echo "<table border='1'>";
        echo "<thead>";
        echo "<tr>";
        echo "<th>No</th>";
        echo "<th>Asset ID</th>";
        echo "<th>Client</th>";
        echo "<th>Part Name</th>";
        echo "<th>Part Number</th>";
        echo "<th>Serial Number</th>";
        echo "<th>Brand</th>";
        echo "<th>Product Group</th>";
        echo "<th>Storage</th>";
        echo "<th>Status</th>";
        echo "<th>Condition</th>";
        echo "<th>Last Movement</th>";
        echo "</tr>";
        echo "</thead>";
        echo "<tbody>";

        foreach ($inventory as $index => $item) {
            $zone = $item->storageLevel ? ($item->storageLevel->bin->rak->zone->name ?? '-') : '-';
            $rack = $item->storageLevel ? ($item->storageLevel->bin->rak->name ?? '-') : '-';
            $bin = $item->storageLevel ? ($item->storageLevel->bin->name ?? '-') : '-';
            $level = $item->storageLevel ? ($item->storageLevel->name ?? '-') : '-';
            $brand = $item->product && $item->product->brand ? $item->product->brand->name : '-';
            $group = $item->product && $item->product->productGroup ? $item->product->productGroup->name : '-';

            echo "<tr>";
            echo "<td>" . ($index + 1) . "</td>";
            echo "<td>{$item->unique_id}</td>";
            echo "<td>" . ($item->client->name ?? '-') . "</td>";
            echo "<td>{$item->part_name}</td>";
            echo "<td>{$item->part_number}</td>";
            echo "<td>'{$item->serial_number}</td>"; // Prefix with ' to force string in Excel
            echo "<td>{$brand}</td>";
            echo "<td>{$group}</td>";
            $storage = $item->storageLevel ? "{$item->storageLevel->bin->rak->zone->name}-{$item->storageLevel->bin->rak->name}-{$item->storageLevel->bin->name}-{$item->storageLevel->name}" : "-";
            echo "<td>{$storage}</td>";
            echo "<td>{$item->status}</td>";
            echo "<td>{$item->condition}</td>";
            echo "<td>" . ($item->last_movement_date ?? '-') . "</td>";
            echo "</tr>";
        }

        echo "</tbody>";
        echo "</table>";
        exit;
    }

    public function stockMovement(): View
    {
        $title = 'Stock Movement';
        $movements = \App\Models\InventoryMovement::with(['inventory', 'fromStorageLevel.bin.rak.zone', 'toStorageLevel.bin.rak.zone', 'user'])
            ->latest()
            ->paginate(20);

        return view('inventory.stock-movement.index', compact('title', 'movements'));
    }

    public function show($id): View
    {
        $title = 'Inventory Detail';
        $inventory = \App\Models\Inventory::with([
            'storageLevel.bin.rak.zone',
            'client',
            'product.brand',
            'product.productGroup',
            'details.inboundDetail.inbound'
        ])
            ->findOrFail($id);

        $sn = $inventory->serial_number;

        // Fetch unified history for this SN and its parent link
        $history = \App\Models\InventoryHistory::where('serial_number', $sn)
            ->orWhere('serial_number', $inventory->parent_serial_number)
            ->orWhere('description', 'like', '%' . $sn . '%')
            ->latest()
            ->get()
            ->map(function ($item) {
                return [
                    'date' => $item->created_at,
                    'type' => $item->type,
                    'category' => $item->category,
                    'reference' => $item->reference_number,
                    'description' => $item->description,
                    'user' => $item->user,
                    'from_location' => $item->from_location,
                    'to_location' => $item->to_location,
                    'sn' => $item->serial_number, // Added to distinguish if it's from another SN
                    'parent_sn' => null // Default for InventoryHistory
                ];
            });

        // Add inbound details to history
        foreach ($inventory->details as $detail) {
            if ($detail->inboundDetail) {
                $history->push([
                    'date' => $detail->inboundDetail->created_at,
                    'type' => 'Inbound',
                    'category' => 'Receiving',
                    'reference' => $detail->inboundDetail->inbound->number ?? '-',
                    'description' => 'Received unit into warehouse.',
                    'user' => $detail->inboundDetail->inbound->received_by,
                    'from_location' => $detail->inboundDetail->vendor ?: 'Supplier',
                    'to_location' => 'Inbound Staging',
                    'sn' => $sn, // The SN of the current inventory item
                    'parent_sn' => $detail->inboundDetail->parent_sn ?? $detail->inboundDetail->old_serial_number
                ]);
            }
        }

        // Sort the combined history by date
        $history = $history->sortByDesc('date')->values();

        return view('inventory.inventory-list.show', compact('title', 'inventory', 'history'));
    }

    public function productMovementIndex(): View
    {
        $title = 'Product Movement';
        $movements = \App\Models\InventoryMovement::with(['inventory', 'fromStorageLevel.bin.rak.zone', 'toStorageLevel.bin.rak.zone', 'user'])
            ->where('type', 'Movement')
            ->latest()
            ->paginate(20);

        return view('inventory.product-movement.index', compact('title', 'movements'));
    }

    public function productMovementProcess(): View
    {
        $title = 'Product Movement';
        $inventory = \App\Models\Inventory::where('qty', '>', 0)->latest()->get();
        $storageZone = \App\Models\StorageZone::all();

        return view('inventory.product-movement.process', compact('title', 'inventory', 'storageZone'));
    }

    public function productMovementUpdate(Request $request)
    {
        $request->validate([
            'products' => 'required|array',
            'storage_level_id' => 'required'
        ]);

        try {
            \Illuminate\Support\Facades\DB::beginTransaction();

            foreach ($request->products as $id) {
                $inventory = \App\Models\Inventory::findOrFail($id);
                $oldStorageLevelId = $inventory->storage_level_id;

                $inventory->update([
                    'storage_level_id' => $request->storage_level_id,
                    'last_movement_date' => now()
                ]);

                // Record History
                \App\Models\InventoryMovement::create([
                    'inventory_id' => $id,
                    'from_storage_level_id' => $oldStorageLevelId,
                    'to_storage_level_id' => $request->storage_level_id,
                    'user_id' => Auth::id(),
                    'type' => 'Movement',
                    'description' => 'Product moved by ' . Auth::user()->name
                ]);

                // Record Unified History
                $toStorage = \App\Models\StorageLevel::with('bin.rak.zone')->find($request->storage_level_id);
                $toName = $toStorage ? "{$toStorage->bin->rak->zone->name}-{$toStorage->bin->rak->name}-{$toStorage->bin->name}-{$toStorage->name}" : 'N/A';

                $fromStorage = \App\Models\StorageLevel::with('bin.rak.zone')->find($oldStorageLevelId);
                $fromName = $fromStorage ? "{$fromStorage->bin->rak->zone->name}-{$fromStorage->bin->rak->name}-{$fromStorage->bin->name}-{$fromStorage->name}" : 'N/A';

                \App\Models\InventoryHistory::create([
                    'inventory_id' => $id,
                    'serial_number' => $inventory->serial_number,
                    'type' => 'Movement',
                    'category' => 'Location Change',
                    'reference_number' => '-',
                    'description' => "Unit moved from [$fromName] to [$toName]",
                    'user' => Auth::user()->name,
                    'from_location' => $fromName,
                    'to_location' => $toName
                ]);
            }

            \Illuminate\Support\Facades\DB::commit();
            return response()->json(['status' => true]);
        } catch (\Throwable $err) {
            \Illuminate\Support\Facades\DB::rollBack();
            return response()->json(['status' => false, 'message' => $err->getMessage()]);
        }
    }
    public function productSummary(Request $request): View
    {
        $title = 'Inventory Product';
        $query = \App\Models\Inventory::select(
            'part_name',
            'part_number',
            \Illuminate\Support\Facades\DB::raw('COUNT(*) as total_in'),
            \Illuminate\Support\Facades\DB::raw('SUM(CASE WHEN qty > 0 THEN 1 ELSE 0 END) as in_inventory'),
            \Illuminate\Support\Facades\DB::raw('SUM(CASE WHEN qty = 0 THEN 1 ELSE 0 END) as total_out')
        )
            ->when($request->search, function ($query) use ($request) {
                return $query->where(function ($q) use ($request) {
                    $q->where('part_name', 'like', '%' . $request->search . '%')
                        ->orWhere('part_number', 'like', '%' . $request->search . '%');
                });
            })
            ->groupBy('part_name', 'part_number')
            ->orderBy('part_name');

        $data = $query->paginate(15);

        return view('inventory.product-summary', compact('title', 'data'));
    }

    public function productSummaryDetail(Request $request)
    {
        $partName = $request->part_name;
        $partNumber = $request->part_number;

        $details = \App\Models\Inventory::with(['storageLevel.bin.rak.zone', 'client'])
            ->where('part_name', $partName)
            ->where('part_number', $partNumber)
            ->get()
            ->map(function ($item) {
                $storage = $item->storageLevel ? "{$item->storageLevel->bin->rak->zone->name}-{$item->storageLevel->bin->rak->name}-{$item->storageLevel->bin->name}-{$item->storageLevel->name}" : "-";
                return [
                    'unique_id' => $item->unique_id,
                    'serial_number' => $item->serial_number,
                    'status' => $item->status,
                    'condition' => $item->condition,
                    'client' => $item->client->name ?? '-',
                    'storage' => $storage
                ];
            });

        return response()->json($details);
    }
}
