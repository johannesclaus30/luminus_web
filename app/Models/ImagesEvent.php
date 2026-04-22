<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ImagesEvent extends Model
{
    protected $table = 'images_events';

    protected $fillable = [
        'event_id',
        'image_path',
    ];

    protected $casts = [
        'event_id' => 'integer',
    ];

    /**
     * Relationship back to the Event
     */
    public function event()
    {
        return $this->belongsTo(Event::class, 'event_id', 'id');
    }

    public function getImageUrlAttribute(): ?string
    {
        if (! $this->image_path) {
            return null;
        }

        $baseUrl = rtrim((string) config('filesystems.disks.supabase_admin.url'), '/');

        if ($baseUrl === '') {
            return null;
        }

        return $baseUrl . '/' . ltrim($this->image_path, '/');
    }
}