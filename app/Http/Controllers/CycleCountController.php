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
        $clients = \App\Models\Client::all();

        $startDate = $request->get('start_date', Carbon::today()->format('Y-m-d'));
        $endDate = $request->get('end_date', Carbon::today()->format('Y-m-d'));
        $type = $request->get('type');
        $clientId = $request->get('client_id');

        $data = InventoryHistory::whereBetween('created_at', [
            Carbon::parse($startDate)->startOfDay(),
            Carbon::parse($endDate)->endOfDay()
        ])
            ->whereIn('type', ['Inbound', 'Outbound', 'Movement'])
            ->with(['inventory.product.brand', 'inventory.product.productGroup', 'inventory.client'])
            ->when($clientId, function ($query) use ($clientId) {
                return $query->whereHas('inventory', function ($q) use ($clientId) {
                    $q->where('client_id', $clientId);
                });
            })
            ->when($type, function ($query) use ($type) {
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

        // Summary for the selected range - matching the logic above
        $baseQuery = InventoryHistory::whereBetween('created_at', [
            Carbon::parse($startDate)->startOfDay(),
            Carbon::parse($endDate)->endOfDay()
        ])->whereIn('type', ['Inbound', 'Outbound', 'Movement'])
            ->when($clientId, function ($query) use ($clientId) {
                return $query->whereHas('inventory', function ($q) use ($clientId) {
                    $q->where('client_id', $clientId);
                });
            });

        $summary = [
            'inbound' => (clone $baseQuery)->where('type', 'Inbound')->count(),
            'outbound' => (clone $baseQuery)->where('type', 'Outbound')->count(),
            'movement' => (clone $baseQuery)->where('type', 'Movement')->count(),
        ];

        return view('inventory.cycle-count.index', compact('title', 'data', 'startDate', 'endDate', 'summary', 'type', 'clients', 'clientId'));
    }
}
