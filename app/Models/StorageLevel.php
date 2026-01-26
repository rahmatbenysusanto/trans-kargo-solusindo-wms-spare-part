<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StorageLevel extends Model
{
    protected $table = 'storage_level';
    protected $fillable = ['storage_zone_id', 'storage_rak_id', 'storage_bin_id', 'name'];

    public function zone(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(StorageZone::class, 'storage_zone_id');
    }

    public function rak(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(StorageRak::class, 'storage_rak_id');
    }

    public function bin(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(StorageBin::class, 'storage_bin_id');
    }
}
