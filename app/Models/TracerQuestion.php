<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TracerQuestion extends Model
{
    protected $table = 'tracer_questions';

    protected $fillable = [
        'form_id',
        'type',
        'question_text',
        'section_header',
        'has_other_option',
        'description',
        'is_required',
        'order_priority',
        'settings',
    ];

    protected $casts = [
        'is_required' => 'boolean',
        'has_other_option' => 'boolean',
        'settings' => 'array',
        'order_priority' => 'integer',
    ];

    public function form(): BelongsTo
    {
        return $this->belongsTo(TracerForm::class, 'form_id');
    }

    public function options(): HasMany
    {
        return $this->hasMany(TracerAnswerOption::class, 'tq_id');
    }

    public function answers(): HasMany
    {
        return $this->hasMany(TracerAnswer::class, 'tq_id');
    }
}