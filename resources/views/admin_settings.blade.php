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
                    <div class="card">
                        <h3>Change Password</h3>
                        <form id="change-password-form" class="settings-form-grid">
                            <div class="form-group full-width">
                                <label>Current Password</label>
                                <input type="password" name="current_password">
                            </div>
                            <div class="form-group">
                                <label>New Password</label>
                                <input type="password" name="password">
                            </div>
                            <div class="form-group">
                                <label>Confirm New Password</label>
                                <input type="password" name="password_confirmation">
                            </div>
                            <div class="form-footer full-width">
                                <button type="button" class="btn-discard" onclick="resetForm('change-password-form')">Reset</button>
                                <button type="button" class="btn-save" onclick="fakeSave('Password updated')">Change Password</button>
                            </div>
                        </form>
                    </div>

                    <div class="card" style="margin-top:16px;">
                        <h3>Two-Factor Authentication</h3>
                        <p>Enhance account security by enabling 2FA.</p>
                        <div class="form-group">
                            <label class="toggle-label">Enable 2FA</label>
                            <label class="switch">
                                <input type="checkbox" id="toggle-2fa">
                                <span class="slider round"></span>
                            </label>
                        </div>
                        <div id="2fa-settings" style="display:none; margin-top:12px;">
                            <div class="form-group full-width">
                                <label>Authentication Method</label>
                                <select>
                                    <option>Authenticator App</option>
                                    <option>SMS</option>
                                </select>
                            </div>
                            <div class="form-footer full-width">
                                <button class="btn-discard" onclick="fakeSave('2FA disabled')">Disable</button>
                                <button class="btn-save" onclick="fakeSave('2FA enabled')">Enable</button>
                            </div>
                        </div>
                    </div>
                @break

                @case('roles')
                    <h1 class="page-title">Admin Roles</h1>
                    <p>Manage admin accounts and their roles.</p>
                    <div class="card">
                        <table class="table-simple">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Role</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>Cristine Maranan</td>
                                    <td>marananc@nu-lipa.edu.ph</td>
                                    <td>Super Admin</td>
                                    <td>
                                        <button class="btn-small">Edit</button>
                                        <button class="btn-small btn-danger">Remove</button>
                                    </td>
                                </tr>
                                <tr>
                                    <td>Juan Dela Cruz</td>
                                    <td>juan@example.com</td>
                                    <td>Editor</td>
                                    <td>
                                        <button class="btn-small">Edit</button>
                                        <button class="btn-small btn-danger">Remove</button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                @break

                @case('add-admin')
                    <h1 class="page-title">Add New Admin</h1>
                    <div class="card">
                        <form id="add-admin-form" class="settings-form-grid">
                            <div class="form-group">
                                <label>First Name</label>
                                <input type="text" name="first_name">
                            </div>
                            <div class="form-group">
                                <label>Last Name</label>
                                <input type="text" name="last_name">
                            </div>
                            <div class="form-group full-width">
                                <label>Email</label>
                                <input type="email" name="email">
                            </div>
                            <div class="form-group">
                                <label>Role</label>
                                <select name="role">
                                    <option>Editor</option>
                                    <option>Moderator</option>
                                    <option>Super Admin</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Send Invitation</label>
                                <input type="checkbox" name="invite" checked>
                            </div>

                            <div class="form-footer full-width">
                                <button type="button" class="btn-discard" onclick="resetForm('add-admin-form')">Clear</button>
                                <button type="button" class="btn-save" onclick="fakeSave('Admin invited')">Add Admin</button>
                            </div>
                        </form>
                    </div>
                @break

                @case('notifications')
                    <h1 class="page-title">Notification Settings</h1>
                    <div class="card">
                        <form id="notifications-form" class="settings-form-grid">
                            <div class="form-group full-width">
                                <label>Email Notifications</label>
                                <input type="checkbox" name="email_notifications" checked>
                            </div>
                            <div class="form-group full-width">
                                <label>SMS Notifications</label>
                                <input type="checkbox" name="sms_notifications">
                            </div>
                            <div class="form-group full-width">
                                <label>System Alerts</label>
                                <input type="checkbox" name="system_alerts" checked>
                            </div>
                            <div class="form-footer full-width">
                                <button class="btn-discard" onclick="resetForm('notifications-form')">Reset</button>
                                <button class="btn-save" onclick="fakeSave('Notification settings saved')">Save</button>
                            </div>
                        </form>
                    </div>
                @break

                @case('download')
                    <h1 class="page-title">Download Data</h1>
                    <div class="card">
                        <p>Select which data to export.</p>
                        <form class="settings-form-grid">
                            <div class="form-group full-width">
                                <label>Export</label>
                                <select>
                                    <option>All Users (CSV)</option>
                                    <option>Announcements</option>
                                    <option>Events</option>
                                </select>
                            </div>
                            <div class="form-footer full-width">
                                <button class="btn-save" onclick="fakeSave('Preparing download...')">Download</button>
                            </div>
                        </form>
                    </div>
                @break

                @default
                    <h1 class="page-title">Account Information</h1>
                    <div class="profile-pic-section">
                        <img src="/assets/avatar-placeholder.png" alt="Profile">
                        <button class="upload-btn">Upload New Photo</button>
                        <button class="remove-btn">Remove Photo</button>
                    </div>

                    <form id="account-form" class="settings-form-grid">
                        <div class="form-group">
                            <label>Last Name</label>
                            <input type="text" name="last_name" value="Maranan">
                        </div>
                        <div class="form-group">
                            <label>First Name</label>
                            <input type="text" name="first_name" value="Cristine">
                        </div>
                        <div class="form-group">
                            <label>Middle Name</label>
                            <input type="text" name="middle_name" value="Reyes">
                        </div>
                        <div class="form-group">
                            <label>Mobile Number</label>
                            <input type="text" name="mobile" value="0912 259 5288">
                        </div>
                        <div class="form-group full-width">
                            <label>Email</label>
                            <input type="email" name="email" value="marananc@nu-lipa.edu.ph">
                        </div>
                    </form>

                    <div class="form-footer">
                        <button class="btn-discard" onclick="resetForm('account-form')">Discard Changes</button>
                        <button class="btn-save" onclick="fakeSave('Profile saved')">Save Profile Information</button>
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
</div>

    <script>
        // Toggle 2FA settings visibility
        document.addEventListener('DOMContentLoaded', function(){
            var t2 = document.getElementById('toggle-2fa');
            if(t2){
                t2.addEventListener('change', function(e){
                    var settings = document.getElementById('2fa-settings');
                    if(this.checked) settings.style.display = 'block'; else settings.style.display = 'none';
                });
                // initialize
                if(t2.checked){
                    var s = document.getElementById('2fa-settings'); if(s) s.style.display = 'block';
                }
            }
        });

        function resetForm(id){
            var f = document.getElementById(id);
            if(f) f.reset();
        }

        function fakeSave(msg){
            alert(msg);
        }
    </script>
</body>
</html>