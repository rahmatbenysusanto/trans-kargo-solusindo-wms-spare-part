<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Client;
use Illuminate\Http\Request;

class ApiClientController extends Controller
{
    /**
     * GET /api/clients
     * Query params:
     *   - search   : string (filter by name)
     *   - per_page : int (default 15)
     *   - page     : int (default 1)
     */
    public function index(Request $request)
    {
        $role      = $request->attributes->get('jwt_role');
        $clientIds = $request->attributes->get('jwt_client_ids', []);

        $query = Client::query();

        // Non-admin hanya bisa lihat client yang dimilikinya
        if ($role !== 'Admin WMS' && count($clientIds) > 0) {
            $query->whereIn('id', $clientIds);
        }

        if ($request->search) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        $perPage = (int) $request->get('per_page', 15);
        $perPage = min($perPage, 100); // max 100

        $clients = $query->orderBy('name')->paginate($perPage);

        return response()->json([
            'status' => true,
            'data'   => $clients->items(),
            'meta'   => [
                'current_page' => $clients->currentPage(),
                'per_page'     => $clients->perPage(),
                'total'        => $clients->total(),
                'last_page'    => $clients->lastPage(),
            ],
        ]);
    }
}
