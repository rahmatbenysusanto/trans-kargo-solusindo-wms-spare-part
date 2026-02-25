<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $title = 'Stock Overview';

        // 1. Stock Overview by Status
        $stockByStatus = \App\Models\Inventory::select('status', \Illuminate\Support\Facades\DB::raw('count(*) as count'))
            ->groupBy('status')
            ->get();

        // 2. Utilization by Client
        $utilizationByClient = \App\Models\Outbound::with('client')
            ->select('client_id', \Illuminate\Support\Facades\DB::raw('count(*) as count'))
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

        $inboundTrend = \App\Models\Inbound::select(\Illuminate\Support\Facades\DB::raw("DATE_FORMAT(received_date, '%Y-%m') as month"), \Illuminate\Support\Facades\DB::raw('sum(qty) as count'))
            ->where('received_date', '>=', now()->subMonths(6))
            ->groupBy('month')
            ->get()
            ->pluck('count', 'month');

        $outboundTrend = \App\Models\Outbound::select(\Illuminate\Support\Facades\DB::raw("DATE_FORMAT(outbound_date, '%Y-%m') as month"), \Illuminate\Support\Facades\DB::raw('sum(qty) as count'))
            ->where('outbound_date', '>=', now()->subMonths(6))
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
        $rmaHistory = \App\Models\InboundDetail::whereNotNull('old_serial_number')
            ->latest()
            ->limit(5)
            ->get();

        $rmaStats = \App\Models\InboundDetail::whereNotNull('old_serial_number')
            ->select(\Illuminate\Support\Facades\DB::raw('count(*) as count'))
            ->first();

        // 5. Stock Monitoring (Top 10 Products by Qty)
        $totalStockCount = \App\Models\Inventory::sum('qty');
        $topStock = \App\Models\Inventory::select('part_name', \Illuminate\Support\Facades\DB::raw('sum(qty) as total_qty'))
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
            'totalStockCount'
        ));
    }

    public function utilizationByClient(): View
    {
        $title = 'utilizationByClient';
        $data = \App\Models\Outbound::with('client')
            ->select('client_id', \Illuminate\Support\Facades\DB::raw('count(*) as total_orders'), \Illuminate\Support\Facades\DB::raw('sum(qty) as total_items'))
            ->groupBy('client_id')
            ->get();

        return view('dashboard.utilization', compact('title', 'data'));
    }

    public function rmaMonitoring(): View
    {
        $title = 'rmaMonitoring';
        $data = \App\Models\InboundDetail::whereNotNull('old_serial_number')
            ->latest()
            ->paginate(20);

        return view('dashboard.rma', compact('title', 'data'));
    }

    public function inboundReturn(): View
    {
        $title = 'inboundReturn';

        $months = collect();
        for ($i = 11; $i >= 0; $i--) {
            $months->push(now()->subMonths($i)->format('Y-m'));
        }

        $inboundTrend = \App\Models\Inbound::select(\Illuminate\Support\Facades\DB::raw("DATE_FORMAT(received_date, '%Y-%m') as month"), \Illuminate\Support\Facades\DB::raw('sum(qty) as count'))
            ->where('received_date', '>=', now()->subMonths(12))
            ->groupBy('month')
            ->get()
            ->pluck('count', 'month');

        $outboundTrend = \App\Models\Outbound::select(\Illuminate\Support\Facades\DB::raw("DATE_FORMAT(outbound_date, '%Y-%m') as month"), \Illuminate\Support\Facades\DB::raw('sum(qty) as count'))
            ->where('outbound_date', '>=', now()->subMonths(12))
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

        return view('dashboard.inbound-return', compact('title', 'trendData'));
    }

    public function stockMonitoring(): View
    {
        $title = 'stockMonitoring';
        $data = \App\Models\Inventory::select('part_name', 'part_number', 'part_description', \Illuminate\Support\Facades\DB::raw('sum(qty) as total_qty'))
            ->groupBy('part_name', 'part_number', 'part_description')
            ->orderBy('total_qty', 'desc')
            ->get();

        return view('dashboard.stock-monitoring', compact('title', 'data'));
    }
}
