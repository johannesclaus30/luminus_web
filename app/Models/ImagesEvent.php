<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ImagesEvent extends Model
{
    // 1. Tell Laravel the exact table name from your ERD
    protected $table = 'images_event';

    // 2. Define the custom Primary Key
    protected $primaryKey = 'ImgEvent_ID';

    // 3. Disable standard timestamps since you're using a manual 'CreatedAt'
    public $timestamps = false;

    // 4. List the columns that are allowed to be filled
    protected $fillable = [
        'Events_ID',
        'ImagePath',
        'CreatedAt'
    ];

    /**
     * Relationship back to the Event
     */
    public function event()
    {
        return $this->belongsTo(Event::class, 'Events_ID', 'Events_ID');
    }
}