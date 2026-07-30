<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Post extends Model
{
    use HasFactory;

    protected $table = 'posts';

    protected $fillable = [
        'alumni_id',
        'caption',
        'moderation_status',
        'visibility',
        'is_draft',
    ];

    protected $casts = [
        'is_draft' => 'boolean',
    ];

    /**
     * Get the alumni that owns the post.
     */
    public function alumni(): BelongsTo
    {
        return $this->belongsTo(Alumni::class);
    }

    /**
     * Get the images for the post.
     */
    public function images(): HasMany
    {
        return $this->hasMany(ImagesPost::class);
    }

    /**
     * Get the comments for the post.
     */
    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    /**
     * Get the reactions for the post.
     */
    public function reactions(): HasMany
    {
        return $this->hasMany(Reaction::class);
    }

    /**
     * Get the reposts for the post.
     */
    public function reposts(): HasMany
    {
        return $this->hasMany(Repost::class);
    }
}