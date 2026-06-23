<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TracerAnswerOption extends Model
{
    protected $table = 'tracer_answer_options';

    protected $fillable = [
        'tq_id',
        'option_label',
        'option_value',
    ];

    public function question(): BelongsTo
    {
        return $this->belongsTo(TracerQuestion::class, 'tq_id');
    }
}