<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use App\Models\Client;

class InventoryController extends Controller
{
    private function applyClientFilter($query, $clientId, $column = 'client_id')
    {
        $user = Auth::user();
        if ($user->isAdminWMS()) {
            if ($clientId) {
                $query->where($column, $clientId);
            }
        } else {
            $accessibleIds = $user->getAccessibleClientIds();
            if ($clientId && in_array($clientId, $accessibleIds)) {
                $query->where($column, $clientId);
            } else {
                $query->whereIn($column, $accessibleIds);
            }
        }
        return $query;
    }

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
        $clientId = $request->get('client_id');
        $user = Auth::user();

        $inventory = \App\Models\Inventory::with(['storageLevel.bin.rak.zone', 'client', 'product.brand', 'product.productGroup'])
            ->when($request->status, function ($query) use ($request) {
                return $query->where('status', $request->status);
            });

        $this->applyClientFilter($inventory, $clientId);

        $inventory = $inventory->when($request->condition, function ($query) use ($request) {
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
        $clients = $user->isAdminWMS() ? Client::all() : $user->clients;

        return view('inventory.inventory-list.index', compact('title', 'inventory', 'statuses', 'conditions', 'clients'));
    }

    public function exportPdf(Request $request): View
    {
        $clientId = $request->get('client_id');

        $inventory = \App\Models\Inventory::with(['storageLevel.bin.rak.zone', 'client', 'product.brand', 'product.productGroup'])
            ->when($request->status, function ($query) use ($request) {
                return $query->where('status', $request->status);
            });

        $this->applyClientFilter($inventory, $clientId);

        $inventory = $inventory->when($request->condition, function ($query) use ($request) {
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
        $clientId = $request->get('client_id');

        $inventory = \App\Models\Inventory::with(['storageLevel.bin.rak.zone', 'client', 'product.brand', 'product.productGroup'])
            ->when($request->status, function ($query) use ($request) {
                return $query->where('status', $request->status);
            });

        $this->applyClientFilter($inventory, $clientId);

        $inventory = $inventory->when($request->condition, function ($query) use ($request) {
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
            $brand = $item->product && $item->product->brand ? $item->product->brand->name : '-';
            $group = $item->product && $item->product->productGroup ? $item->product->productGroup->name : '-';

            echo "<tr>";
            echo "<td>" . ($index + 1) . "</td>";
            echo "<td>{$item->unique_id}</td>";
            echo "<td>" . ($item->client->name ?? '-') . "</td>";
            echo "<td>{$item->part_name}</td>";
            echo "<td>{$item->part_number}</td>";
            echo "<td>'{$item->serial_number}</td>";
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
        $user = Auth::user();

        $movements = \App\Models\InventoryMovement::with(['inventory', 'fromStorageLevel.bin.rak.zone', 'toStorageLevel.bin.rak.zone', 'user']);

        if ($user->isAdminWMS()) {
            // No automatic filter
        } else {
            $accessibleIds = $user->getAccessibleClientIds();
            $movements->whereHas('inventory', function ($q) use ($accessibleIds) {
                $q->whereIn('client_id', $accessibleIds);
            });
        }

        $movements = $movements->latest()->paginate(20);

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

        // Fetch unified history
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
                    'sn' => $item->serial_number,
                    'parent_sn' => null
                ];
            });

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
                    'sn' => $sn,
                    'parent_sn' => $detail->inboundDetail->parent_sn ?? $detail->inboundDetail->old_serial_number
                ]);
            }
        }

        $history = $history->sortByDesc('date')->values();

        return view('inventory.inventory-list.show', compact('title', 'inventory', 'history'));
    }

    public function productMovementIndex(): View
    {
        $title = 'Product Movement';
        $user = Auth::user();

        $movements = \App\Models\InventoryMovement::with(['inventory', 'fromStorageLevel.bin.rak.zone', 'toStorageLevel.bin.rak.zone', 'user'])
            ->where('type', 'Movement');

        if ($user->isAdminWMS()) {
            // No automatic filter
        } else {
            $accessibleIds = $user->getAccessibleClientIds();
            $movements->whereHas('inventory', function ($q) use ($accessibleIds) {
                $q->whereIn('client_id', $accessibleIds);
            });
        }

        $movements = $movements->latest()->paginate(20);

        return view('inventory.product-movement.index', compact('title', 'movements'));
    }

    public function productMovementProcess(): View
    {
        $title = 'Product Movement';
        $user = Auth::user();

        $inventory = \App\Models\Inventory::where('qty', '>', 0);

        if (!$user->isAdminWMS()) {
            $inventory->whereIn('client_id', $user->getAccessibleClientIds());
        }

        $inventory = $inventory->latest()->get();
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

                \App\Models\InventoryMovement::create([
                    'inventory_id' => $id,
                    'from_storage_level_id' => $oldStorageLevelId,
                    'to_storage_level_id' => $request->storage_level_id,
                    'user_id' => Auth::id(),
                    'type' => 'Movement',
                    'description' => 'Product moved by ' . Auth::user()->name
                ]);

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
        $clientId = $request->get('client_id');
        $user = Auth::user();

        $query = \App\Models\Inventory::select(
            'part_name',
            'part_number',
            \Illuminate\Support\Facades\DB::raw('COUNT(*) as total_in'),
            \Illuminate\Support\Facades\DB::raw('SUM(CASE WHEN qty > 0 THEN 1 ELSE 0 END) as in_inventory'),
            \Illuminate\Support\Facades\DB::raw('SUM(CASE WHEN qty = 0 THEN 1 ELSE 0 END) as total_out')
        );

        $this->applyClientFilter($query, $clientId);

        $query = $query->when($request->search, function ($query) use ($request) {
            return $query->where(function ($q) use ($request) {
                $q->where('part_name', 'like', '%' . $request->search . '%')
                    ->orWhere('part_number', 'like', '%' . $request->search . '%');
            });
        })
            ->groupBy('part_name', 'part_number')
            ->orderBy('part_name');

        $data = $query->paginate(15);
        $clients = $user->isAdminWMS() ? Client::all() : $user->clients;

        return view('inventory.product-summary', compact('title', 'data', 'clients'));
    }

    public function productSummaryDetail(Request $request)
    {
        $partName = $request->part_name;
        $partNumber = $request->part_number;

        $details = \App\Models\Inventory::with(['storageLevel.bin.rak.zone', 'client'])
            ->where('part_name', $partName)
            ->where('part_number', $partNumber);

        if (!Auth::user()->isAdminWMS()) {
            $details->whereIn('client_id', Auth::user()->getAccessibleClientIds());
        }

        $details = $details->get()
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

    public function stockStatement(Request $request): View
    {
        $title = 'Inventory Stock Statement';
        $user = Auth::user();
        $clients = $user->isAdminWMS() ? Client::all() : $user->clients;
        $categories = ['New PO', 'Spare from/to Replacement', 'Spare from/to Loan', 'Faulty', 'RMA', 'Spare Write-off', 'Spare Migration'];
        $requestTypes = ['New PO', 'RMA', 'Loan', 'Spare Write Off', 'Spare Migration'];

        $clientId = $request->get('client_id');

        $inboundData = \App\Models\InboundDetail::with(['inbound.client', 'brand', 'storageLevel.bin.rak.zone', 'productGroup'])
            ->select('inbound_detail.*')
            ->join('inbound', 'inbound_detail.inbound_id', '=', 'inbound.id')
            ->where(function ($q) {
                $q->whereNotNull('inbound_detail.storage_level_id')
                    ->orWhereExists(function ($query) {
                        $query->select(\Illuminate\Support\Facades\DB::raw(1))
                            ->from('outbound_detail')
                            ->whereColumn('outbound_detail.serial_number', 'inbound_detail.serial_number');
                    });
            });

        // Custom filter for Inbound relation
        if ($user->isAdminWMS()) {
            if ($clientId) {
                $inboundData->where('inbound.client_id', $clientId);
            }
        } else {
            $accessibleIds = $user->getAccessibleClientIds();
            if ($clientId && in_array($clientId, $accessibleIds)) {
                $inboundData->where('inbound.client_id', $clientId);
            } else {
                $inboundData->whereIn('inbound.client_id', $accessibleIds);
            }
        }

        $inboundData = $inboundData->when($request->category, function ($query) use ($request) {
            return $query->where('inbound.category', $request->category);
        })
            ->when($request->request_type, function ($query) use ($request) {
                return $query->where('inbound.request_type', $request->request_type);
            })
            ->when($request->search, function ($query) use ($request) {
                return $query->where(function ($q) use ($request) {
                    $q->where('inbound_detail.serial_number', 'like', '%' . $request->search . '%')
                        ->orWhere('inbound_detail.part_name', 'like', '%' . $request->search . '%')
                        ->orWhere('inbound.number', 'like', '%' . $request->search . '%')
                        ->orWhere('inbound.receiving_note', 'like', '%' . $request->search . '%')
                        ->orWhereExists(function ($query) use ($request) {
                            $query->select(\Illuminate\Support\Facades\DB::raw(1))
                                ->from('inventory')
                                ->whereColumn('inventory.serial_number', 'inbound_detail.serial_number')
                                ->where('inventory.unique_id', 'like', '%' . $request->search . '%');
                        });
                });
            })
            ->latest()
            ->paginate(50);

        $sns = $inboundData->pluck('serial_number')->toArray();
        $inventories = \App\Models\Inventory::with('storageLevel.bin.rak.zone')->whereIn('serial_number', $sns)->get()->keyBy('serial_number');
        $outbounds = \App\Models\OutboundDetail::with('outbound')->whereIn('serial_number', $sns)->get()->keyBy('serial_number');

        foreach ($inboundData as $item) {
            $inventory = $inventories->get($item->serial_number);
            $outbound = $outbounds->get($item->serial_number);

            $item->is_outbound = (bool)$outbound;
            $item->is_in_stock = $inventory && $inventory->qty > 0;
            $item->current_inventory = $inventory;
            $item->outbound_detail = $outbound;
        }

        return view('inventory.stock-statement.index', compact('title', 'inboundData', 'clients', 'categories', 'requestTypes'));
    }
}
