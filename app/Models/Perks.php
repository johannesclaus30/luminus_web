<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Perks extends Model
{
    protected $table = 'perks';

    protected $fillable = [
        'PerkTitle',
        'PerkDescription',
        'PerkValidity',
        'PerkImage'
    ];

    
    protected $casts = [
        'PerkValidity' => 'date',
    ];

}
