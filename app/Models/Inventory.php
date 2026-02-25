<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Inventory extends Model
{
    use HasFactory;

    protected $table = 'inventory';

    protected $fillable = [
        'unique_id',
        'client_id',
        'storage_level_id',
        'product_id',
        'brand_id',
        'product_group_id',
        'qty',
        'part_name',
        'part_number',
        'condition',
        'part_description',
        'serial_number',
        'parent_serial_number',
        'status',
        'last_staging_date',
        'last_movement_date',
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class, 'client_id');
    }

    public function storageLevel(): BelongsTo
    {
        return $this->belongsTo(StorageLevel::class, 'storage_level_id');
    }

    public function details(): HasMany
    {
        return $this->hasMany(InventoryDetail::class, 'inventory_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id');
    }
}
