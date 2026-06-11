<?php

namespace App\Http\Controllers;

use App\Models\InventoryHistory;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class CycleCountController extends Controller
{
    public function index(Request $request): View
    {
        $title = 'Cycle Count';
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

        // Client Filter Logic
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
                    $q->whereIn('client_id', $accessibleIds)
                        ->orWhereNull('client_id');
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

        // Summary Statistics using same logic
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
                    $q->whereIn('client_id', $accessibleIds)
                        ->orWhereNull('client_id');
                });
            }
        }

        $summary = [
            'inbound' => (clone $baseQuery)->where('type', 'Inbound')->count(),
            'outbound' => (clone $baseQuery)->where('type', 'Outbound')->count(),
            'movement' => (clone $baseQuery)->where('type', 'Movement')->count(),
        ];

        return view('inventory.cycle-count.index', compact('title', 'data', 'startDate', 'endDate', 'summary', 'type', 'clients', 'clientId'));
    }

    public function exportPdf(Request $request)
    {
        $title = 'Cycle Count Report';
        $user = Auth::user();
        $startDate = $request->get('start_date', Carbon::today()->format('Y-m-d'));
        $endDate = $request->get('end_date', Carbon::today()->format('Y-m-d'));
        $type = $request->get('type');
        $clientId = $request->get('client_id');

        $query = InventoryHistory::whereBetween('created_at', [
            Carbon::parse($startDate)->startOfDay(),
            Carbon::parse($endDate)->endOfDay()
        ])
            ->whereIn('type', ['Inbound', 'Outbound', 'Movement'])
            ->with(['inventory.product.brand', 'inventory.product.productGroup', 'inventory.client']);

        if (!$user->isAdminWMS()) {
            $accessibleIds = $user->getAccessibleClientIds();
            $query->whereHas('inventory', function ($q) use ($accessibleIds) {
                $q->where(function ($sub) use ($accessibleIds) {
                    $sub->whereIn('client_id', $accessibleIds)
                        ->orWhereNull('client_id');
                });
            });
        }
        
        if ($clientId) {
            $query->whereHas('inventory', function ($q) use ($clientId) {
                $q->where('client_id', $clientId);
            });
        }

        $data = $query->when($type, function ($q) use ($type) {
                return $q->where('type', $type);
            })
            ->latest()->get();

        return view('inventory.cycle-count.pdf', compact('title', 'data', 'startDate', 'endDate'));
    }

    public function exportExcel(Request $request)
    {
        $user = Auth::user();
        $startDate = $request->get('start_date', Carbon::today()->format('Y-m-d'));
        $endDate = $request->get('end_date', Carbon::today()->format('Y-m-d'));
        $type = $request->get('type');
        $clientId = $request->get('client_id');

        $query = InventoryHistory::whereBetween('created_at', [
            Carbon::parse($startDate)->startOfDay(),
            Carbon::parse($endDate)->endOfDay()
        ])
            ->whereIn('type', ['Inbound', 'Outbound', 'Movement'])
            ->with(['inventory.product.brand', 'inventory.product.productGroup', 'inventory.client']);

        if (!$user->isAdminWMS()) {
            $accessibleIds = $user->getAccessibleClientIds();
            $query->whereHas('inventory', function ($q) use ($accessibleIds) {
                $q->where(function ($sub) use ($accessibleIds) {
                    $sub->whereIn('client_id', $accessibleIds)
                        ->orWhereNull('client_id');
                });
            });
        }
        
        if ($clientId) {
            $query->whereHas('inventory', function ($q) use ($clientId) {
                $q->where('client_id', $clientId);
            });
        }

        $data = $query->when($type, function ($q) use ($type) {
                return $q->where('type', $type);
            })
            ->latest()->get();

        $filename = "cycle-count-" . date('Y-m-d') . ".xls";
        header("Content-Type: application/vnd.ms-excel");
        header("Content-Disposition: attachment; filename=\"$filename\"");

        echo "<table border='1'>";
        echo "<thead><tr><th>No</th><th>Date</th><th>Type</th><th>SN</th><th>Asset ID</th><th>Part Number</th><th>Parent Serial Number</th><th>Description</th><th>User</th></tr></thead>";
        echo "<tbody>";
        foreach ($data as $index => $item) {
            echo "<tr>";
            echo "<td>" . ($index + 1) . "</td>";
            echo "<td>" . $item->created_at . "</td>";
            echo "<td>" . $item->type . "</td>";
            echo "<td>'" . $item->serial_number . "</td>";
            echo "<td>" . ($item->inventory->unique_id ?? '-') . "</td>";
            echo "<td>" . ($item->inventory->part_number ?? '-') . "</td>";
            echo "<td>" . ($item->inventory->parent_serial_number ?? '-') . "</td>";
            echo "<td>" . $item->description . "</td>";
            echo "<td>" . $item->user . "</td>";
            echo "</tr>";
        }
        echo "</tbody></table>";
        exit;
    }
}
