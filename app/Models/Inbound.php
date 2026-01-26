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
        'client_id',
        'number',
        'reff_number',
        'receiving_note',
        'sttb',
        'courier_delivery_note',
        'courier_invoice',
        'rma_number',
        'itsm_number',
        'vendor',
        'qty',
        'received_date',
        'received_by',
        'status'
    ];

    public function details(): HasMany
    {
        return $this->hasMany(InboundDetail::class, 'inbound_id');
    }
}
