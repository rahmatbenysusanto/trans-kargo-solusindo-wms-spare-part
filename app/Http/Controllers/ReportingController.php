<?php

namespace App\Http\Controllers;

use App\Models\Inventory;
use App\Models\InventoryHistory;
use App\Models\OutboundDetail;
use App\Models\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReportingController extends Controller
{
    public function stockOnHand(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $query = Inventory::with(['client', 'storageLevel.bin.rak.zone']);

        $user = Auth::user();
        if ($user->isAdminWMS()) {
            if ($request->client_id) {
                $query->where('client_id', $request->client_id);
            }
        } else {
            $accessibleIds = $user->getAccessibleClientIds();
            if ($request->client_id && in_array($request->client_id, $accessibleIds)) {
                $query->where('client_id', $request->client_id);
            } else {
                $query->where(function ($q) use ($accessibleIds) {
                    $q->whereIn('client_id', $accessibleIds)
                        ->orWhereNull('client_id');
                });
            }
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
        $clients = $user->getAvailableClients();
        $title = 'Stock on Hand';

        return view('reporting.stock_on_hand', compact('data', 'clients', 'title'));
    }

    public function movementHistory(Request $request)
    {
        $query = InventoryHistory::with(['inventory.client']);
        $user = Auth::user();

        if (!$user->isAdminWMS()) {
            $accessibleIds = $user->getAccessibleClientIds();
            $query->whereHas('inventory', function ($q) use ($accessibleIds) {
                $q->where(function ($sub) use ($accessibleIds) {
                    $sub->whereIn('client_id', $accessibleIds)
                        ->orWhereNull('client_id');
                });
            });
        }

        if ($request->sn) {
            $sn = $request->sn;
            $query->where(function ($q) use ($sn) {
                $q->where('serial_number', 'like', "%$sn%")
                  ->orWhereHas('inventory', function ($invQuery) use ($sn) {
                      $invQuery->where('part_number', 'like', "%$sn%")
                               ->orWhere('unique_id', 'like', "%$sn%");
                  });
            });
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

    public function movementHistoryCsv(Request $request)
    {
        $query = InventoryHistory::with(['inventory.client']);
        $user = Auth::user();

        if (!$user->isAdminWMS()) {
            $accessibleIds = $user->getAccessibleClientIds();
            $query->whereHas('inventory', function ($q) use ($accessibleIds) {
                $q->where(function ($sub) use ($accessibleIds) {
                    $sub->whereIn('client_id', $accessibleIds)
                        ->orWhereNull('client_id');
                });
            });
        }

        if ($request->sn) {
            $sn = $request->sn;
            $query->where(function ($q) use ($sn) {
                $q->where('serial_number', 'like', "%$sn%")
                  ->orWhereHas('inventory', function ($invQuery) use ($sn) {
                      $invQuery->where('part_number', 'like', "%$sn%")
                               ->orWhere('unique_id', 'like', "%$sn%");
                  });
            });
        }

        if ($request->start_date && $request->end_date) {
            $query->whereBetween('created_at', [$request->start_date . ' 00:00:00', $request->end_date . ' 23:59:59']);
        }

        if ($request->type) {
            $query->where('type', $request->type);
        }

        $data = $query->latest()->limit(10000)->get();

        $filename = "movement-history-" . date('Y-m-d') . ".csv";

        header("Content-Type: text/csv; charset=UTF-8");
        header("Content-Disposition: attachment; filename=\"$filename\"");
        header("Pragma: no-cache");
        header("Expires: 0");

        $output = fopen('php://output', 'w');

        // Header row
        fputcsv($output, [
            'Timestamp',
            'Client',
            'Activity Type',
            'Category',
            'Reference Number',
            'Serial Number',
            'WH Asset Number',
            'Part Number',
            'Parent Serial Number',
            'Part Description',
            'From Location',
            'To Location',
            'User',
            'Description',
        ]);

        foreach ($data as $h) {
            fputcsv($output, [
                $h->created_at?->format('Y-m-d H:i:s') ?? '-',
                $h->inventory?->client?->name ?? '-',
                $h->type ?? '-',
                $h->category ?? '-',
                $h->reference_number ?? '-',
                $h->serial_number ?? '-',
                $h->inventory?->unique_id ?? '-',
                $h->inventory?->part_number ?? '-',
                $h->inventory?->parent_serial_number ?? '-',
                $h->inventory?->part_description ?? '-',
                $h->from_location ?? '-',
                $h->to_location ?? '-',
                $h->user ?? '-',
                $h->description ?? '-',
            ]);
        }

        fclose($output);
        exit;
    }

    public function utilizationReport(Request $request)
    {
        // Utilization is typically focused on outbound for support/incidents
        $query = OutboundDetail::with(['outbound.client']);

        $user = Auth::user();
        if ($user->isAdminWMS()) {
            if ($request->client_id) {
                $query->whereHas('outbound', function ($q) use ($request) {
                    $q->where('client_id', $request->client_id);
                });
            }
        } else {
            $accessibleIds = $user->getAccessibleClientIds();
            if ($request->client_id && in_array($request->client_id, $accessibleIds)) {
                $query->whereHas('outbound', function ($q) use ($request) {
                    $q->where('client_id', $request->client_id);
                });
            } else {
                $query->whereHas('outbound', function ($q) use ($accessibleIds) {
                    $q->where(function ($sub) use ($accessibleIds) {
                        $sub->whereIn('client_id', $accessibleIds)
                            ->orWhereNull('client_id');
                    });
                });
            }
        }

        if ($request->start_date && $request->end_date) {
            $query->whereHas('outbound', function ($q) use ($request) {
                $q->whereBetween('outbound_date', [$request->start_date, $request->end_date]);
            });
        }

        $data = $query->latest()->paginate(20);
        $clients = $user->getAvailableClients();
        $title = 'Utilization Report';

        return view('reporting.utilization', compact('data', 'clients', 'title'));
    }
}
