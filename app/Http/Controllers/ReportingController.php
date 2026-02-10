<?php

namespace App\Http\Controllers;

use App\Models\Inventory;
use App\Models\InventoryHistory;
use App\Models\OutboundDetail;
use App\Models\Client;
use Illuminate\Http\Request;

class ReportingController extends Controller
{
    public function stockOnHand(Request $request)
    {
        $query = Inventory::with(['client', 'storageLevel.bin.rak.zone']);

        if ($request->client_id) {
            $query->where('client_id', $request->client_id);
        }

        if ($request->search) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('serial_number', 'like', "%$s%")
                    ->orWhere('part_name', 'like', "%$s%")
                    ->orWhere('part_number', 'like', "%$s%")
                    ->orWhere('unique_id', 'like', "%$s%");
            });
        }

        $data = $query->where('qty', '>', 0)->latest()->paginate(20);
        $clients = Client::all();
        $title = 'Stock on Hand';

        return view('reporting.stock_on_hand', compact('data', 'clients', 'title'));
    }

    public function movementHistory(Request $request)
    {
        $query = InventoryHistory::with(['inventory.client']);

        if ($request->sn) {
            $query->where('serial_number', 'like', "%$request->sn%");
        }

        if ($request->start_date && $request->end_date) {
            $query->whereBetween('created_at', [$request->start_date . ' 00:00:00', $request->end_date . ' 23:59:59']);
        }

        if ($request->type) {
            $query->where('type', $request->type);
        }

        $data = $query->latest()->paginate(20);
        $title = 'Movement History';

        return view('reporting.movement_history', compact('data', 'title'));
    }

    public function utilizationReport(Request $request)
    {
        // Utilization is typically focused on outbound for support/incidents
        $query = OutboundDetail::with(['outbound.client']);

        if ($request->client_id) {
            $query->whereHas('outbound', function ($q) use ($request) {
                $q->where('client_id', $request->client_id);
            });
        }

        if ($request->start_date && $request->end_date) {
            $query->whereHas('outbound', function ($q) use ($request) {
                $q->whereBetween('outbound_date', [$request->start_date, $request->end_date]);
            });
        }

        $data = $query->latest()->paginate(20);
        $clients = Client::all();
        $title = 'Utilization Report';

        return view('reporting.utilization', compact('data', 'clients', 'title'));
    }
}
