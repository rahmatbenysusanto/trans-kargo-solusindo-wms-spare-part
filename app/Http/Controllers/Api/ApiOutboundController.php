<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Outbound;
use Illuminate\Http\Request;

class ApiOutboundController extends Controller
{
    /**
     * GET /api/outbound
     *
     * Query params:
     *   - search       : string (number, sap_po_number, ntt_dn_number, tks_dn_number, rma_number, itsm_number)
     *   - client_id    : int
     *   - category     : string
     *   - status       : string
     *   - date_from    : date (Y-m-d) filter outbound_date
     *   - date_to      : date (Y-m-d) filter outbound_date
     *   - per_page     : int (default 15, max 100)
     *   - page         : int (default 1)
     */
    public function index(Request $request)
    {
        $role      = $request->attributes->get('jwt_role');
        $clientIds = $request->attributes->get('jwt_client_ids', []);

        $query = Outbound::with(['client:id,name'])->latest();

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

        // Filter category
        if ($request->category) {
            $query->where('category', $request->category);
        }

        // Filter status
        if ($request->status) {
            $query->where('status', $request->status);
        }

        // Filter tanggal outbound_date
        if ($request->date_from) {
            $query->whereDate('outbound_date', '>=', $request->date_from);
        }
        if ($request->date_to) {
            $query->whereDate('outbound_date', '<=', $request->date_to);
        }

        // Filter pencarian
        if ($request->search) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('number', 'like', "%$s%")
                    ->orWhere('sap_po_number', 'like', "%$s%")
                    ->orWhere('ntt_dn_number', 'like', "%$s%")
                    ->orWhere('tks_dn_number', 'like', "%$s%")
                    ->orWhere('tks_invoice_number', 'like', "%$s%")
                    ->orWhere('rma_number', 'like', "%$s%")
                    ->orWhere('itsm_number', 'like', "%$s%");
            });
        }

        $perPage = min((int) $request->get('per_page', 15), 100);
        $data    = $query->paginate($perPage);

        return response()->json([
            'status' => true,
            'data'   => $data->map(function ($item) {
                return [
                    'id'                  => $item->id,
                    'number'              => $item->number,
                    'category'            => $item->category,
                    'request_type'        => $item->request_type,
                    'client'              => $item->client ? ['id' => $item->client->id, 'name' => $item->client->name] : null,
                    'status'              => $item->status,
                    'shipment_status'     => $item->shipment_status,
                    'qty'                 => $item->qty,
                    'sap_po_number'       => $item->sap_po_number,
                    'ntt_dn_number'       => $item->ntt_dn_number,
                    'tks_dn_number'       => $item->tks_dn_number,
                    'tks_invoice_number'  => $item->tks_invoice_number,
                    'rma_number'          => $item->rma_number,
                    'itsm_number'         => $item->itsm_number,
                    'ntt_requestor'       => $item->ntt_requestor,
                    'pickup_address'      => $item->pickup_address,
                    'outbound_date'       => $item->outbound_date,
                    'outbound_by'         => $item->outbound_by,
                    'created_at'          => $item->created_at?->toDateTimeString(),
                    'updated_at'          => $item->updated_at?->toDateTimeString(),
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
