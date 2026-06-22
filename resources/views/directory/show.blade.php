<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Alumni Profile | LumiNUs Admin</title>

    <!-- Fonts & Icons -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    
    <!-- Stylesheets -->
    <link rel="stylesheet" href="/css/admin.css">
    <link rel="stylesheet" href="/css/directory_modern.css">
    <link rel="icon" type="image/png" href="/assets/logos/LumiNUs_Icon.png">

    <style>
        /* Profile specific styles */
        .profile-container { max-width: 900px; margin: 0 auto; padding: 20px; }
        .profile-card { background: #fff; border-radius: 12px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); padding: 30px; margin-bottom: 20px; }
        .profile-header { display: flex; align-items: center; gap: 20px; margin-bottom: 30px; border-bottom: 1px solid #eee; padding-bottom: 20px; }
        .profile-photo { width: 120px; height: 120px; border-radius: 50%; object-fit: cover; border: 4px solid #f0f0f0; }
        .profile-info h2 { margin: 0; font-size: 1.8rem; color: #1e293b; }
        .profile-info p { margin: 5px 0 0; color: #64748b; font-size: 1.1rem; }
        .profile-details { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; }
        .detail-item { background: #f8fafc; padding: 15px; border-radius: 8px; }
        .detail-item label { display: block; font-size: 0.85rem; color: #64748b; margin-bottom: 5px; font-weight: 500; }
        .detail-item span { font-size: 1rem; color: #1e293b; font-weight: 500; }
        
        /* Mailer Test Section */
        .mailer-test-section { background: #eff6ff; border: 1px dashed #3b82f6; border-radius: 12px; padding: 25px; text-align: center; margin-top: 20px; }
        .mailer-test-section h3 { color: #1e40af; margin-top: 0; }
        .btn-send-email { background: #2563eb; color: white; padding: 12px 24px; border: none; border-radius: 8px; font-size: 1rem; font-weight: 600; cursor: pointer; transition: background 0.2s; display: inline-flex; align-items: center; gap: 8px; }
        .btn-send-email:hover { background: #1d4ed8; }
        .btn-send-email:disabled { background: #93c5fd; cursor: not-allowed; }
        
        .btn-back { background: #e2e8f0; color: #475569; padding: 10px 20px; border: none; border-radius: 8px; font-weight: 500; text-decoration: none; display: inline-flex; align-items: center; gap: 8px; margin-bottom: 20px; }
        .btn-back:hover { background: #cbd5e1; }
    </style>
</head>
<body>
    @include('partials.admin-navbar')

    <!-- Mobile Menu Overlay -->
    <div class="mobile-overlay" id="mobileOverlay" onclick="toggleMobileMenu()"></div>

    <div class="admin-layout">
        <!-- Sidebar Navigation (Same as your directory page) -->
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
                <a href="/admin/dashboard" class="nav-item"><i class="fa-solid fa-chart-line"></i><span>Dashboard</span></a>
                <a href="{{ route('admin.directory') }}" class="nav-item active"><i class="fa-solid fa-users"></i><span>Alumni Directory</span></a>
                <a href="{{ route('announcements.index') }}" class="nav-item"><i class="fa-solid fa-bullhorn"></i><span>Announcements</span></a>
                <a href="{{ route('events.index') }}" class="nav-item"><i class="fa-solid fa-calendar-check"></i><span>Events</span></a>
                <a href="{{ route('perks.index') }}" class="nav-item"><i class="fa-solid fa-gift"></i><span>Perks & Discounts</span></a>
                <a href="/admin/alumni_tracer" class="nav-item"><i class="fa-solid fa-location-dot"></i><span>Alumni Tracer</span></a>
                <a href="/admin/messages" class="nav-item"><i class="fa-solid fa-envelope"></i><span>Messages</span></a>
                <a href="{{ route('admin.settings') }}" class="nav-item"><i class="fa-solid fa-gear"></i><span>Settings</span></a>
            </nav>
            
            <div class="sidebar-footer">
                <a href="{{ route('admin.logout') }}" class="nav-item logout-btn"><i class="fa-solid fa-right-from-bracket"></i><span>Sign Out</span></a>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="admin-main">
            <button class="mobile-menu-toggle" id="mobileMenuToggle" onclick="toggleMobileMenu()">
                <i class="fa-solid fa-bars"></i>
            </button>

            <header class="page-header">
                <div class="header-content">
                    <div class="header-title-section">
                        <h1 class="page-title"><i class="fa-solid fa-user"></i> Alumni Profile</h1>
                        <p class="page-subtitle">View details and test mailer for {{ $alumnus->first_name }} {{ $alumnus->last_name }}</p>
                    </div>
                    <div class="header-actions">
                        <a href="{{ route('admin.alumni.edit', $alumnus->id) }}" class="btn btn-primary">
                            <i class="fa-solid fa-pen-to-square"></i> <span>Edit Profile</span>
                        </a>
                    </div>
                </div>
            </header>

            <div class="profile-container">
                <a href="{{ route('admin.directory') }}" class="btn-back">
                    <i class="fa-solid fa-arrow-left"></i> Back to Directory
                </a>

                <div class="profile-card">
                    <div class="profile-header">
                        @php
                            // Reusing your exact photo logic
                            $photoPath = trim((string) ($alumnus->alumni_photo ?: $alumnus->card_photo));
                            if ($photoPath === '') { $photoUrl = '/assets/FINAL-NULIPA.jpg'; } 
                            elseif (preg_match('/^https?:\/\//i', $photoPath)) { $photoUrl = $photoPath; } 
                            elseif (str_starts_with($photoPath, '/storage/')) { $photoUrl = $photoPath; } 
                            elseif (str_starts_with($photoPath, 'storage/')) { $photoUrl = '/' . $photoPath; } 
                            elseif (str_starts_with($photoPath, '/')) { $photoUrl = $photoPath; } 
                            elseif (trim((string) config('filesystems.disks.s3.url')) !== '') { $photoUrl = rtrim((string) config('filesystems.disks.s3.url'), '/') . '/' . ltrim($photoPath, '/'); } 
                            else { $photoUrl = asset('storage/' . ltrim($photoPath, '/')); }
                        @endphp
                        <img src="{{ $photoUrl }}" alt="{{ $alumnus->first_name }}" class="profile-photo" onerror="this.src='/assets/FINAL-NULIPA.jpg'">
                        <div class="profile-info">
                            <h2>{{ $alumnus->first_name }} {{ $alumnus->middle_name }} {{ $alumnus->last_name }}</h2>
                            <p><i class="fa-solid fa-graduation-cap"></i> {{ $alumnus->program ?? 'N/A' }} &bull; Class of {{ optional($alumnus->year_graduated)->format('Y') ?? 'N/A' }}</p>
                        </div>
                    </div>

                    <div class="profile-details">
                        <div class="detail-item"><label>Student ID</label><span>{{ $alumnus->student_id_number ?? 'N/A' }}</span></div>
                        <div class="detail-item"><label>Email Address</label><span>{{ $alumnus->email ?? 'N/A' }}</span></div>
                        <div class="detail-item"><label>Phone Number</label><span>{{ $alumnus->phone_number ?? 'N/A' }}</span></div>
                        <div class="detail-item"><label>Sex</label><span>{{ $alumnus->sex ?? 'N/A' }}</span></div>
                        <div class="detail-item"><label>Date of Birth</label><span>{{ $alumnus->date_of_birth ?? 'N/A' }}</span></div>
                        <div class="detail-item"><label>Account Status</label><span style="color: #16a34a;"><i class="fa-solid fa-circle-check"></i> Active</span></div>
                    </div>
                </div>

                <!-- Mailer Testing Section -->
                <div class="mailer-test-section">
                    <h3><i class="fa-solid fa-paper-plane"></i> Mailer Testing Zone</h3>
                    <p>Click the button below to send a test email to this alumni's registered email address (<strong>{{ $alumnus->email }}</strong>).</p>
                    
                    <form action="{{ route('admin.alumni.send-test-email', $alumnus->id) }}" method="POST" id="testEmailForm">
                        @csrf
                        <button type="submit" class="btn-send-email" id="sendEmailBtn">
                            <i class="fa-solid fa-envelope"></i>
                            <span>Send Test Email</span>
                        </button>
                    </form>
                </div>
            </div>
        </main>
    </div>

    <!-- Alert Toast -->
    <div id="alertToast" class="alert-toast">
        <i class="alert-icon fa-solid fa-circle-check"></i>
        <span class="alert-message"></span>
        <button class="alert-close" onclick="hideAlert()"><i class="fa-solid fa-xmark"></i></button>
    </div>

    <script>
        function toggleMobileMenu() {
            const sidebar = document.getElementById('adminSidebar');
            const overlay = document.getElementById('mobileOverlay');
            sidebar.classList.toggle('mobile-open');
            overlay.classList.toggle('active');
            document.body.style.overflow = sidebar.classList.contains('mobile-open') ? 'hidden' : '';
        }

        function showAlert(message, type = 'success') {
            const toast = document.getElementById('alertToast');
            const icon = toast.querySelector('.alert-icon');
            const msg = toast.querySelector('.alert-message');
            const icons = { success: 'fa-circle-check', error: 'fa-circle-exclamation', info: 'fa-circle-info', warning: 'fa-circle-exclamation' };
            const colors = { success: 'var(--success)', error: 'var(--danger)', info: 'var(--info)', warning: 'var(--warning)' };
            
            icon.className = `alert-icon fa-solid ${icons[type] || icons.success}`;
            toast.style.borderColor = colors[type] || colors.success;
            msg.textContent = message;
            toast.classList.add('show');
            setTimeout(() => hideAlert(), 4000);
        }

        function hideAlert() { document.getElementById('alertToast').classList.remove('show'); }

        // Loading state for button
        document.getElementById('testEmailForm').addEventListener('submit', function() {
            const btn = document.getElementById('sendEmailBtn');
            btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> <span>Sending...</span>';
            btn.disabled = true;
        });

        // Show flash messages from session
        @if(session('success'))
            window.addEventListener('DOMContentLoaded', () => showAlert("{{ session('success') }}", 'success'));
        @endif
        @if(session('error'))
            window.addEventListener('DOMContentLoaded', () => showAlert("{{ session('error') }}", 'error'));
        @endif

        document.querySelectorAll('.nav-item').forEach(item => {
            item.addEventListener('click', function() { if (window.innerWidth <= 1024) toggleMobileMenu(); });
        });
    </script>
</body>
</html>