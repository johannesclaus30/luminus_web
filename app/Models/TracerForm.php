<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TracerForm extends Model
{
    use SoftDeletes;

    protected $table = 'tracer_forms';

    protected $fillable = [
        'admin_id',
        'form_title',
        'form_description',
        'form_header',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function questions()
    {
        return $this->hasMany(TracerQuestion::class, 'form_id')->orderBy('order_priority');
    }

    public function responses()
    {
        return $this->hasMany(TracerResponse::class, 'form_id');
    }
}
