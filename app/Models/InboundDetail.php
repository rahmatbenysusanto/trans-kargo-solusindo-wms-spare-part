<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InboundDetail extends Model
{
    use HasFactory;

    protected $table = 'inbound_detail';

    protected $fillable = [
        'inbound_id',
        'product_id',
        'part_name',
        'part_number',
        'description',
        'qty',
        'serial_number',
        'old_serial_number',
        'condition',
    ];

    public function inbound(): BelongsTo
    {
        return $this->belongsTo(Inbound::class, 'inbound_id');
    }
}
