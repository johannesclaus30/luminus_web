<?php

namespace App\Models;

use App\Models\Admin;
use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    protected $table = 'events';

    protected $fillable = [
        'admin_id',
        'title',
        'description',
        'start_date',
        'end_date',
        'max_capacity',
        'status',
        'event_type',
        'platform',
        'platform_url',
        'venue_id',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'max_capacity' => 'integer',
        'venue_id' => 'integer',
        'status' => 'integer',
    ];

    public function images()
    {
        return $this->hasMany(ImagesEvent::class, 'event_id', 'id');
    }

    public function admin()
    {
        return $this->belongsTo(Admin::class, 'admin_id', 'id');
    }

    public function venue()
    {
        return $this->belongsTo(Venue::class, 'venue_id');
    }
}