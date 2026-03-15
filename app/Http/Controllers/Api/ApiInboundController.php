<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Inbound;
use Illuminate\Http\Request;

class ApiInboundController extends Controller
{
    /**
     * GET /api/inbound
     *
     * Query params:
     *   - search       : string (number, rma_number, itsm_number, vendor)
     *   - client_id    : int
     *   - category     : string
     *   - request_type : string
     *   - status       : string
     *   - date_from    : date (Y-m-d) filter received_date
     *   - date_to      : date (Y-m-d) filter received_date
     *   - per_page     : int (default 15, max 100)
     *   - page         : int (default 1)
     */
    public function index(Request $request)
    {
        $role      = $request->attributes->get('jwt_role');
        $clientIds = $request->attributes->get('jwt_client_ids', []);

        $query = Inbound::with(['client:id,name'])->latest();

        // Scope client access
        if ($role !== 'Admin WMS') {
            if (count($clientIds) === 0) {
                return response()->json([
                    'status' => false,
                    'message' => 'No accessible clients found for this user.',
                ], 403);
            }
            $query->whereIn('client_id', $clientIds);
        }

        // Filter client_id (jika admin memilih salah satu)
        if ($request->client_id) {
            $query->where('client_id', $request->client_id);
        }

        // Filter category
        if ($request->category) {
            $query->where('category', $request->category);
        }

        // Filter request_type
        if ($request->request_type) {
            $query->where('request_type', $request->request_type);
        }

        // Filter status
        if ($request->status) {
            $query->where('status', $request->status);
        }

        // Filter tanggal received_date
        if ($request->date_from) {
            $query->whereDate('received_date', '>=', $request->date_from);
        }
        if ($request->date_to) {
            $query->whereDate('received_date', '<=', $request->date_to);
        }

        // Filter pencarian
        if ($request->search) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('number', 'like', "%$s%")
                    ->orWhere('rma_number', 'like', "%$s%")
                    ->orWhere('itsm_number', 'like', "%$s%")
                    ->orWhere('vendor', 'like', "%$s%")
                    ->orWhere('receiving_note', 'like', "%$s%")
                    ->orWhere('sttb', 'like', "%$s%");
            });
        }

        $perPage = min((int) $request->get('per_page', 15), 100);
        $data    = $query->paginate($perPage);

        return response()->json([
            'status' => true,
            'data'   => $data->map(function ($item) {
                return [
                    'id'               => $item->id,
                    'number'           => $item->number,
                    'receiving_note'   => $item->receiving_note,
                    'category'         => $item->category,
                    'request_type'     => $item->request_type,
                    'client'           => $item->client ? ['id' => $item->client->id, 'name' => $item->client->name] : null,
                    'status'           => $item->status,
                    'shipment_status'  => $item->shipment_status,
                    'qty'              => $item->qty,
                    'vendor'           => $item->vendor,
                    'sttb'             => $item->sttb,
                    'rma_number'       => $item->rma_number,
                    'itsm_number'      => $item->itsm_number,
                    'sap_po_number'    => $item->sap_po_number,
                    'ntt_dn_number'    => $item->ntt_dn_number,
                    'tks_dn_number'    => $item->tks_dn_number,
                    'tks_invoice_number' => $item->tks_invoice_number,
                    'received_date'    => $item->received_date,
                    'received_by'      => $item->received_by,
                    'delivery_date'    => $item->delivery_date,
                    'created_at'       => $item->created_at?->toDateTimeString(),
                    'updated_at'       => $item->updated_at?->toDateTimeString(),
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
