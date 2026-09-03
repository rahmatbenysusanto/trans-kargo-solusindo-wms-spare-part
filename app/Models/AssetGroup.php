<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class AssetGroup extends Model
{
    use HasFactory;

    protected $table = 'asset_groups';

    protected $fillable = [
        'group_number',
        'name',
        'description',
        'created_by',
    ];

    public function items(): HasMany
    {
        return $this->hasMany(AssetGroupItem::class, 'asset_group_id');
    }

    public function inventories(): HasManyThrough
    {
        return $this->hasManyThrough(
            Inventory::class,
            AssetGroupItem::class,
            'asset_group_id',
            'id',
            'id',
            'inventory_id'
        );
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
