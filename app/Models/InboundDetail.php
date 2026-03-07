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
        'wh_asset_number',
        'serial_number',
        'old_serial_number',
        'parent_sn',
        'condition',
        'stock_status',
        'storage_level_id',
        'staging_date',
        'brand_id',
        'product_group_id'
    ];

    public function inbound(): BelongsTo
    {
        return $this->belongsTo(Inbound::class, 'inbound_id');
    }

    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class, 'brand_id');
    }

    public function storageLevel(): BelongsTo
    {
        return $this->belongsTo(StorageLevel::class, 'storage_level_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function productGroup(): BelongsTo
    {
        return $this->belongsTo(ProductGroup::class, 'product_group_id');
    }
}
