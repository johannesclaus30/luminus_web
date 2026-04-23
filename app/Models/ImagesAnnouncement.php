<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ImagesAnnouncement extends Model
{
    protected $table = 'images_announcements';

    protected $fillable = [
        'announcement_id',
        'image_path',
    ];

    protected $casts = [
        'announcement_id' => 'integer',
    ];

    public function announcement()
    {
        return $this->belongsTo(Announcement::class, 'announcement_id', 'id');
    }
}