<?php
 
namespace App\Models;
 
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
 
class Admin extends Authenticatable
{
    use HasFactory, Notifiable;
 
    // 1. Explicitly tell Laravel to look at the 'admins' table in Supabase
    protected $table = 'admins';
 
    // 2. The EXACT columns allowed to be inserted or updated from the web dashboard
    protected $fillable = [
        'admin_first_name',
        'admin_middle_name',
        'admin_last_name',
        'admin_email',
        'admin_password_hash',
        'admin_role',
        'status',
    ];
 
    // 3. Columns that should NEVER be returned when querying the database (Security)
    protected $hidden = [
        'password_hash', // Hides the password from API responses
        'remember_token',
    ];
 
    // 4. (Optional) If your password column in Supabase is NOT named 'password', 
    // Laravel needs this function so the built-in Auth::attempt() login system works.
    public function getAuthPassword()
    {
        return $this->password_hash; 
    }
}