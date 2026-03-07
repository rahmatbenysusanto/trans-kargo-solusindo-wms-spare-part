<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

class Invoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_number',
        'invoice_date',
        'amount',
        'description',
        'file_path'
    ];

    /**
     * Get all inbounds linked to this invoice.
     */
    public function inbounds(): MorphToMany
    {
        return $this->morphedByMany(Inbound::class, 'linkable', 'invoice_links');
    }

    /**
     * Get all outbounds linked to this invoice.
     */
    public function outbounds(): MorphToMany
    {
        return $this->morphedByMany(Outbound::class, 'linkable', 'invoice_links');
    }
}
