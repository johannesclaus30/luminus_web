<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PerkImage extends Model
{
    // Table name for perk images (adjust if your DB uses a different name)
    protected $table = 'images_perks';

    protected $fillable = [
        'perk_id',
        'image_path',
    ];

    /**
     * Relationship back to the Perks model
     */
    public function perk()
    {
        return $this->belongsTo(Perks::class, 'perk_id');
    }
}
