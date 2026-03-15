<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Inventory;
use Illuminate\Http\Request;

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
            if (count($clientIds) === 0) {
                return response()->json([
                    'status'  => false,
                    'message' => 'No accessible clients found for this user.',
                ], 403);
            }
            $query->whereIn('client_id', $clientIds);
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
}
