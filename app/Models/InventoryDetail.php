<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InventoryDetail extends Model
{
    use HasFactory;

    protected $table = 'inventory_detail';

    protected $fillable = [
        'inventory_id',
        'inbound_detail_id',
    ];

    public function inventory(): BelongsTo
    {
        return $this->belongsTo(Inventory::class, 'inventory_id');
    }

    public function inboundDetail(): BelongsTo
    {
        return $this->belongsTo(InboundDetail::class, 'inbound_detail_id');
    }
}
