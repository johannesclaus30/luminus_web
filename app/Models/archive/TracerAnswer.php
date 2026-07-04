<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TracerAnswer extends Model
{
    protected $table = 'tracer_answers';

    protected $fillable = [
        'tracer_response_id',
        'tq_id',
        'answer_value',
    ];

    public function response()
    {
        return $this->belongsTo(TracerResponse::class, 'tracer_response_id');
    }

    public function question()
    {
        return $this->belongsTo(TracerQuestion::class, 'tq_id');
    }
}