<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StorageRak extends Model
{
    protected $table = 'storage_rak';
    protected $fillable = ['storage_zone_id', 'name'];

    public function zone(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(StorageZone::class, 'storage_zone_id');
    }
}
