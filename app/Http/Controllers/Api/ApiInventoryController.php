<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Inventory;
use App\Models\InboundDetail;
use App\Models\InventoryHistory;
use App\Models\OutboundDetail;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ApiInventoryController extends Controller
{
    /**
     * GET /api/inventory
     *
     * Query params:
     *   - search       : string (unique_id, part_name, serial_number, part_number)
     *   - client_id    : int
     *   - status       : string
     *   - condition    : string
     *   - per_page     : int (default 15, max 100)
     *   - page         : int (default 1)
     */
    public function index(Request $request)
    {
        $role      = $request->attributes->get('jwt_role');
        $clientIds = $request->attributes->get('jwt_client_ids', []);

        $query = Inventory::with([
            'client:id,name',
            'storageLevel.bin.rak.zone',
        ])->latest();

        // Scope client access
        if ($role !== 'Admin WMS') {
            $query->where(function ($q) use ($clientIds) {
                $q->whereIn('client_id', $clientIds)
                    ->orWhereNull('client_id');
            });
        }

        // Filter client_id
        if ($request->client_id) {
            $query->where('client_id', $request->client_id);
        }

        // Filter status
        if ($request->status) {
            $query->where('status', $request->status);
        }

        // Filter condition
        if ($request->condition) {
            $query->where('condition', $request->condition);
        }

        // Filter pencarian
        if ($request->search) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('unique_id', 'like', "%$s%")
                    ->orWhere('part_name', 'like', "%$s%")
                    ->orWhere('serial_number', 'like', "%$s%")
                    ->orWhere('part_number', 'like', "%$s%");
            });
        }

        $perPage = min((int) $request->get('per_page', 15), 100);
        $data    = $query->paginate($perPage);

        return response()->json([
            'status' => true,
            'data'   => $data->map(function ($item) {
                $location = null;
                if ($item->storageLevel && $item->storageLevel->bin && $item->storageLevel->bin->rak && $item->storageLevel->bin->rak->zone) {
                    $location = implode('-', [
                        $item->storageLevel->bin->rak->zone->name,
                        $item->storageLevel->bin->rak->name,
                        $item->storageLevel->bin->name,
                        $item->storageLevel->name,
                    ]);
                }
                return [
                    'id'                   => $item->id,
                    'unique_id'            => $item->unique_id,
                    'client'               => $item->client ? ['id' => $item->client->id, 'name' => $item->client->name] : null,
                    'part_name'            => $item->part_name,
                    'part_number'          => $item->part_number,
                    'part_description'     => $item->part_description,
                    'serial_number'        => $item->serial_number,
                    'parent_serial_number' => $item->parent_serial_number,
                    'qty'                  => $item->qty,
                    'status'               => $item->status,
                    'condition'            => $item->condition,
                    'location'             => $location,
                    'last_staging_date'    => $item->last_staging_date,
                    'last_movement_date'   => $item->last_movement_date,
                    'created_at'           => $item->created_at?->toDateTimeString(),
                    'updated_at'           => $item->updated_at?->toDateTimeString(),
                ];
            }),
            'meta' => [
                'current_page' => $data->currentPage(),
                'per_page'     => $data->perPage(),
                'total'        => $data->total(),
                'last_page'    => $data->lastPage(),
            ],
        ]);
    }

    /**
     * GET /api/inventory/stock-statement
     */
    public function stockStatement(Request $request)
    {
        $role      = $request->attributes->get('jwt_role');
        $clientIds = $request->attributes->get('jwt_client_ids', []);
        $clientId  = $request->get('client_id');

        $query = InboundDetail::with(['inbound.client', 'brand', 'storageLevel.bin.rak.zone', 'productGroup'])
            ->select('inbound_detail.*')
            ->join('inbound', 'inbound_detail.inbound_id', '=', 'inbound.id');

        // Scope client access
        if ($role !== 'Admin WMS') {
            $query->where(function ($q) use ($clientIds) {
                $q->whereIn('inbound.client_id', $clientIds)
                    ->orWhereNull('inbound.client_id');
            });
        }

        if ($clientId) {
            $query->where('inbound.client_id', $clientId);
        }

        if ($request->category) {
            $query->where('inbound.category', $request->category);
        }

        if ($request->search) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('inbound_detail.serial_number', 'like', "%$s%")
                    ->orWhere('inbound_detail.part_name', 'like', "%$s%")
                    ->orWhere('inbound.number', 'like', "%$s%");
            });
        }

        $perPage = min((int) $request->get('per_page', 15), 100);
        $data    = $query->latest('inbound_detail.created_at')->paginate($perPage);

        $sns = $data->pluck('serial_number')->toArray();
        $inventories = Inventory::whereIn('serial_number', $sns)->get()->keyBy('serial_number');
        $outbounds   = OutboundDetail::with('outbound')->whereIn('serial_number', $sns)->get()->keyBy('serial_number');

        return response()->json([
            'status' => true,
            'data'   => $data->map(function ($item) use ($inventories, $outbounds) {
                $inv = $inventories->get($item->serial_number);
                $out = $outbounds->get($item->serial_number);

                return [
                    'serial_number' => $item->serial_number,
                    'part_name'     => $item->part_name,
                    'part_number'   => $item->part_number,
                    'client'        => $item->inbound->client->name ?? '-',
                    'inbound'       => [
                        'number' => $item->inbound->number,
                        'date'   => $item->inbound->received_date,
                    ],
                    'outbound' => $out ? [
                        'number' => $out->outbound->number,
                        'date'   => $out->outbound->outbound_date,
                    ] : null,
                    'status' => ($inv && $inv->qty > 0) ? 'In Stock' : ($out ? 'Outbound' : 'N/A'),
                ];
            }),
            'meta' => [
                'current_page' => $data->currentPage(),
                'per_page'     => $data->perPage(),
                'total'        => $data->total(),
                'last_page'    => $data->lastPage(),
            ],
        ]);
    }

    /**
     * GET /api/inventory/cycle-count
     */
    public function cycleCount(Request $request)
    {
        $role      = $request->attributes->get('jwt_role');
        $clientIds = $request->attributes->get('jwt_client_ids', []);
        
        $startDate = $request->get('start_date', Carbon::today()->format('Y-m-d'));
        $endDate   = $request->get('end_date', Carbon::today()->format('Y-m-d'));

        $query = InventoryHistory::with(['inventory.client'])
            ->whereBetween('created_at', [
                Carbon::parse($startDate)->startOfDay(),
                Carbon::parse($endDate)->endOfDay()
            ])
            ->whereIn('type', ['Inbound', 'Outbound', 'Movement']);

        if ($role !== 'Admin WMS') {
            $query->whereHas('inventory', function ($q) use ($clientIds) {
                $q->whereIn('client_id', $clientIds)
                    ->orWhereNull('client_id');
            });
        }

        if ($request->client_id) {
            $query->whereHas('inventory', function ($q) use ($request) {
                $q->where('client_id', $request->client_id);
            });
        }

        if ($request->search) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('serial_number', 'like', "%$s%")
                    ->orWhere('reference_number', 'like', "%$s%")
                    ->orWhere('description', 'like', "%$s%");
            });
        }

        $perPage = min((int) $request->get('per_page', 15), 100);
        $data    = $query->latest()->paginate($perPage);

        return response()->json([
            'status' => true,
            'data'   => $data->map(function ($item) {
                return [
                    'date'             => $item->created_at->toDateTimeString(),
                    'type'             => $item->type,
                    'reference_number' => $item->reference_number,
                    'serial_number'    => $item->serial_number,
                    'description'      => $item->description,
                    'user'             => $item->user,
                    'from_location'    => $item->from_location,
                    'to_location'      => $item->to_location,
                    'client'           => $item->inventory->client->name ?? '-',
                ];
            }),
            'meta' => [
                'current_page' => $data->currentPage(),
                'per_page'     => $data->perPage(),
                'total'        => $data->total(),
                'last_page'    => $data->lastPage(),
            ],
        ]);
    }

    /**
     * GET /api/inventory/check-outbounded
     * Check if a serial number or unique_id exists and has been outbounded.
     */
    public function checkOutbounded(Request $request)
    {
        $search = $request->get('search');
        if (!$search) {
            return response()->json(['status' => false, 'message' => 'Search term is required']);
        }

        // Find in Inventory (master data)
        $inventory = Inventory::where('serial_number', $search)
            ->orWhere('unique_id', $search)
            ->first();

        if (!$inventory) {
            return response()->json(['status' => false, 'message' => 'Item not found in master data.']);
        }

        // Check if outbounded (qty = 0 OR has outbound history)
        $isOutbounded = ($inventory->qty == 0) || OutboundDetail::where('serial_number', $inventory->serial_number)->exists();

        if (!$isOutbounded) {
            return response()->json([
                'status' => false, 
                'message' => 'Item found but is still IN STOCK. Replacement is only allowed for outbounded items.',
                'data' => [
                    'serial_number' => $inventory->serial_number,
                    'unique_id'     => $inventory->unique_id,
                    'qty'           => $inventory->qty
                ]
            ]);
        }

        return response()->json([
            'status' => true,
            'message' => 'Item validated and ready for replacement.',
            'data'   => [
                'id'                   => $inventory->id,
                'unique_id'            => $inventory->unique_id,
                'serial_number'        => $inventory->serial_number,
                'part_name'            => $inventory->part_name,
                'part_number'          => $inventory->part_number,
                'part_description'     => $inventory->part_description,
                'brand'                => $inventory->brand->name ?? '-',
                'product_group'        => $inventory->productGroup->name ?? '-',
            ]
        ]);
    }
}
