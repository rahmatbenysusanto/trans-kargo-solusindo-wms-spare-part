<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Inbound extends Model
{
    use HasFactory;

    protected $table = 'inbound';

    protected $fillable = [
        'category',
        'request_type',
        'ntt_requestor',
        'request_date',
        'client_id',
        'client_contact',
        'pickup_address',
        'number',
        'reff_number',
        'receiving_note',
        'sttb',
        'courier_delivery_note',
        'courier_invoice',
        'rma_number',
        'itsm_number',
        'ecapex_number',
        'sap_po_number',
        'vendor_dn_number',
        'tks_dn_number',
        'tks_invoice_number',
        'ntt_dn_number',
        'delivery_date',
        'vendor',
        'qty',
        'received_date',
        'received_by',
        'status',
        'shipment_status'
    ];

    public function details(): HasMany
    {
        return $this->hasMany(InboundDetail::class, 'inbound_id');
    }

    public function invoices(): \Illuminate\Database\Eloquent\Relations\MorphToMany
    {
        return $this->morphToMany(Invoice::class, 'linkable', 'invoice_links');
    }

    public function client(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Client::class, 'client_id');
    }
}
