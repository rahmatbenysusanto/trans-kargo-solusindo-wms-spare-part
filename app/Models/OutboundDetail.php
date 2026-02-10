<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OutboundDetail extends Model
{
    use HasFactory;

    protected $table = 'outbound_detail';

    protected $fillable = [
        'outbound_id',
        'product_id',
        'part_name',
        'part_number',
        'description',
        'qty',
        'serial_number',
        'old_serial_number',
        'condition'
    ];

    public function outbound(): BelongsTo
    {
        return $this->belongsTo(Outbound::class, 'outbound_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id');
    }
}
