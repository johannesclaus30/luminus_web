<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings | LumiNUs Admin</title>

    <!-- Fonts & Icons -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Stylesheets -->
    <link rel="stylesheet" href="/css/admin.css">
    <link rel="stylesheet" href="/css/settings_modern.css">
    <link rel="icon" type="image/png" href="/assets/logos/LumiNUs_Icon.png">
</head>
<body>
    
    @include('partials.admin-navbar')

    <!-- Mobile Menu Overlay -->
    <div class="mobile-overlay" id="mobileOverlay" onclick="toggleMobileMenu()"></div>

    <div class="admin-layout">
        <!-- Sidebar Navigation -->
        <aside class="admin-sidebar" id="adminSidebar">
            <div class="sidebar-header">
                <div class="logo-container">
                    <img src="/assets/logos/LumiNUs_Logo_Landscape_Blue.png" alt="LumiNUs Logo" class="logo-luminus">
                </div>
                <button class="sidebar-close" id="sidebarClose" onclick="toggleMobileMenu()">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>
            
            <nav class="sidebar-nav">
                <p class="nav-section-title">Admin Menu</p>
                <a href="/admin/dashboard" class="nav-item">
                    <i class="fa-solid fa-chart-line"></i>
                    <span>Dashboard</span>
                </a>
                <a href="/admin/directory" class="nav-item">
                    <i class="fa-solid fa-users"></i>
                    <span>Alumni Directory</span>
                </a>
                <a href="{{ route('announcements.index') }}" class="nav-item">
                    <i class="fa-solid fa-bullhorn"></i>
                    <span>Announcements</span>
                </a>
                <a href="{{ route('events.index') }}" class="nav-item">
                    <i class="fa-solid fa-calendar-check"></i>
                    <span>Events</span>
                </a>
                <a href="{{ route('perks.index') }}" class="nav-item">
                    <i class="fa-solid fa-gift"></i>
                    <span>Perks & Discounts</span>
                </a>
                <a href="/admin/alumni_tracer" class="nav-item">
                    <i class="fa-solid fa-location-dot"></i>
                    <span>Alumni Tracer</span>
                </a>
                <a href="/admin/messages" class="nav-item">
                    <i class="fa-solid fa-envelope"></i>
                    <span>Messages</span>
                </a>
                <a href="{{ route('admin.settings') }}" class="nav-item active">
                    <i class="fa-solid fa-gear"></i>
                    <span>Settings</span>
                </a>
            </nav>
            
            <div class="sidebar-footer">
                <a href="{{ route('admin.logout') }}" class="nav-item logout-btn">
                    <i class="fa-solid fa-right-from-bracket"></i>
                    <span>Sign Out</span>
                </a>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="admin-main">

                    <!-- Mobile Menu Toggle -->
                    <button class="mobile-menu-toggle" id="mobileMenuToggle" onclick="toggleMobileMenu()">
                        <i class="fa-solid fa-bars"></i>
                    </button>

                    @php
                        $section = request()->query('section', 'account');

                        $sectionMeta = [
                            'account' => [
                                'eyebrow' => 'Admin account',
                                'title' => 'Account Information',
                                'description' => 'Update the profile details shown across the admin workspace.',
                                'status' => 'Profile ready',
                            ],
                            'security' => [
                                'eyebrow' => 'Admin account',
                                'title' => 'Security',
                                'description' => 'Protect the dashboard with stronger credentials and multi-factor access.',
                                'status' => 'Security controls',
                            ],
                            'roles' => [
                                'eyebrow' => 'Admin control',
                                'title' => 'Admin Roles',
                                'description' => 'Review the people who can manage content, users, and system settings.',
                                'status' => '2 active roles',
                            ],
                            'add-admin' => [
                                'eyebrow' => 'Admin control',
                                'title' => 'Add New Admin',
                                'description' => 'Invite a new administrator and assign the appropriate access level.',
                                'status' => 'Invitation flow',
                            ],
                            'notifications' => [
                                'eyebrow' => 'System settings',
                                'title' => 'Notification Settings',
                                'description' => 'Choose how the team receives updates about platform activity.',
                                'status' => 'Delivery preferences',
                            ],
                            'download' => [
                                'eyebrow' => 'System settings',
                                'title' => 'Download Data',
                                'description' => 'Export records for reporting, archiving, or offline review.',
                                'status' => 'Export tools',
                            ],
                        ];

                        $activeMeta = $sectionMeta[$section] ?? $sectionMeta['account'];
                    @endphp

        <header class="page-header">
                <div class="header-content">
                    <div class="header-title-section">
                        <h1 class="page-title">
                            <i class="fa-solid fa-gear"></i>
                            {{ $activeMeta['title'] }}
                        </h1>
                        <p class="page-subtitle">{{ $activeMeta['description'] }}</p>
                    </div>
                </div>
            </header>

            @if (session('status'))
                <div class="settings-alert settings-alert-success">
                    <i class="fa-solid fa-circle-check"></i>
                    <div>
                        <strong>{{ session('status') }}</strong>
                        @if (session('temporary_password'))
                            <span>Temporary password: <code>{{ session('temporary_password') }}</code></span>
                        @endif
                    </div>
                </div>
            @endif

            @if ($errors->any())
                <div class="settings-alert settings-alert-error">
                    <i class="fa-solid fa-circle-exclamation"></i>
                    <div>
                        <strong>Please review the form.</strong>
                        <span>{{ $errors->first() }}</span>
                    </div>
                </div>
            @endif

            <div class="settings-layout">
                <!-- Settings Sub-Navigation -->
                <aside class="settings-sidebar">
                    <div class="settings-nav-group">
                        <p class="nav-section-title">Admin Account</p>
                        <a href="?section=account" class="settings-nav-item {{ $section == 'account' ? 'active' : '' }}">
                            <i class="fa-solid fa-user"></i> Account Information
                        </a>
                        <a href="?section=security" class="settings-nav-item {{ $section == 'security' ? 'active' : '' }}">
                            <i class="fa-solid fa-shield-halved"></i> Security
                        </a>
                    </div>
                    <div class="settings-nav-group">
                        <p class="nav-section-title">Admin Control</p>
                        <a href="?section=roles" class="settings-nav-item {{ $section == 'roles' ? 'active' : '' }}">
                            <i class="fa-solid fa-user-tag"></i> Admin Roles
                        </a>
                        <a href="?section=add-admin" class="settings-nav-item {{ $section == 'add-admin' ? 'active' : '' }}">
                            <i class="fa-solid fa-user-plus"></i> Add New Admin
                        </a>
                    </div>
                    <div class="settings-nav-group">
                        <p class="nav-section-title">System Settings</p>
                        <a href="?section=notifications" class="settings-nav-item {{ $section == 'notifications' ? 'active' : '' }}">
                            <i class="fa-solid fa-bell"></i> Notifications
                        </a>
                        <a href="?section=download" class="settings-nav-item {{ $section == 'download' ? 'active' : '' }}">
                            <i class="fa-solid fa-download"></i> Download Data
                        </a>
                    </div>
                </aside>

                <!-- Settings Content -->
                <div class="settings-content">
                    @switch($section)
                        @case('security')
                            <div class="form-card">
                                <div class="form-card-header">
                                    <div>
                                        <h3>Change Password</h3>
                                        <p>Keep the admin account protected with a strong, unique password.</p>
                                    </div>
                                </div>
                                <form id="change-password-form" class="settings-form-grid" method="POST" action="{{ route('admin.password.update') }}">
                                    @csrf
                                    @method('PUT')
                                    
                                    <div class="form-group full-width">
                                        <label class="form-label">Current Password</label>
                                        <div class="password-input-wrapper">
                                            <input type="password" name="current_password" class="form-control" placeholder="Enter current password" required>
                                            <button type="button" class="password-toggle" onclick="togglePassword(this)" tabindex="-1">
                                                <i class="fa-solid fa-eye"></i>
                                            </button>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="form-label">New Password</label>
                                        <div class="password-input-wrapper">
                                            <input type="password" name="password" class="form-control" placeholder="Enter new password" required minlength="8">
                                            <button type="button" class="password-toggle" onclick="togglePassword(this)" tabindex="-1">
                                                <i class="fa-solid fa-eye"></i>
                                            </button>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="form-label">Confirm New Password</label>
                                        <div class="password-input-wrapper">
                                            <input type="password" name="password_confirmation" class="form-control" placeholder="Repeat new password" required minlength="8">
                                            <button type="button" class="password-toggle" onclick="togglePassword(this)" tabindex="-1">
                                                <i class="fa-solid fa-eye"></i>
                                            </button>
                                        </div>
                                    </div>
                                    
                                    <div class="forgot-password-row full-width">
                                        <a href="{{ route('admin.forgot-password') }}" class="forgot-password-link">
                                            <i class="fa-solid fa-key"></i> Forgot your password?
                                        </a>
                                    </div>
                                    
                                    <div class="form-actions full-width">
                                        <button type="button" class="btn btn-secondary" onclick="resetForm('change-password-form')">Reset</button>
                                        <button type="submit" class="btn btn-primary">Change Password</button>
                                    </div>
                                </form>
                            </div>

                            <div class="form-card">
                                <div class="form-card-header">
                                    <div>
                                        <h3>Two-Factor Authentication</h3>
                                        <p>Add a second layer of login verification for sensitive admin work.</p>
                                    </div>
                                    <span class="status-badge status-badge-warning">Recommended</span>
                                </div>
                                <div class="settings-toggle-row">
                                    <div>
                                        <strong>Enable 2FA</strong>
                                        <p>Require a verification code when signing in.</p>
                                    </div>
                                    <label class="switch">
                                        <input type="checkbox" id="toggle-2fa">
                                        <span class="slider round"></span>
                                    </label>
                                </div>
                                <div id="2fa-settings" class="settings-reveal-panel" style="display:none;">
                                    <div class="form-group full-width">
                                        <label class="form-label">Authentication Method</label>
                                        <select class="form-control">
                                            <option>Authenticator App</option>
                                            <option>SMS</option>
                                        </select>
                                    </div>
                                    <div class="form-actions full-width">
                                        <button type="button" class="btn btn-secondary" onclick="fakeSave('2FA disabled')">Disable</button>
                                        <button type="button" class="btn btn-primary" onclick="fakeSave('2FA enabled')">Enable</button>
                                    </div>
                                </div>
                            </div>
                        @break

                        @case('roles')
                            <div class="form-card">
                                <div class="form-card-header">
                                    <div>
                                        <h3>Admin Accounts</h3>
                                        <p>Track who has access to the admin tools and what they can change.</p>
                                    </div>
                                    <span class="status-badge status-badge-info">Live data</span>
                                </div>
                                <div class="table-wrap">
                                    <table class="settings-table">
                                        <thead>
                                            <tr>
                                                <th>Name</th>
                                                <th>Email</th>
                                                <th>Role</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse ($admins as $admin)
                                                @php
                                                    $firstName = $admin->admin_first_name ?? $admin->AdminFirstName ?? '';
                                                    $middleName = $admin->admin_middle_name ?? $admin->AdminMiddleName ?? '';
                                                    $lastName = $admin->admin_last_name ?? $admin->AdminLastName ?? '';
                                                    $email = $admin->admin_email ?? $admin->AdminEmail ?? '';
                                                    $roleValue = $admin->admin_role ?? $admin->AdminRole ?? '';
                                                    $displayName = trim($firstName . ' ' . ($middleName ? $middleName . ' ' : '') . $lastName);
                                                    
                                                    $roleLabels = [
                                                        'Executive Director' => 'Executive Director',
                                                        'Academic Director' => 'Academic Director',
                                                        'Coordinator' => 'Coordinator',
                                                        'Assistant Coordinator' => 'Assistant Coordinator',
                                                    ];
                                                    $roleLabel = $roleLabels[$roleValue] ?? ($roleValue !== '' ? ucwords(str_replace(['_', '-'], ' ', $roleValue)) : 'Unassigned');
                                                    
                                                    $roleChipClass = match($roleValue) {
                                                        'Executive Director' => 'role-chip-primary',
                                                        'Academic Director' => 'role-chip-secondary',
                                                        'Coordinator' => 'role-chip-success',
                                                        'Assistant Coordinator' => 'role-chip-muted',
                                                        default => '',
                                                    };
                                                @endphp
                                                <tr>
                                                    <td>{{ $displayName ?: 'Unnamed Admin' }}</td>
                                                    <td>{{ $email ?: 'No email provided' }}</td>
                                                    <td>
                                                        <span class="role-chip {{ $roleChipClass }}">{{ $roleLabel }}</span>
                                                    </td>
                                                    <td>
                                                        <button type="button" class="btn-action btn-edit" title="Edit">
                                                            <i class="fa-solid fa-pen-to-square"></i>
                                                        </button>
                                                        <button type="button" class="btn-action" style="background:#fee; color:#ef4444;" title="Remove">
                                                            <i class="fa-solid fa-trash-can"></i>
                                                        </button>
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="4" class="empty-table-cell">No admin accounts have been created yet.</td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        @break

                        @case('add-admin')
                            <div class="form-card">
                                <div class="form-card-header">
                                    <div>
                                        <h3>Invite Admin</h3>
                                        <p>Fill in the contact details and assign an access level before sending the invitation.</p>
                                    </div>
                                </div>
                                <form id="add-admin-form" class="settings-form-grid" method="POST" action="{{ route('admin.settings.store') }}" enctype="multipart/form-data">
                                    @csrf
                                    <div class="form-group">
                                        <label class="form-label">First Name</label>
                                        <input type="text" name="admin_first_name" class="form-control" value="{{ old('admin_first_name') }}" placeholder="First name">
                                    </div>
                                    <div class="form-group">
                                        <label class="form-label">Middle Name</label>
                                        <input type="text" name="admin_middle_name" class="form-control" value="{{ old('admin_middle_name') }}" placeholder="Middle name">
                                    </div>
                                    <div class="form-group">
                                        <label class="form-label">Last Name</label>
                                        <input type="text" name="admin_last_name" class="form-control" value="{{ old('admin_last_name') }}" placeholder="Last name">
                                    </div>
                                    <div class="form-group full-width">
                                        <label class="form-label">Email</label>
                                        <input type="email" name="admin_email" class="form-control" value="{{ old('admin_email') }}" placeholder="name@example.com">
                                    </div>
                                    <div class="form-group">
                                        <label class="form-label">Phone Number</label>
                                        <input type="text" name="phone_number" class="form-control" value="{{ old('phone_number') }}" placeholder="09xx xxx xxxx">
                                    </div>
                                    <div class="form-group">
                                        <label class="form-label">Role</label>
                                        <select name="admin_role" class="form-control">
                                            <option value="">-- Select a role --</option>
                                            <option value="Executive Director" @selected(old('admin_role') === 'Executive Director')>Executive Director</option>
                                            <option value="Academic Director" @selected(old('admin_role') === 'Academic Director')>Academic Director</option>
                                            <option value="Coordinator" @selected(old('admin_role') === 'Coordinator')>Coordinator</option>
                                            <option value="Assistant Coordinator" @selected(old('admin_role') === 'Assistant Coordinator')>Assistant Coordinator</option>
                                        </select>
                                    </div>
                                    <div class="form-group full-width">
                                        <div class="rule-alert">
                                            <i class="fa-solid fa-circle-info"></i>
                                            <div>
                                                <strong>Temporary password</strong>
                                                <p>A secure password will be generated automatically after saving.</p>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="form-group full-width">
                                        <label class="form-label">Photo</label>
                                        <div class="upload-zone">
                                            <i class="fa-solid fa-cloud-arrow-up upload-icon"></i>
                                            <p class="upload-title">Click to upload profile photo</p>
                                            <p class="upload-desc">JPG, PNG, or WEBP format.</p>
                                            <input type="file" name="photo" accept="image/*" class="settings-file-input">
                                        </div>
                                    </div>

                                    <div class="form-actions full-width">
                                        <button type="button" class="btn btn-secondary" onclick="resetForm('add-admin-form')">Clear</button>
                                        <button type="submit" class="btn btn-primary">Add Admin</button>
                                    </div>
                                </form>
                            </div>
                        @break

                        @case('notifications')
                            <div class="form-card">
                                <div class="form-card-header">
                                    <div>
                                        <h3>Notification Channels</h3>
                                        <p>Select which alerts should reach the team in real time.</p>
                                    </div>
                                </div>
                                <form id="notifications-form" class="settings-form-grid">
                                    <div class="form-group full-width settings-option-row">
                                        <div>
                                            <label class="form-label">Email Notifications</label>
                                            <p>Send important updates to the admin inbox.</p>
                                        </div>
                                        <label class="switch">
                                            <input type="checkbox" name="email_notifications" checked>
                                            <span class="slider round"></span>
                                        </label>
                                    </div>
                                    <div class="form-group full-width settings-option-row">
                                        <div>
                                            <label class="form-label">SMS Notifications</label>
                                            <p>Push urgent alerts to registered phone numbers.</p>
                                        </div>
                                        <label class="switch">
                                            <input type="checkbox" name="sms_notifications">
                                            <span class="slider round"></span>
                                        </label>
                                    </div>
                                    <div class="form-group full-width settings-option-row">
                                        <div>
                                            <label class="form-label">System Alerts</label>
                                            <p>Track issues, warnings, and maintenance events.</p>
                                        </div>
                                        <label class="switch">
                                            <input type="checkbox" name="system_alerts" checked>
                                            <span class="slider round"></span>
                                        </label>
                                    </div>
                                    <div class="form-actions full-width">
                                        <button type="button" class="btn btn-secondary" onclick="resetForm('notifications-form')">Reset</button>
                                        <button type="button" class="btn btn-primary" onclick="fakeSave('Notification settings saved')">Save</button>
                                    </div>
                                </form>
                            </div>
                        @break

                        @case('download')
                            <div class="form-card">
                                <div class="form-card-header">
                                    <div>
                                        <h3>Export Options</h3>
                                        <p>Generate a file for reporting, audits, or offline review.</p>
                                    </div>
                                </div>
                                <form class="settings-form-grid">
                                    <div class="form-group full-width">
                                        <label class="form-label">Export</label>
                                        <select class="form-control">
                                            <option>All Users (CSV)</option>
                                            <option>Announcements</option>
                                            <option>Events</option>
                                        </select>
                                    </div>
                                    <div class="rule-alert full-width">
                                        <i class="fa-solid fa-circle-info"></i>
                                        <span>The export will be prepared using the latest available records.</span>
                                    </div>
                                    <div class="form-actions full-width">
                                        <button type="button" class="btn btn-primary" onclick="fakeSave('Preparing download...')">
                                            <i class="fa-solid fa-download"></i> Download
                                        </button>
                                    </div>
                                </form>
                            </div>
                        @break

                        @default
                            @php
                                $firstName = trim($currentAdmin->admin_first_name ?? '');
                                $lastName = trim($currentAdmin->admin_last_name ?? '');
                                $initials = '';

                                if ($firstName !== '') {
                                    $initials .= strtoupper(mb_substr($firstName, 0, 1));
                                }
                                if ($lastName !== '') {
                                    $initials .= strtoupper(mb_substr($lastName, 0, 1));
                                }
                                if ($initials === '') {
                                    $nameParts = preg_split('/\s+/', trim($currentAdmin->admin_name ?? '')) ?: [];
                                    if (count($nameParts) >= 2) {
                                        $initials = strtoupper(mb_substr($nameParts[0], 0, 1) . mb_substr($nameParts[count($nameParts) - 1], 0, 1));
                                    } else {
                                        $initials = strtoupper(mb_substr($currentAdmin->admin_name ?? 'AD', 0, 2));
                                    }
                                }
                            @endphp

                            <div class="form-card settings-profile-card">
                                <div class="profile-pic-section">
                                    <div class="profile-avatar-wrapper {{ $currentAdminPhotoUrl ? 'has-photo' : 'is-initials' }}">
                                        @if ($currentAdminPhotoUrl)
                                            <img src="{{ $currentAdminPhotoUrl }}" alt="Profile photo">
                                        @else
                                            <span class="profile-initials">{{ $initials }}</span>
                                        @endif
                                    </div>
                                    <div class="profile-pic-copy">
                                        <h3>Profile Photo</h3>
                                        <p>Use a clear headshot so the admin profile is recognizable across the system.</p>
                                        <div class="profile-action-row">
                                            <label class="btn btn-primary upload-btn" for="account-photo-input">
                                                <i class="fa-solid fa-upload"></i> Upload New Photo
                                            </label>
                                            <button type="button" id="remove-photo-btn" class="btn btn-secondary remove-btn">Remove Photo</button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="form-card">
                                <div class="form-card-header">
                                    <div>
                                        <h3>Personal Details</h3>
                                        <p>These details appear in the admin profile and internal references.</p>
                                    </div>
                                </div>

                                <form id="account-form" class="settings-form-grid" method="POST" action="{{ route('admin.settings.update') }}" enctype="multipart/form-data">
                                    @csrf
                                    @method('PUT')
                                    <input type="hidden" name="remove_photo" id="remove-photo-flag" value="">

                                    <div class="form-group">
                                        <label class="form-label">Last Name</label>
                                        <input type="text" name="admin_last_name" class="form-control" value="{{ old('admin_last_name', $currentAdmin->admin_last_name ?? '') }}">
                                    </div>
                                    <div class="form-group">
                                        <label class="form-label">First Name</label>
                                        <input type="text" name="admin_first_name" class="form-control" value="{{ old('admin_first_name', $currentAdmin->admin_first_name ?? '') }}">
                                    </div>
                                    <div class="form-group">
                                        <label class="form-label">Middle Name</label>
                                        <input type="text" name="admin_middle_name" class="form-control" value="{{ old('admin_middle_name', $currentAdmin->admin_middle_name ?? '') }}">
                                    </div>
                                    <div class="form-group">
                                        <label class="form-label">Mobile Number</label>
                                        <input type="text" name="phone_number" class="form-control" value="{{ old('phone_number', $currentAdmin->phone_number ?? '') }}">
                                    </div>
                                    <div class="form-group full-width">
                                        <label class="form-label">Email</label>
                                        <input type="email" name="admin_email" class="form-control" value="{{ old('admin_email', $currentAdmin->admin_email ?? '') }}">
                                    </div>
                                    <div class="form-group full-width">
                                        <label class="form-label">Profile Photo</label>
                                        <div class="upload-zone">
                                            <i class="fa-solid fa-cloud-arrow-up upload-icon"></i>
                                            <p class="upload-title">Click to upload new photo</p>
                                            <p class="upload-desc">Uploading a new photo will replace the current admin photo.</p>
                                            <input id="account-photo-input" type="file" name="photo" accept="image/*" class="settings-file-input">
                                        </div>
                                    </div>
                                    <div class="form-actions full-width">
                                        <button type="button" class="btn btn-secondary" onclick="resetForm('account-form')">Discard Changes</button>
                                        <button type="submit" class="btn btn-primary">Save Profile Information</button>
                                    </div>
                                </form>
                            </div>
                    @endswitch
                </div>
            </div>
        </main>
    </div>

    <script>
        // Mobile menu toggle
        function toggleMobileMenu() {
            const sidebar = document.getElementById('adminSidebar');
            const overlay = document.getElementById('mobileOverlay');
            sidebar.classList.toggle('mobile-open');
            overlay.classList.toggle('active');
            document.body.style.overflow = sidebar.classList.contains('mobile-open') ? 'hidden' : '';
        }

        // Close sidebar when clicking on a nav item (mobile)
        document.querySelectorAll('.nav-item').forEach(item => {
            item.addEventListener('click', function() {
                if (window.innerWidth <= 1024) {
                    toggleMobileMenu();
                }
            });
        });

        // Handle window resize
        let resizeTimer;
        window.addEventListener('resize', function() {
            clearTimeout(resizeTimer);
            resizeTimer = setTimeout(function() {
                if (window.innerWidth > 1024) {
                    document.getElementById('adminSidebar').classList.remove('mobile-open');
                    document.getElementById('mobileOverlay').classList.remove('active');
                    document.body.style.overflow = '';
                }
            }, 250);
        });

        // Toggle 2FA settings visibility
        document.addEventListener('DOMContentLoaded', function(){
            var t2 = document.getElementById('toggle-2fa');
            if(t2){
                t2.addEventListener('change', function(e){
                    var settings = document.getElementById('2fa-settings');
                    if(this.checked) settings.style.display = 'block'; else settings.style.display = 'none';
                });
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

        // Remove photo logic
        document.addEventListener('DOMContentLoaded', function() {
            const removeBtn = document.getElementById('remove-photo-btn');
            const form = document.getElementById('account-form');
            const removeFlag = document.getElementById('remove-photo-flag');

            if (removeBtn && form && removeFlag) {
                removeBtn.addEventListener('click', function() {
                    if (confirm('Are you sure you want to remove your profile photo?')) {
                        removeFlag.value = '1';
                        form.submit();
                    }
                });
            }
        });
    </script>
</body>
</html>