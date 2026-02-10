<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Outbound extends Model
{
    use HasFactory;

    protected $table = 'outbound';

    protected $fillable = [
        'category',
        'client_id',
        'number',
        'ntt_dn_number',
        'tks_dn_number',
        'tks_invoice_number',
        'rma_number',
        'itsm_number',
        'qty',
        'status',
        'outbound_date',
        'outbound_by'
    ];

    public function details(): HasMany
    {
        return $this->hasMany(OutboundDetail::class, 'outbound_id');
    }

    public function client()
    {
        return $this->belongsTo(Client::class, 'client_id');
    }
}
