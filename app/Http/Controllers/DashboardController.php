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
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $model = $query->getModel();

        $callback = function ($q) use ($user, $clientId, $column) {
            if ($user->isAdminWMS()) {
                if ($clientId) {
                    $q->where($column, $clientId);
                }
            } else {
                $accessibleIds = $user->getAccessibleClientIds();
                if ($clientId && in_array($clientId, $accessibleIds)) {
                    $q->where($column, $clientId);
                } else {
                    $q->where(function ($sub) use ($column, $accessibleIds) {
                        $sub->whereIn($column, $accessibleIds)
                            ->orWhereNull($column);
                    });
                }
            }
        };

        if ($model instanceof \App\Models\InboundDetail && $column === 'client_id') {
            $query->whereHas('inbound', $callback);
        } elseif ($model instanceof \App\Models\OutboundDetail && $column === 'client_id') {
            $query->whereHas('outbound', $callback);
        } else {
            $callback($query);
        }

        return $query;
    }

    /**
     * AJAX endpoint for dashboard card modals
     * type: in-stock | outbounded | rma
     */
    public function dashboardData(Request $request): \Illuminate\Http\JsonResponse
    {
        $type = $request->get('type', 'in-stock');
        $page = (int) $request->get('page', 1);
        $perPage = 10;
        $user = Auth::user();

        $data = match ($type) {
            'in-stock' => $this->getDashboardInventoryData($request, $user, fn($q) => $q->where('qty', '>', 0)),
            'outbounded' => $this->getDashboardInventoryData($request, $user, fn($q) => $q->where('qty', 0)->whereNotIn('status', ['Write-off', 'write-off'])),
            'write-off' => $this->getDashboardInventoryData($request, $user, fn($q) => $q->whereIn('status', ['Write-off', 'write-off'])),
            'rma' => $this->getDashboardRmaData($request, $user),
            default => collect(),
        };

        $total = $data->count();
        $items = $data->forPage($page, $perPage)->values();

        return response()->json([
            'items'      => $items,
            'total'      => $total,
            'page'       => $page,
            'perPage'    => $perPage,
            'lastPage'   => max(1, (int) ceil($total / $perPage)),
        ]);
    }

    private function getDashboardInventoryData(Request $request, $user, callable $filter)
    {
        $clientId = $request->get('client_id');
        $query = Inventory::with(['storageLevel.bin.rak.zone', 'client', 'brand', 'productGroup']);

        if ($user->isAdminWMS()) {
            if ($clientId) $query->where('client_id', $clientId);
        } else {
            $ids = $user->getAccessibleClientIds();
            $query->where(fn($q) => $q->whereIn('client_id', $ids)->orWhereNull('client_id'));
        }

        $filter($query);
        return $query->latest()->get()->map(fn($i) => [
            'id'             => $i->id,
            'unique_id'      => $i->unique_id,
            'serial_number'  => $i->serial_number,
            'part_name'      => $i->part_name,
            'part_number'    => $i->part_number,
            'condition'      => $i->condition,
            'status'         => $i->status,
            'client_name'    => $i->client?->name ?? '-',
            'brand'          => $i->brand?->name ?? '-',
            'group'          => $i->productGroup?->name ?? '-',
            'location'       => $i->storageLevel ? "{$i->storageLevel->bin->rak->zone->name}-{$i->storageLevel->bin->rak->name}-{$i->storageLevel->bin->name}-{$i->storageLevel->name}" : '-',
            'qty'            => $i->qty,
        ]);
    }

    private function getDashboardRmaData(Request $request, $user)
    {
        $clientId = $request->get('client_id');
        $query = InboundDetail::with(['inbound.client'])->whereNotNull('old_serial_number');

        if (!$user->isAdminWMS()) {
            $ids = $user->getAccessibleClientIds();
            $query->whereHas('inbound', fn($q) => $q->whereIn('client_id', $ids));
        } elseif ($clientId) {
            $query->whereHas('inbound', fn($q) => $q->where('client_id', $clientId));
        }

        return $query->latest()->get()->map(fn($i) => [
            'part_name'          => $i->part_name,
            'part_number'        => $i->part_number,
            'old_serial_number'  => $i->old_serial_number,
            'serial_number'      => $i->serial_number,
            'condition'          => $i->condition,
            'date'               => $i->created_at?->format('d/m/Y H:i'),
            'client_name'        => $i->inbound?->client?->name ?? '-',
        ]);
    }

    public function index(Request $request): View
    {
        $title = 'Stock Overview';
        $clientId = $request->get('client_id');
        $user = Auth::user();

        $clients = $user->getAvailableClients();

        // 1. Stock Overview by Status
        $stockQuery = Inventory::query();
        $this->applyClientFilter($stockQuery, $clientId);
        $stockByStatus = $stockQuery->select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->get();

        // 1b. Stock by Condition
        $conditionQuery = Inventory::query();
        $this->applyClientFilter($conditionQuery, $clientId);
        $stockByCondition = $conditionQuery->select('condition', DB::raw('count(*) as count'))
            ->groupBy('condition')
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
        $this->applyClientFilter($inboundQuery, $clientId);

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
                'inbound' => (int)($inboundTrend->get($month) ?? 0),
                'outbound' => (int)($outboundTrend->get($month) ?? 0)
            ];
        });

        // 3b. Month-over-month percent change
        $trendArr = $trendData->values();
        $prevInbound = $trendArr->count() > 1 ? $trendArr[$trendArr->count() - 2]['inbound'] ?? 0 : 0;
        $prevOutbound = $trendArr->count() > 1 ? $trendArr[$trendArr->count() - 2]['outbound'] ?? 0 : 0;
        $currentInbound = $trendArr->count() > 0 ? $trendArr[$trendArr->count() - 1]['inbound'] ?? 0 : 0;
        $currentOutbound = $trendArr->count() > 0 ? $trendArr[$trendArr->count() - 1]['outbound'] ?? 0 : 0;

        $inboundChange = $prevInbound > 0 ? round((($currentInbound - $prevInbound) / $prevInbound) * 100, 1) : 0;
        $outboundChange = $prevOutbound > 0 ? round((($currentOutbound - $prevOutbound) / $prevOutbound) * 100, 1) : 0;

        // 4. RMA Monitoring (Recent Swap)
        $rmaQuery = InboundDetail::with(['inbound'])->whereNotNull('old_serial_number');
        $this->applyClientFilter($rmaQuery, $clientId);
        $rmaHistory = $rmaQuery->latest()->limit(5)->get();

        // RMA stats - total + this month
        $rmaStatsAllQuery = InboundDetail::whereNotNull('old_serial_number');
        $this->applyClientFilter($rmaStatsAllQuery, $clientId);
        $rmaStats = $rmaStatsAllQuery->select(DB::raw('count(*) as count'))->first();

        $rmaMonthQuery = InboundDetail::whereNotNull('old_serial_number')
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year);
        $this->applyClientFilter($rmaMonthQuery, $clientId);
        $rmaThisMonth = $rmaMonthQuery->count();

        // 5. Stock Monitoring
        $totalStockQuery = Inventory::query();
        $this->applyClientFilter($totalStockQuery, $clientId);
        $totalStockCount = $totalStockQuery->sum('qty');

        // Count items actually in stock (qty > 0)
        $inStockQuery = Inventory::where('qty', '>', 0);
        $this->applyClientFilter($inStockQuery, $clientId);
        $inStockCount = $inStockQuery->count();

        // Count outbounded items
        $outboundedQuery = Inventory::where('qty', 0)->whereNotIn('status', ['Write-off', 'write-off']);
        $this->applyClientFilter($outboundedQuery, $clientId);
        $outboundedCount = $outboundedQuery->count();

        $topStockQuery = Inventory::query();
        $this->applyClientFilter($topStockQuery, $clientId);
        $topStock = $topStockQuery->select('part_name', DB::raw('sum(qty) as total_qty'))
            ->groupBy('part_name')
            ->orderBy('total_qty', 'desc')
            ->limit(5)
            ->get();

        // 6. Recent Activity (last 7 movements)
        $recentActivity = \App\Models\InventoryHistory::with(['inventory'])
            ->latest()
            ->limit(7)
            ->get();

        // 7. Inbound this month count
        $inboundMonthQuery = Inbound::whereMonth('received_date', now()->month)
            ->whereYear('received_date', now()->year);
        $this->applyClientFilter($inboundMonthQuery, $clientId);
        $inboundMonthCount = $inboundMonthQuery->count();

        // 8. Outbound this month count
        $outboundMonthQuery = Outbound::whereMonth('outbound_date', now()->month)
            ->whereYear('outbound_date', now()->year);
        $this->applyClientFilter($outboundMonthQuery, $clientId);
        $outboundMonthCount = $outboundMonthQuery->count();

        return view('dashboard.index', compact(
            'title',
            'stockByStatus',
            'stockByCondition',
            'utilizationByClient',
            'trendData',
            'inboundChange',
            'outboundChange',
            'rmaStats',
            'rmaThisMonth',
            'rmaHistory',
            'topStock',
            'totalStockCount',
            'inStockCount',
            'outboundedCount',
            'inboundMonthCount',
            'outboundMonthCount',
            'recentActivity',
            'clients'
        ));
    }

    public function utilizationByClient(Request $request): View
    {
        $title = 'utilizationByClient';
        $clientId = $request->get('client_id');
        $user = Auth::user();

        $clients = $user->getAvailableClients();

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

        $clients = $user->getAvailableClients();

        $query = InboundDetail::whereNotNull('old_serial_number');
        $this->applyClientFilter($query, $clientId);

        $data = $query->latest()->paginate(20);

        return view('dashboard.rma', compact('title', 'data', 'clients'));
    }

    public function rmaMonitoringDelete(Request $request, $id)
    {
        $user = Auth::user();

        if (!$user->isAdminWMS()) {
            return back()->with('error', 'You do not have permission to revert RMA records.');
        }

        // Cari InboundDetail RMA
        $detail = InboundDetail::whereNotNull('old_serial_number')->findOrFail($id);

        // Cek apakah sudah di-put-away (ada InventoryDetail)
        $inventoryDetail = \App\Models\InventoryDetail::where('inbound_detail_id', $detail->id)->first();

        if ($inventoryDetail) {
            $inventory = $inventoryDetail->inventory;

            if ($inventory) {
                // Revert inventory: set qty = 0, status unavailable
                // Data inventory tetap ada, cuma tidak dihitung sebagai stok
                $inventory->update([
                    'qty'    => 0,
                    'status' => 'unavailable',
                ]);

                // Catat history reverted
                \App\Models\InventoryHistory::create([
                    'inventory_id'     => $inventory->id,
                    'serial_number'    => $inventory->serial_number,
                    'type'             => 'Adjustment',
                    'category'         => 'RMA Revert',
                    'reference_number' => 'RMA-' . $detail->id,
                    'description'      => 'RMA reverted by ' . $user->name . ' - SN: ' . $inventory->serial_number . ' (InboundDetail #' . $detail->id . ' soft-deleted)',
                    'user'             => $user->name,
                    'to_location'      => 'VOID',
                ]);
            }
        }

        // Soft delete InboundDetail (data tetap ada, cuma di-flag)
        $detail->delete();

        return back()->with('success', 'RMA record reverted successfully.');
    }

    public function inboundReturn(Request $request): View
    {
        $title = 'inboundReturn';
        $clientId = $request->get('client_id');
        $user = Auth::user();

        $clients = $user->getAvailableClients();

        $months = collect();
        for ($i = 11; $i >= 0; $i--) {
            $months->push(now()->subMonths($i)->format('Y-m'));
        }

        $inboundQuery = Inbound::where('received_date', '>=', now()->subMonths(12));
        $this->applyClientFilter($inboundQuery, $clientId);

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

        $clients = $user->getAvailableClients();

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

        $inventory = \App\Models\Inventory::with([
            'storageLevel.bin.rak.zone',
            'client',
            'product.brand',
            'product.productGroup',
            'details.inboundDetail.inbound',
        ]);

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

        // Collect SNs to batch-fetch outbound data
        $sns = $inventory->pluck('serial_number')->toArray();
        $outboundDetails = \App\Models\OutboundDetail::with('outbound')
            ->whereIn('serial_number', $sns)
            ->get()
            ->keyBy('serial_number');

        $filename = "inventory-list-" . date('Y-m-d') . ".xls";

        header("Content-Type: application/vnd.ms-excel");
        header("Content-Disposition: attachment; filename=\"$filename\"");

        $yellow = 'background-color: #FFFF00;';
        $bold = 'font-weight: bold;';

        echo "<table border='1'>";
        echo "<thead>";
        echo "<tr>";
        echo "<th style='{$yellow}{$bold}'>Movement Category</th>";
        echo "<th style='{$yellow}{$bold}'>Stock Category</th>";
        echo "<th style='{$yellow}{$bold}'>Request Type</th>";
        echo "<th style='{$bold}'>NTT Requestor</th>";
        echo "<th style='{$bold}'>Request Date</th>";
        echo "<th style='{$bold}'>Product Group</th>";
        echo "<th style='{$bold}'>Brand</th>";
        echo "<th style='{$bold}'>Product Number (SKU)</th>";
        echo "<th style='{$bold}'>Product Description</th>";
        echo "<th style='{$bold}'>Serial Number (SN)</th>";
        echo "<th style='{$bold}'>Parent SN</th>";
        echo "<th style='{$bold}'>Qty</th>";
        echo "<th style='{$yellow}{$bold}'>WH Asset Number</th>";
        echo "<th style='{$bold}'>Stock Status</th>";
        echo "<th style='{$yellow}{$bold}'>Stock Condition</th>";
        echo "<th style='{$yellow}{$bold}'>Stock Location (Rack ID)</th>";
        echo "<th style='{$bold}'>eCapex#</th>";
        echo "<th style='{$bold}'>SAP PO#</th>";
        echo "<th style='{$bold}'>Vendor/Supplier DN#</th>";
        echo "<th style='{$bold}'>NTT RN#</th>";
        echo "<th style='{$bold}'>Received Date</th>";
        echo "<th style='{$bold}'>NTT DN#</th>";
        echo "<th style='{$bold}'>Delivery Date</th>";
        echo "<th style='{$bold}'>Transkargo DN#</th>";
        echo "<th style='{$bold}'>Transkargo Invoice#</th>";
        echo "<th style='{$bold}'>Staging Date</th>";
        echo "<th style='{$bold}'>ITSM#</th>";
        echo "<th style='{$bold}'>RMA#</th>";
        echo "<th style='{$bold}'>Processed by</th>";
        echo "<th style='{$bold}'>Client Name</th>";
        echo "<th style='{$bold}'>Client Contact</th>";
        echo "<th style='{$bold}'>Pickup/Shipment Address</th>";
        echo "<th style='{$bold}'>Shipment Status</th>";
        echo "</tr>";
        echo "</thead>";
        echo "<tbody>";

        foreach ($inventory as $index => $item) {
            // Find inbound data via InventoryDetail -> InboundDetail -> Inbound
            $inboundDetail = $item->details->first()?->inboundDetail;
            $inbound = $inboundDetail?->inbound;

            // Find outbound data
            $outboundDetail = $outboundDetails->get($item->serial_number);
            $outbound = $outboundDetail?->outbound;

            $brand = $item->product?->brand?->name ?? '-';
            $group = $item->product?->productGroup?->name ?? '-';
            $storage = $item->storageLevel
                ? "{$item->storageLevel->bin->rak->zone->name}-{$item->storageLevel->bin->rak->name}-{$item->storageLevel->bin->name}-{$item->storageLevel->name}"
                : '-';

            // Determine movement category
            $movementCategory = $inbound?->category ?? ($outbound?->category ?? '-');

            echo "<tr>";
            echo "<td>{$movementCategory}</td>";
            echo "<td>" . ($inbound?->category ?? $outbound?->category ?? '-') . "</td>";
            echo "<td>" . ($inbound?->request_type ?? $outbound?->request_type ?? '-') . "</td>";
            echo "<td>" . ($inbound?->ntt_requestor ?? $outbound?->ntt_requestor ?? '-') . "</td>";
            echo "<td>" . ($inbound?->request_date ?? $outbound?->request_date ?? '-') . "</td>";
            echo "<td>{$group}</td>";
            echo "<td>{$brand}</td>";
            echo "<td>{$item->part_number}</td>";
            echo "<td>" . ($item->part_description ?? '-') . "</td>";
            echo "<td>'{$item->serial_number}</td>";
            echo "<td>" . ($item->parent_serial_number ?? '-') . "</td>";
            echo "<td>{$item->qty}</td>";
            echo "<td>{$item->unique_id}</td>";
            echo "<td>{$item->status}</td>";
            echo "<td>{$item->condition}</td>";
            echo "<td>{$storage}</td>";
            echo "<td>" . ($inbound?->ecapex_number ?? '-') . "</td>";
            echo "<td>" . ($inbound?->sap_po_number ?? '-') . "</td>";
            echo "<td>" . ($inbound?->vendor_dn_number ?? '-') . "</td>";
            echo "<td>" . ($inbound?->receiving_note ?? '-') . "</td>";
            echo "<td>" . ($inbound?->received_date ?? '-') . "</td>";
            echo "<td>" . ($inbound?->ntt_dn_number ?? $outbound?->ntt_dn_number ?? '-') . "</td>";
            echo "<td>" . ($inbound?->delivery_date ?? '-') . "</td>";
            echo "<td>" . ($inbound?->tks_dn_number ?? $outbound?->tks_dn_number ?? '-') . "</td>";
            echo "<td>" . ($inbound?->tks_invoice_number ?? $outbound?->tks_invoice_number ?? '-') . "</td>";
            echo "<td>" . ($inboundDetail?->staging_date ?? $item->last_staging_date ?? '-') . "</td>";
            echo "<td>" . ($inbound?->itsm_number ?? '-') . "</td>";
            echo "<td>" . ($inbound?->rma_number ?? $outbound?->rma_number ?? '-') . "</td>";
            echo "<td>" . ($inbound?->received_by ?? $outbound?->outbound_by ?? '-') . "</td>";
            echo "<td>" . ($item->client?->name ?? '-') . "</td>";
            echo "<td>" . ($inbound?->client_contact ?? $outbound?->client_contact ?? '-') . "</td>";
            echo "<td>" . ($inbound?->pickup_address ?? $outbound?->pickup_address ?? '-') . "</td>";
            echo "<td>" . ($inbound?->shipment_status ?? $outbound?->shipment_status ?? '-') . "</td>";
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
        $clients = $user->getAvailableClients();

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

        // Quick Search: multi SN / Asset ID (paste multiple values)
        $quickSearch = $request->get('quick_search');
        if ($quickSearch && trim($quickSearch) !== '') {
            $terms = preg_split('/[\r\n,;]+/', $quickSearch);
            $terms = array_map('trim', $terms);
            $terms = array_filter($terms, fn($v) => $v !== '');

            if (!empty($terms)) {
                $query->where(function ($q) use ($terms) {
                    $q->whereIn('serial_number', $terms)
                      ->orWhereIn('unique_id', $terms);
                });
            }
        }

        $query = $query->when($request->search, function ($query) use ($request) {
            return $query->where(function ($q) use ($request) {
                $q->where('part_name', 'like', '%' . $request->search . '%')
                    ->orWhere('part_number', 'like', '%' . $request->search . '%');
            });
        })
            ->groupBy('part_name', 'part_number')
            ->orderBy('part_name');

        $data = $query->paginate(15);
        $clients = $user->getAvailableClients();

        return view('dashboard.reports.product-summary', compact('title', 'data', 'clients'));
    }

    public function stockStatement(Request $request): View
    {
        $title = 'Summary Stock: Stock Statement';
        $user = Auth::user();
        $clients = $user->getAvailableClients();
        $categories = ['New PO', 'Spare from/to Replacement', 'Spare from/to Loan', 'Faulty', 'RMA', 'Spare Write-off', 'Spare Migration', 'Spare Return'];
        $requestTypes = ['New PO', 'RMA', 'Loan', 'Spare Write Off', 'Spare Migration', 'Return'];

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
        $clients = $user->getAvailableClients();

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

    public function receivingMonitoring(Request $request): View
    {
        $title = 'Receiving Monitoring';
        $clientId = $request->get('client_id');
        $user = Auth::user();
        $clients = $user->getAvailableClients();

        $query = Inbound::with('client')->latest();
        $this->applyClientFilter($query, $clientId);

        $inbound = $query->when($request->category, function ($q) use ($request) {
            return $q->where('category', $request->category);
        })
            ->when($request->request_type, function ($q) use ($request) {
                return $q->where('request_type', $request->request_type);
            })
            ->when($request->search, function ($q) use ($request) {
                return $q->where(function ($sub) use ($request) {
                    $sub->where('number', 'like', '%' . $request->search . '%')
                        ->orWhere('rma_number', 'like', '%' . $request->search . '%')
                        ->orWhere('itsm_number', 'like', '%' . $request->search . '%')
                        ->orWhere('vendor', 'like', '%' . $request->search . '%');
                });
            })
            ->paginate(15);

        $categories = ['New PO', 'Spare from/to Replacement', 'Spare from/to Loan', 'Faulty', 'RMA', 'Spare Write-off', 'Spare Migration', 'Spare Return'];
        $requestTypes = ['New PO', 'RMA', 'Loan', 'Spare Write Off', 'Spare Migration', 'Return'];

        return view('dashboard.monitoring.receiving', compact('title', 'inbound', 'categories', 'requestTypes', 'clients'));
    }

    public function outboundMonitoring(Request $request): View
    {
        $title = 'Outbound Monitoring';
        $clientId = $request->get('client_id');
        $user = Auth::user();
        $clients = $user->getAvailableClients();

        $query = Outbound::with('client')->latest();
        $this->applyClientFilter($query, $clientId);

        $data = $query->when($request->category, function ($q) use ($request) {
            return $q->where('category', $request->category);
        })
            ->when($request->search, function ($q) use ($request) {
                return $q->where(function ($sub) use ($request) {
                    $sub->where('number', 'like', '%' . $request->search . '%')
                        ->orWhere('sap_po_number', 'like', '%' . $request->search . '%')
                        ->orWhere('ntt_dn_number', 'like', '%' . $request->search . '%')
                        ->orWhere('tks_dn_number', 'like', '%' . $request->search . '%')
                        ->orWhere('tks_invoice_number', 'like', '%' . $request->search . '%')
                        ->orWhere('rma_number', 'like', '%' . $request->search . '%')
                        ->orWhere('itsm_number', 'like', '%' . $request->search . '%');
                });
            })
            ->paginate(15);

        $categories = ['Spare Write Off', 'Spare from/to Loan', 'RMA', 'Faulty', 'Spare from/to Replacement', 'Spare Migration'];

        return view('dashboard.monitoring.outbound', compact('title', 'data', 'clients', 'categories'));
    }

    public function receivingMonitoringDetail($id): View
    {
        $inbound = Inbound::with(['client', 'details.brand', 'details.storageLevel.zone', 'details.storageLevel.rak', 'details.storageLevel.bin', 'invoices'])->findOrFail($id);

        $user = Auth::user();
        if (!$user->isAdminWMS()) {
            $accessibleIds = $user->getAccessibleClientIds();
            if (!in_array($inbound->client_id, $accessibleIds)) {
                abort(403, 'Unauthorized access to this record.');
            }
        }

        $title = 'Receiving Detail Monitoring';
        return view('dashboard.monitoring.receiving_show', compact('title', 'inbound'));
    }

    public function outboundMonitoringDetail($id): View
    {
        $outbound = Outbound::with(['client', 'details.brand', 'invoices'])->findOrFail($id);

        $user = Auth::user();
        if (!$user->isAdminWMS()) {
            $accessibleIds = $user->getAccessibleClientIds();
            if (!in_array($outbound->client_id, $accessibleIds)) {
                abort(403, 'Unauthorized access to this record.');
            }
        }

        $title = 'Outbound Detail Monitoring';
        return view('dashboard.monitoring.outbound_show', compact('title', 'outbound'));
    }
}
