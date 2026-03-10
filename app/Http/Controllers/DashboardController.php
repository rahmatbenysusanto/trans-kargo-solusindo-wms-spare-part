<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Illuminate\Support\Facades\DB;
use App\Models\Inventory;
use App\Models\Outbound;
use App\Models\Inbound;
use App\Models\InboundDetail;
use App\Models\Client;
use App\Models\InventoryHistory;
use Carbon\Carbon;

class DashboardController extends Controller
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

    public function index(Request $request): View
    {
        $title = 'Stock Overview';
        $clientId = $request->get('client_id');
        $user = Auth::user();

        $clients = $user->isAdminWMS() ? Client::all() : $user->clients;

        // 1. Stock Overview by Status
        $stockQuery = Inventory::query();
        $this->applyClientFilter($stockQuery, $clientId);
        $stockByStatus = $stockQuery->select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->get();

        // 2. Utilization by Client
        $utilizationQuery = Outbound::with('client');
        $this->applyClientFilter($utilizationQuery, $clientId);
        $utilizationByClient = $utilizationQuery->select('client_id', DB::raw('count(*) as count'))
            ->groupBy('client_id')
            ->get()
            ->map(function ($item) {
                return [
                    'client_name' => $item->client->name ?? 'Unknown',
                    'count' => $item->count
                ];
            });

        // 3. Inbound vs Outbound Trend (Last 6 Months)
        $months = collect();
        for ($i = 5; $i >= 0; $i--) {
            $months->push(now()->subMonths($i)->format('Y-m'));
        }

        $inboundQuery = Inbound::where('received_date', '>=', now()->subMonths(6));
        if ($user->isAdminWMS()) {
            if ($clientId) {
                $inboundQuery->whereHas('details', function ($query) use ($clientId) {
                    $query->where('client_id', $clientId);
                });
            }
        } else {
            $accessibleIds = $user->getAccessibleClientIds();
            $inboundQuery->whereHas('details', function ($query) use ($clientId, $accessibleIds) {
                if ($clientId && in_array($clientId, $accessibleIds)) {
                    $query->where('client_id', $clientId);
                } else {
                    $query->whereIn('client_id', $accessibleIds);
                }
            });
        }

        $inboundTrend = $inboundQuery->select(DB::raw("DATE_FORMAT(received_date, '%Y-%m') as month"), DB::raw('sum(qty) as count'))
            ->groupBy('month')
            ->get()
            ->pluck('count', 'month');

        $outboundQuery = Outbound::where('outbound_date', '>=', now()->subMonths(6));
        $this->applyClientFilter($outboundQuery, $clientId);
        $outboundTrend = $outboundQuery->select(DB::raw("DATE_FORMAT(outbound_date, '%Y-%m') as month"), DB::raw('sum(qty) as count'))
            ->groupBy('month')
            ->get()
            ->pluck('count', 'month');

        $trendData = $months->map(function ($month) use ($inboundTrend, $outboundTrend) {
            return [
                'month' => $month,
                'inbound' => $inboundTrend->get($month) ?? 0,
                'outbound' => $outboundTrend->get($month) ?? 0
            ];
        });

        // 4. RMA Monitoring (Recent Swap)
        $rmaQuery = InboundDetail::whereNotNull('old_serial_number');
        $this->applyClientFilter($rmaQuery, $clientId);
        $rmaHistory = $rmaQuery->latest()->limit(5)->get();

        $rmaStatsQuery = InboundDetail::whereNotNull('old_serial_number');
        $this->applyClientFilter($rmaStatsQuery, $clientId);
        $rmaStats = $rmaStatsQuery->select(DB::raw('count(*) as count'))->first();

        // 5. Stock Monitoring (Top 10 Products by Qty)
        $totalStockQuery = Inventory::query();
        $this->applyClientFilter($totalStockQuery, $clientId);
        $totalStockCount = $totalStockQuery->sum('qty');

        $topStockQuery = Inventory::query();
        $this->applyClientFilter($topStockQuery, $clientId);
        $topStock = $topStockQuery->select('part_name', DB::raw('sum(qty) as total_qty'))
            ->groupBy('part_name')
            ->orderBy('total_qty', 'desc')
            ->limit(5)
            ->get();

        return view('dashboard.index', compact(
            'title',
            'stockByStatus',
            'utilizationByClient',
            'trendData',
            'rmaStats',
            'rmaHistory',
            'topStock',
            'totalStockCount',
            'clients'
        ));
    }

    public function utilizationByClient(Request $request): View
    {
        $title = 'utilizationByClient';
        $clientId = $request->get('client_id');
        $user = Auth::user();

        $clients = $user->isAdminWMS() ? Client::all() : $user->clients;

        $query = Outbound::with('client');
        $this->applyClientFilter($query, $clientId);

        $data = $query->select('client_id', DB::raw('count(*) as total_orders'), DB::raw('sum(qty) as total_items'))
            ->groupBy('client_id')
            ->get();

        return view('dashboard.utilization', compact('title', 'data', 'clients'));
    }

    public function rmaMonitoring(Request $request): View
    {
        $title = 'rmaMonitoring';
        $clientId = $request->get('client_id');
        $user = Auth::user();

        $clients = $user->isAdminWMS() ? Client::all() : $user->clients;

        $query = InboundDetail::whereNotNull('old_serial_number');
        $this->applyClientFilter($query, $clientId);

        $data = $query->latest()->paginate(20);

        return view('dashboard.rma', compact('title', 'data', 'clients'));
    }

    public function inboundReturn(Request $request): View
    {
        $title = 'inboundReturn';
        $clientId = $request->get('client_id');
        $user = Auth::user();

        $clients = $user->isAdminWMS() ? Client::all() : $user->clients;

        $months = collect();
        for ($i = 11; $i >= 0; $i--) {
            $months->push(now()->subMonths($i)->format('Y-m'));
        }

        $inboundQuery = Inbound::where('received_date', '>=', now()->subMonths(12));
        if ($user->isAdminWMS()) {
            if ($clientId) {
                $inboundQuery->whereHas('details', function ($query) use ($clientId) {
                    $query->where('client_id', $clientId);
                });
            }
        } else {
            $accessibleIds = $user->getAccessibleClientIds();
            $inboundQuery->whereHas('details', function ($query) use ($clientId, $accessibleIds) {
                if ($clientId && in_array($clientId, $accessibleIds)) {
                    $query->where('client_id', $clientId);
                } else {
                    $query->whereIn('client_id', $accessibleIds);
                }
            });
        }

        $inboundTrend = $inboundQuery->select(DB::raw("DATE_FORMAT(received_date, '%Y-%m') as month"), DB::raw('sum(qty) as count'))
            ->groupBy('month')
            ->get()
            ->pluck('count', 'month');

        $outboundQuery = Outbound::where('outbound_date', '>=', now()->subMonths(12));
        $this->applyClientFilter($outboundQuery, $clientId);

        $outboundTrend = $outboundQuery->select(DB::raw("DATE_FORMAT(outbound_date, '%Y-%m') as month"), DB::raw('sum(qty) as count'))
            ->groupBy('month')
            ->get()
            ->pluck('count', 'month');

        $trendData = $months->map(function ($month) use ($inboundTrend, $outboundTrend) {
            return [
                'month' => $month,
                'inbound' => $inboundTrend->get($month) ?? 0,
                'outbound' => $outboundTrend->get($month) ?? 0
            ];
        });

        return view('dashboard.inbound-return', compact('title', 'trendData', 'clients'));
    }

    public function stockMonitoring(Request $request): View
    {
        $title = 'stockMonitoring';
        $clientId = $request->get('client_id');
        $user = Auth::user();

        $clients = $user->isAdminWMS() ? Client::all() : $user->clients;

        $query = Inventory::query();
        $this->applyClientFilter($query, $clientId);

        $data = $query->select('part_name', 'part_number', 'part_description', DB::raw('sum(qty) as total_qty'))
            ->groupBy('part_name', 'part_number', 'part_description')
            ->orderBy('total_qty', 'desc')
            ->get();

        return view('dashboard.stock-monitoring', compact('title', 'data', 'clients'));
    }

    public function summaryStock(Request $request): View
    {
        $title = 'Summary Stock';
        return view('dashboard.summary-stock', compact('title'));
    }

    // --- Specialized Report for Clients (Dashboard Context) ---

    public function inventoryShow($id): View
    {
        $title = 'Summary Stock: Inventory Detail';
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

        return view('dashboard.reports.inventory-show', compact('title', 'inventory', 'history'));
    }

    public function inventoryExportPdf(Request $request): View
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

    public function inventoryExportExcel(Request $request)
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

    public function productSummaryDetail(Request $request)
    {
        $partName = $request->part_name;
        $partNumber = $request->part_number;

        $details = Inventory::with(['storageLevel.bin.rak.zone', 'client'])
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

    public function inventoryList(Request $request): View
    {
        $title = 'Summary Stock: Inventory List';
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

        $statuses = Inventory::select('status')->distinct()->pluck('status');
        $conditions = Inventory::select('condition')->distinct()->pluck('condition');
        $clients = $user->isAdminWMS() ? Client::all() : $user->clients;

        return view('dashboard.reports.inventory-list', compact('title', 'inventory', 'statuses', 'conditions', 'clients'));
    }

    public function productSummary(Request $request): View
    {
        $title = 'Summary Stock: Product Summary';
        $clientId = $request->get('client_id');
        $user = Auth::user();

        $query = Inventory::select(
            'part_name',
            'part_number',
            DB::raw('COUNT(*) as total_in'),
            DB::raw('SUM(CASE WHEN qty > 0 THEN 1 ELSE 0 END) as in_inventory'),
            DB::raw('SUM(CASE WHEN qty = 0 THEN 1 ELSE 0 END) as total_out')
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

        return view('dashboard.reports.product-summary', compact('title', 'data', 'clients'));
    }

    public function stockStatement(Request $request): View
    {
        $title = 'Summary Stock: Stock Statement';
        $user = Auth::user();
        $clients = $user->isAdminWMS() ? Client::all() : $user->clients;
        $categories = ['New PO', 'Spare from/to Replacement', 'Spare from/to Loan', 'Faulty', 'RMA', 'Spare Write-off', 'Spare Migration'];
        $requestTypes = ['New PO', 'RMA', 'Loan', 'Spare Write Off', 'Spare Migration'];

        $clientId = $request->get('client_id');

        $inboundData = InboundDetail::with(['inbound.client', 'brand', 'storageLevel.bin.rak.zone', 'productGroup'])
            ->select('inbound_detail.*')
            ->join('inbound', 'inbound_detail.inbound_id', '=', 'inbound.id')
            ->where(function ($q) {
                $q->whereNotNull('inbound_detail.storage_level_id')
                    ->orWhereExists(function ($query) {
                        $query->select(DB::raw(1))
                            ->from('outbound_detail')
                            ->whereColumn('outbound_detail.serial_number', 'inbound_detail.serial_number');
                    });
            });

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
                            $query->select(DB::raw(1))
                                ->from('inventory')
                                ->whereColumn('inventory.serial_number', 'inbound_detail.serial_number')
                                ->where('inventory.unique_id', 'like', '%' . $request->search . '%');
                        });
                });
            })
            ->latest()
            ->paginate(50);

        $sns = $inboundData->pluck('serial_number')->toArray();
        $inventories = Inventory::with('storageLevel.bin.rak.zone')->whereIn('serial_number', $sns)->get()->keyBy('serial_number');
        $outbounds = \App\Models\OutboundDetail::with('outbound')->whereIn('serial_number', $sns)->get()->keyBy('serial_number');

        foreach ($inboundData as $item) {
            $inventory = $inventories->get($item->serial_number);
            $outbound = $outbounds->get($item->serial_number);

            $item->is_outbound = (bool)$outbound;
            $item->is_in_stock = $inventory && $inventory->qty > 0;
            $item->current_inventory = $inventory;
            $item->outbound_detail = $outbound;
        }

        return view('dashboard.reports.stock-statement', compact('title', 'inboundData', 'clients', 'categories', 'requestTypes'));
    }

    public function cycleCount(Request $request): View
    {
        $title = 'Summary Stock: Cycle Count';
        $user = Auth::user();
        $clients = $user->isAdminWMS() ? Client::all() : $user->clients;

        $startDate = $request->get('start_date', Carbon::today()->format('Y-m-d'));
        $endDate = $request->get('end_date', Carbon::today()->format('Y-m-d'));
        $type = $request->get('type');
        $clientId = $request->get('client_id');

        $data = InventoryHistory::whereBetween('created_at', [
            Carbon::parse($startDate)->startOfDay(),
            Carbon::parse($endDate)->endOfDay()
        ])
            ->whereIn('type', ['Inbound', 'Outbound', 'Movement'])
            ->with(['inventory.product.brand', 'inventory.product.productGroup', 'inventory.client']);

        if ($user->isAdminWMS()) {
            if ($clientId) {
                $data->whereHas('inventory', function ($q) use ($clientId) {
                    $q->where('client_id', $clientId);
                });
            }
        } else {
            $accessibleIds = $user->getAccessibleClientIds();
            if ($clientId && in_array($clientId, $accessibleIds)) {
                $data->whereHas('inventory', function ($q) use ($clientId) {
                    $q->where('client_id', $clientId);
                });
            } else {
                $data->whereHas('inventory', function ($q) use ($accessibleIds) {
                    $q->whereIn('client_id', $accessibleIds);
                });
            }
        }

        $data = $data->when($type, function ($query) use ($type) {
            return $query->where('type', $type);
        })
            ->when($request->search, function ($query) use ($request) {
                return $query->where(function ($q) use ($request) {
                    $q->where('serial_number', 'like', '%' . $request->search . '%')
                        ->orWhere('reference_number', 'like', '%' . $request->search . '%')
                        ->orWhere('description', 'like', '%' . $request->search . '%')
                        ->orWhereHas('inventory', function ($inv) use ($request) {
                            $inv->where('unique_id', 'like', '%' . $request->search . '%');
                        });
                });
            })
            ->latest()
            ->paginate(50);

        $baseQuery = InventoryHistory::whereBetween('created_at', [
            Carbon::parse($startDate)->startOfDay(),
            Carbon::parse($endDate)->endOfDay()
        ])->whereIn('type', ['Inbound', 'Outbound', 'Movement']);

        if ($user->isAdminWMS()) {
            if ($clientId) {
                $baseQuery->whereHas('inventory', function ($q) use ($clientId) {
                    $q->where('client_id', $clientId);
                });
            }
        } else {
            $accessibleIds = $user->getAccessibleClientIds();
            if ($clientId && in_array($clientId, $accessibleIds)) {
                $baseQuery->whereHas('inventory', function ($q) use ($clientId) {
                    $q->where('client_id', $clientId);
                });
            } else {
                $baseQuery->whereHas('inventory', function ($q) use ($accessibleIds) {
                    $q->whereIn('client_id', $accessibleIds);
                });
            }
        }

        $summary = [
            'inbound' => (clone $baseQuery)->where('type', 'Inbound')->count(),
            'outbound' => (clone $baseQuery)->where('type', 'Outbound')->count(),
            'movement' => (clone $baseQuery)->where('type', 'Movement')->count(),
        ];

        return view('dashboard.reports.cycle-count', compact('title', 'data', 'startDate', 'endDate', 'summary', 'type', 'clients', 'clientId'));
    }
}
