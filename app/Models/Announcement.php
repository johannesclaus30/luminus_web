<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;

class Announcement extends Model
{
    protected $fillable = [
        'admin_id',
        'title',
        'announcement_description',
        'date_posted',
        'scheduled_at', // Changed from scheduled_post_at
        'status',
        'scheduled_at' // Also ensure this is in fillable
    ];

    protected $casts = [
        'scheduled_at' => 'datetime',
        'date_posted' => 'datetime',
        'scheduled_at' => 'datetime:Y-m-d H:i:s', // Ensure proper format
    ];

    // If you need to maintain backward compatibility temporarily
    public function getScheduledPostAtAttribute($value)
    {
        // If scheduled_post_at is still in the database, use it as fallback
        if ($value !== null) {
            return Carbon::parse($value);
        }
        
        // Otherwise use scheduled_at
        return $this->scheduled_at;
    }

    // Relationship with images
    public function images(): HasMany
    {
        return $this->hasMany(ImagesAnnouncement::class);
    }
}