<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Announcement extends Model
{
    protected $primaryKey = 'Announcement_ID';

    protected $fillable = [
        'AnnouncementTitle',
        'AnnouncementDescription',
        'DatePosted',
    ];

    public function images()
    {
        return $this->hasMany(
            ImagesAnnouncement::class,
            'Announcement_ID',
            'Announcement_ID'
        );
    }
}