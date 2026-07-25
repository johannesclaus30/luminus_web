<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\AdminPermission;

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
        'reset_token',             
        'reset_token_expires_at',   
        'account_status',    
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

    public function permissions()
    {
        return $this->hasMany(AdminPermission::class, 'admin_id');
    }

    public function canViewModule(string $module): bool
    {
        // Coordinator ALWAYS has full access (cannot be restricted)
        if ($this->admin_role === 'Coordinator') {
            return true;
        }
        
        // Check if account is restricted
        if (($this->account_status ?? 1) == 0) {
            return false;
        }
        
        // Check custom permissions first, if not set then use role defaults
        $permission = $this->permissions()->where('module', $module)->first();
        
        if ($permission) {
            return $permission->can_view;
        }
        
        // Fall back to role-based defaults
        $defaults = AdminPermission::getDefaultPermissionsForRole($this->admin_role);
        return $defaults[$module] ?? false;
    }

    public function getAccessibleModules(): array
    {
        // Coordinator always gets everything
        if ($this->admin_role === 'Coordinator') {
            return AdminPermission::getAvailableModules();
        }
        
        $allModules = AdminPermission::getAvailableModules();
        $accessibleModules = [];
        
        foreach ($allModules as $key => $module) {
            if ($this->canViewModule($key)) {
                $accessibleModules[$key] = $module;
            }
        }
        
        return $accessibleModules;
    }

    public function syncPermissions(array $modulePermissions): void
    {
        foreach ($modulePermissions as $module => $canView) {
            AdminPermission::updateOrCreate(
                [
                    'admin_id' => $this->id,
                    'module' => $module,
                ],
                [
                    'can_view' => $canView,
                    'can_manage' => $canView,
                ]
            );
        }
    }

     public function setupDefaultPermissions(): void
    {
        // Coordinator doesn't need permissions (always has full access)
        if ($this->admin_role === 'Coordinator') {
            return;
        }
        
        $defaults = AdminPermission::getDefaultPermissionsForRole($this->admin_role);
        
        foreach ($defaults as $module => $canView) {
            AdminPermission::create([
                'admin_id' => $this->id,
                'module' => $module,
                'can_view' => $canView,
                'can_manage' => $canView,
            ]);
        }
    }

}