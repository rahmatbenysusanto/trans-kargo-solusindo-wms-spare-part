<?php

namespace App\Http\Controllers;

use App\Models\InventoryHistory;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Carbon\Carbon;

class CycleCountController extends Controller
{
    public function index(Request $request): View
    {
        $title = 'Cycle Count';
        $date = $request->get('date', Carbon::today()->format('Y-m-d'));

        // Data keluar masuk barang pada hari tersebut
        $data = InventoryHistory::whereDate('created_at', $date)
            ->with(['inventory'])
            ->latest()
            ->paginate(20);

        // Summary for today
        $summary = [
            'inbound' => InventoryHistory::whereDate('created_at', $date)->where('type', 'Inbound')->count(),
            'outbound' => InventoryHistory::whereDate('created_at', $date)->where('type', 'Outbound')->count(),
            'movement' => InventoryHistory::whereDate('created_at', $date)->where('type', 'Movement')->count(),
        ];

        return view('inventory.cycle-count.index', compact('title', 'data', 'date', 'summary'));
    }
}
