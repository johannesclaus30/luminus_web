<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TracerQuestion extends Model
{
    protected $table = 'tracer_questions';

    protected $fillable = [
        'form_id',
        'type',
        'question_text',
        'description',
        'is_required',
        'order_priority',
        'settings',
    ];

    protected $casts = [
        'is_required' => 'boolean',
        'settings' => 'array',
    ];

    public function form()
    {
        return $this->belongsTo(TracerForm::class, 'form_id');
    }

    public function options()
    {
        return $this->hasMany(TracerAnswerOption::class, 'tq_id');
    }
}