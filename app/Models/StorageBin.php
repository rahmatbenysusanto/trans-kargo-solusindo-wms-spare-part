<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StorageBin extends Model
{
    protected $table = 'storage_bin';
    protected $fillable = ['storage_zone_id', 'storage_rak_id', 'name'];

    public function zone(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(StorageZone::class, 'storage_zone_id');
    }

    public function rak(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(StorageRak::class, 'storage_rak_id');
    }
}
