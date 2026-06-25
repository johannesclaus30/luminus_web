<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Admin extends Model
{
    protected $table = 'admins';
    
    // Add this - Supabase needs timestamps disabled if using their auto-generated ones
    public $timestamps = true; // Keep if you want Laravel to manage timestamps
    
    protected $fillable = [
        'admin_first_name',
        'admin_middle_name',
        'admin_last_name',
        'admin_email',
        'admin_password_hash',
        'admin_role',
        'phone_number',
        'photo',
    ];

    protected $hidden = [
        'admin_password_hash',
    ];

    public function getFullNameAttribute()
    {
        return "{$this->admin_last_name}, {$this->admin_first_name}";
    }

    public function getInitialsAttribute()
    {
        return strtoupper(substr($this->admin_first_name, 0, 1) . substr($this->admin_last_name, 0, 1));
    }

    // ADD THESE TWO ACCESSORS:
    public function getNameAttribute()
    {
        return trim($this->admin_first_name . ' ' . $this->admin_last_name);
    }

    public function getEmailAttribute()
    {
        return $this->admin_email;
    }

    public function messagesSent()
    {
        return $this->hasMany(Message::class, 'sender_id')->where('sender_type', 'admin');
    }

    public function messagesReceived()
    {
        return $this->hasMany(Message::class, 'receiver_id')->where('receiver_type', 'admin');
    }
}