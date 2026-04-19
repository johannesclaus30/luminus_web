<?php
 
namespace App\Models;
 
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
 
class Admin extends Authenticatable
{
    use HasFactory, Notifiable;
 
    protected $table = 'admins';
    protected $primaryKey = 'id';
 
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
        'remember_token',
    ];
 
    public function getAuthPassword()
    {
        return $this->admin_password_hash;
    }
}