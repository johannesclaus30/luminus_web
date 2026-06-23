<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Announcement extends Model
{
    protected $table = 'announcements';

    protected $fillable = [
        'admin_id',
        'title',
        'announcement_description',
        'date_posted',
        'scheduled_post_at',
        'status',
    ];

    protected $casts = [
        'admin_id' => 'integer',
        'status' => 'integer',
        'date_posted' => 'datetime',
        'scheduled_post_at' => 'datetime',
    ];

    public function images()
    {
        return $this->hasMany(ImagesAnnouncement::class, 'announcement_id', 'id');
    }

    public function admin()
    {
        return $this->belongsTo(Admin::class, 'admin_id', 'id');
    }
}