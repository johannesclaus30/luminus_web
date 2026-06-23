<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TracerResponse extends Model
{
    protected $table = 'tracer_responses';

    protected $fillable = [
        'alumni_id',
        'form_id',
        'submitted_at',
    ];

    protected $casts = [
        'submitted_at' => 'datetime',
    ];

    public function form()
    {
        return $this->belongsTo(TracerForm::class, 'form_id');
    }

    public function answers()
    {
        return $this->hasMany(TracerAnswer::class, 'tracer_response_id');
    }
}