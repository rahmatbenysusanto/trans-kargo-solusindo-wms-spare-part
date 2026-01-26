<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $title = 'Stock Overview';
        return view('dashboard.index', compact('title'));
    }

    public function utilizationByClient(): View
    {
        $title = 'utilizationByClient';
        return view('dashboard.utilization', compact('title'));
    }

    public function rmaMonitoring(): View
    {
        $title = 'rmaMonitoring';
        return view('dashboard.rma', compact('title'));
    }

    public function inboundReturn(): View
    {
        $title = 'inboundReturn';
        return view('dashboard.inbound-return', compact('title'));
    }

    public function stockMonitoring(): View
    {
        $title = 'stockMonitoring';
        return view('dashboard.stock-monitoring', compact('title'));
    }
}
