<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InventoryMovement extends Model
{
    protected $fillable = [
        'inventory_id',
        'from_storage_level_id',
        'to_storage_level_id',
        'user_id',
        'type',
        'description',
    ];

    public function inventory()
    {
        return $this->belongsTo(Inventory::class);
    }

    public function fromStorageLevel()
    {
        return $this->belongsTo(StorageLevel::class, 'from_storage_level_id');
    }

    public function toStorageLevel()
    {
        return $this->belongsTo(StorageLevel::class, 'to_storage_level_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
