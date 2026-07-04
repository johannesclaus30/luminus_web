<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TracerPhase extends Model
{
    protected $table = 'tracer_phases';

    protected $fillable = [
        'form_id',
        'title',
        'subtitle',
        'icon',
        'color',
        'order_priority',
    ];

    // ═══════════════════════════════════════
    // RELATIONSHIPS
    // ═══════════════════════════════════════

    public function form(): BelongsTo
    {
        return $this->belongsTo(TracerForm::class, 'form_id');
    }

    public function sections(): HasMany
    {
        return $this->hasMany(TracerSection::class, 'phase_id')->orderBy('order_priority');
    }
}