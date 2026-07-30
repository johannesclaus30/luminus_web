<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Address extends Model
{
    protected $fillable = [
        'alumni_id',
        'address_type',
        'region',
        'province',
        'municipality',
        'barangay',
        'street',
        'zip_code',
        'latitude',
        'longitude',
    ];
    
    /**
     * Get the alumni that owns this address.
     */
    public function alumni()
    {
        return $this->belongsTo(Alumni::class);
    }
}