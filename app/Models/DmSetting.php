<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DmSetting extends Model
{
    protected $table = 'dm_settings';
    public $timestamps = true;
    
    protected $fillable = [
        'user_id',
        'user_type',
        'contact_id',
        'contact_type',
        'is_archived',
        'is_muted',
        'is_hidden'
    ];
    
    protected $casts = [
        'is_archived' => 'boolean',
        'is_muted' => 'boolean',
        'is_hidden' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}