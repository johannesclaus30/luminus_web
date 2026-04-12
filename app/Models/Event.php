<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    // Important: Tell Laravel the custom PK name
    protected $primaryKey = 'Events_ID';

    protected $fillable = [
        'Admin_ID',
        'Title',
        'Description',
        'StartDate',
        'EndDate',
        'Location',
        'MaxCapacity',
        'Status',
    ];

    // Link to the images
    public function images()
    {
        return $this->hasMany(ImagesEvent::class, 'Events_ID', 'Events_ID');
    }
}