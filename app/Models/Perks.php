<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Perks extends Model
{
    // Note: not using SoftDeletes so we don't require a DB migration here.
    // Keeping this explicitly set ensures it connects to the 'perks' table 
    // even though the model name is now singular.
    protected $table = 'perks';

    protected $fillable = [
        'title',
        'description',
        'valid_until',
        'status',
        'admin_id',
    ];

    protected $casts = [
        'valid_until' => 'date',
        'status' => 'integer',
    ];

    protected $dates = [
        'valid_until',
    ];

    /**
     * Relationship: A perk was created by an admin.
     */
    public function admin(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'admin_id');
    }

    /**
     * Relationship: A perk can have many gallery images.
     * This links to your 'images_perks' table via the PerkImage model.
     */
    public function images(): HasMany
    {
        return $this->hasMany(PerkImage::class, 'perk_id');
    }
}