<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Repost extends Model
{
    protected $table = 'reposts';
    
    protected $fillable = [
        'alumni_id',
        'post_id',
        'caption',
        'moderation_status',
    ];
    
    public function alumni(): BelongsTo
    {
        return $this->belongsTo(Alumni::class);
    }
    
    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class);
    }
}