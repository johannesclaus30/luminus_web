<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Alumni extends Model
{
    use HasFactory;

    protected $table = 'alumnis';

    protected $fillable = [
        'first_name',
        'middle_name',
        'last_name',
        'date_of_birth',
        'sex',
        'year_graduated',
        'alumni_photo',
        'alumni_bio',
        'student_id_number',
        'email',
        'phone_number',
        'password_hash',
        'verification_status',
        'program',
        'card_photo',
    ];

    protected $hidden = [
        'password_hash',
    ];

    protected $casts = [
        'date_of_birth' => 'date',
        'year_graduated' => 'date',
        'is_online' => 'boolean',
    ];

    // Add these accessors
    public function getFullNameAttribute()
    {
        return "{$this->last_name}, {$this->first_name}" . ($this->middle_name ? " {$this->middle_name}" : '');
    }

    public function getInitialsAttribute()
    {
        return strtoupper(substr($this->first_name, 0, 1) . substr($this->last_name, 0, 1));
    }

    // Add these relationships
    public function messagesSent()
    {
        return $this->hasMany(Message::class, 'sender_id')->where('sender_type', 'alumni');
    }

    public function messagesReceived()
    {
        return $this->hasMany(Message::class, 'receiver_id')->where('receiver_type', 'alumni');
    }

    // ADD THIS: Relationship to event registrations
    public function eventRegistrations()
    {
        return $this->hasMany(EventRegistration::class, 'alumni_id', 'id');
    }

    // ADD THIS: Accessor for alumni photo URL
    public function getAlumniPhotoUrlAttribute()
    {
        if ($this->alumni_photo) {
            // If it's already a full URL, return as is
            if (filter_var($this->alumni_photo, FILTER_VALIDATE_URL)) {
                return $this->alumni_photo;
            }
            // Otherwise, assume it's stored in storage
            return asset('storage/' . $this->alumni_photo);
        }
        return null;
    }
}