<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TracerForm extends Model
{
    protected $table = 'tracer_forms';

    protected $fillable = [
        'admin_id',
        'form_title',
        'form_description',
        'form_header',
        'status',
    ];

    protected $casts = [
        'status' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Status constants
    public const STATUS_DELETED = 0;
    public const STATUS_ACTIVE = 1;
    public const STATUS_DRAFT = 2;
    public const STATUS_CLOSED = 3;

    public function admin(): BelongsTo
    {
        return $this->belongsTo(Admin::class);
    }

    public function questions(): HasMany
    {
        return $this->hasMany(TracerQuestion::class, 'form_id');
    }

    public function responses(): HasMany
    {
        return $this->hasMany(TracerResponse::class, 'form_id');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    public function scopeNotDeleted($query)
    {
        return $query->where('status', '!=', self::STATUS_DELETED);
    }

    public function scopeDeleted($query)
    {
        return $query->where('status', self::STATUS_DELETED);
    }

    // Helper methods
    public function isDeleted(): bool
    {
        return $this->status === self::STATUS_DELETED;
    }

    public function isActiveStatus(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    public function markAsDeleted()
    {
        $this->status = self::STATUS_DELETED;
        $this->save();
    }

    public function markAsActive()
    {
        $this->status = self::STATUS_ACTIVE;
        $this->save();
    }

    public function markAsClosed()
    {
        $this->status = self::STATUS_CLOSED;
        $this->save();
    }

    public function restoreFromDeleted()
    {
        $this->status = self::STATUS_ACTIVE;
        $this->save();
    }
}