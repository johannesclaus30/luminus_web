<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings</title>

    <link rel="stylesheet" href="/css/admin.css">
    <link rel="stylesheet" href="/css/settings.css">
    <link rel="icon" type="image/png" href="/assets/logos/LumiNUs_Icon.png">
    
    <style>
        /* Ensuring the right sidebar matches the layout logic */
        .settings-right-sidebar { 
            flex: 0 0 280px; /* Lock width to 280px, same as Admin Menu */
            margin: 15px 15px 15px 0;
            background-color: #ffffff;
            border-radius: 18px;
            padding: 15px;
            display: flex;
            flex-direction: column;
        }
    </style>
</head>
<body>
    
    <nav class="nav-main">
        <img class="nav-logo" src="/assets/logos/LumiNUs_Logo_Landscape_White.png" alt="LumiNUs Logo">
    </nav>

    <div class="layout-wrapper">
        <div class="admin-menu">
            <div>
                <p class="text-titles">Admin Menu</p>
                <a href="dashboard" class="admin-menu-buttons">Admin Dashboard</a>
                <a href="directory" class="admin-menu-buttons">Alumni Directory</a>
                <a href="announcements" class="admin-menu-buttons">Announcement Editor</a>
                <a href="events" class="admin-menu-buttons">Event Organizer</a>
                <a href="perks" class="admin-menu-buttons">Perks and Discounts</a>
                <a href="alumni_tracer" class="admin-menu-buttons">NU Alumni Tracer</a>
                <a href="messages" class="admin-menu-buttons">Messages</a>
                <a href="settings" class="admin-menu-current">Settings</a>
                <a href="testing" class="admin-menu-buttons">Users Testing</a>
            </div>
            <a href="login" class="admin-menu-signout">Sign Out</a>
        </div>

        <div class="div-dashboard-container admin-scrollable">
            @php
                $section = request()->query('section', 'account');
            @endphp

            @switch($section)
                @case('security')
                    <h1 class="page-title">Security</h1>
                    <p>Password and 2FA settings go here.</p>
                @break

                @case('roles')
                    <h1 class="page-title">Admin Roles</h1>
                    <p>This is the admin roles</p>
                @break

                @case('add-admin')
                    <h1 class="page-title">Add New Admin</h1>
                    <p>This is the new admin page</p>
                @break

                @default
                    <h1 class="page-title">Account Information</h1>
                    <div class="profile-pic-section">
                        <img src="/assets/avatar-placeholder.png" alt="Profile">
                        <button class="upload-btn">Upload New Photo</button>
                        <button class="remove-btn">Remove Photo</button>
                    </div>

                    <form class="settings-form-grid">
                        <div class="form-group">
                            <label>Last Name</label>
                            <input type="text" value="Maranan">
                        </div>
                        <div class="form-group">
                            <label>First Name</label>
                            <input type="text" value="Cristine">
                        </div>
                        <div class="form-group">
                            <label>Middle Name</label>
                            <input type="text" value="Reyes">
                        </div>
                        <div class="form-group">
                            <label>Mobile Number</label>
                            <input type="text" value="0912 259 5288">
                        </div>
                        <div class="form-group full-width">
                            <label>Email</label>
                            <input type="email" value="marananc@nu-lipa.edu.ph">
                        </div>
                    </form>

                    <div class="form-footer">
                        <button class="btn-discard">Discard Changes</button>
                        <button class="btn-save">Save Profile Information</button>
                    </div>
            @endswitch
        </div>

        <div class="settings-right-sidebar">
            <p class="text-titles">Settings</p>
            
            <span class="sidebar-group-label">Admin Account</span>
            <a href="?section=account" class="settings-nav-item {{ $section == 'account' ? 'active' : '' }}">
                Account Information
            </a>
            <a href="?section=security" class="settings-nav-item {{ $section == 'security' ? 'active' : '' }}">
                Security
            </a>

            <span class="sidebar-group-label">Admin Control</span>
            <a href="?section=roles" class="settings-nav-item {{ $section == 'roles' ? 'active' : '' }}">
                Admin Roles
            </a>
            <a href="?section=add-admin" class="settings-nav-item {{ $section == 'add-admin' ? 'active' : '' }}">
                Add New Admin
            </a>

            <span class="sidebar-group-label">System Settings</span>
            <a href="?section=notifications" class="settings-nav-item {{ $section == 'notifications' ? 'active' : '' }}">
                Notification Settings
            </a>
            <a href="?section=download" class="settings-nav-item {{ $section == 'download' ? 'active' : '' }}">
                Download Data
            </a>
        </div>
    </div>
</body>
</html>