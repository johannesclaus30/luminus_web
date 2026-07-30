<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Comment extends Model
{
    protected $table = 'comments';
    
    protected $fillable = [
        'alumni_id',
        'post_id',
        'comment',
        'moderation_status',
        'parent_id',
        'announcement_id',
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