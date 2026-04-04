<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ImagesAnnouncement extends Model
{
    protected $table = 'images_announcements';

    protected $primaryKey = 'ImgAnnouncement_ID';

    protected $fillable = [
        'Announcement_ID',
        'ImagePath',   // ✅ THIS WAS MISSING
        'UploadTime',
    ];

    public function announcement()
    {
        return $this->belongsTo(
            Announcement::class,
            'Announcement_ID',
            'Announcement_ID'
        );
    }
}