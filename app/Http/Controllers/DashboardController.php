<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Facades\DB;
use App\Models\Inventory;
use App\Models\Outbound;
use App\Models\Inbound;
use App\Models\InboundDetail;
use App\Models\Client;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $title = 'Stock Overview';
        $clientId = $request->get('client_id');
        $clients = Client::all();

        // 1. Stock Overview by Status
        $stockQuery = Inventory::query();
        if ($clientId) {
            $stockQuery->where('client_id', $clientId);
        }
        $stockByStatus = $stockQuery->select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->get();

        // 2. Utilization by Client
        $utilizationQuery = Outbound::with('client');
        if ($clientId) {
            $utilizationQuery->where('client_id', $clientId);
        }
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
        if ($clientId) {
            $inboundQuery->whereHas('details', function ($query) use ($clientId) {
                $query->where('client_id', $clientId);
            });
        }
        $inboundTrend = $inboundQuery->select(DB::raw("DATE_FORMAT(received_date, '%Y-%m') as month"), DB::raw('sum(qty) as count'))
            ->groupBy('month')
            ->get()
            ->pluck('count', 'month');

        $outboundQuery = Outbound::where('outbound_date', '>=', now()->subMonths(6));
        if ($clientId) {
            $outboundQuery->where('client_id', $clientId);
        }
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
        if ($clientId) {
            $rmaQuery->where('client_id', $clientId);
        }
        $rmaHistory = $rmaQuery->latest()->limit(5)->get();

        $rmaStatsQuery = InboundDetail::whereNotNull('old_serial_number');
        if ($clientId) {
            $rmaStatsQuery->where('client_id', $clientId);
        }
        $rmaStats = $rmaStatsQuery->select(DB::raw('count(*) as count'))->first();

        // 5. Stock Monitoring (Top 10 Products by Qty)
        $totalStockQuery = Inventory::query();
        if ($clientId) {
            $totalStockQuery->where('client_id', $clientId);
        }
        $totalStockCount = $totalStockQuery->sum('qty');

        $topStockQuery = Inventory::query();
        if ($clientId) {
            $topStockQuery->where('client_id', $clientId);
        }
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
        $clients = Client::all();

        $query = Outbound::with('client');
        if ($clientId) {
            $query->where('client_id', $clientId);
        }

        $data = $query->select('client_id', DB::raw('count(*) as total_orders'), DB::raw('sum(qty) as total_items'))
            ->groupBy('client_id')
            ->get();

        return view('dashboard.utilization', compact('title', 'data', 'clients'));
    }

    public function rmaMonitoring(Request $request): View
    {
        $title = 'rmaMonitoring';
        $clientId = $request->get('client_id');
        $clients = Client::all();

        $query = InboundDetail::whereNotNull('old_serial_number');
        if ($clientId) {
            $query->where('client_id', $clientId);
        }

        $data = $query->latest()->paginate(20);

        return view('dashboard.rma', compact('title', 'data', 'clients'));
    }

    public function inboundReturn(Request $request): View
    {
        $title = 'inboundReturn';
        $clientId = $request->get('client_id');
        $clients = Client::all();

        $months = collect();
        for ($i = 11; $i >= 0; $i--) {
            $months->push(now()->subMonths($i)->format('Y-m'));
        }

        $inboundQuery = Inbound::where('received_date', '>=', now()->subMonths(12));
        if ($clientId) {
            $inboundQuery->whereHas('details', function ($query) use ($clientId) {
                $query->where('client_id', $clientId);
            });
        }

        $inboundTrend = $inboundQuery->select(DB::raw("DATE_FORMAT(received_date, '%Y-%m') as month"), DB::raw('sum(qty) as count'))
            ->groupBy('month')
            ->get()
            ->pluck('count', 'month');

        $outboundQuery = Outbound::where('outbound_date', '>=', now()->subMonths(12));
        if ($clientId) {
            $outboundQuery->where('client_id', $clientId);
        }

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
        $clients = Client::all();

        $query = Inventory::query();
        if ($clientId) {
            $query->where('client_id', $clientId);
        }

        $data = $query->select('part_name', 'part_number', 'part_description', DB::raw('sum(qty) as total_qty'))
            ->groupBy('part_name', 'part_number', 'part_description')
            ->orderBy('total_qty', 'desc')
            ->get();

        return view('dashboard.stock-monitoring', compact('title', 'data', 'clients'));
    }
}
