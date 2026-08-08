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
    
    <!-- Leaflet CSS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    
    <!-- Stylesheets -->
    <link rel="stylesheet" href="/css/admin.css">
    <link rel="stylesheet" href="/css/admin-fixed-navbar.css">
    <link rel="stylesheet" href="/css/directory_modern.css">
    <link rel="icon" type="image/png" href="/assets/logos/LumiNUs_Icon.png">

    <style>
        /* Profile specific styles */
        .profile-container { max-width: 1200px; margin: 0 auto; padding: 20px; }
        .profile-card { background: #fff; border-radius: 12px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); padding: 30px; margin-bottom: 20px; }
        .profile-header { display: flex; align-items: center; gap: 20px; margin-bottom: 30px; border-bottom: 1px solid #eee; padding-bottom: 20px; }
        .profile-photo { width: 120px; height: 120px; border-radius: 50%; object-fit: cover; border: 4px solid #f0f0f0; }
        .profile-info h2 { margin: 0; font-size: 1.8rem; color: #1e293b; }
        .profile-info p { margin: 5px 0 0; color: #64748b; font-size: 1.1rem; }
        .profile-details { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; }
        .detail-item { background: #f8fafc; padding: 15px; border-radius: 8px; }
        .detail-item label { display: block; font-size: 0.85rem; color: #64748b; margin-bottom: 5px; font-weight: 500; }
        .detail-item span { font-size: 1rem; color: #1e293b; font-weight: 500; }
        
        /* Section Titles */
        .section-title { margin-top: 0; margin-bottom: 20px; color: #1e293b; font-size: 1.3rem; display: flex; align-items: center; gap: 10px; }
        .section-title i { color: #3b82f6; }
        
        /* Address Cards */
        .address-card { background: #f8fafc; padding: 20px; border-radius: 8px; margin-bottom: 15px; border-left: 4px solid #3b82f6; }
        .address-card:last-child { margin-bottom: 0; }
        .address-card .address-type { font-weight: 600; color: #1e293b; margin-bottom: 8px; font-size: 1.05rem; }
        .address-card .address-type i { color: #3b82f6; margin-right: 8px; }
        .address-card .address-text { color: #475569; line-height: 1.6; }
        .address-card .coordinates { font-size: 0.9rem; color: #64748b; margin-top: 8px; padding-top: 8px; border-top: 1px dashed #e2e8f0; }
        .address-card .coordinates i { color: #3b82f6; margin-right: 6px; }
        
        /* Map Container */
        .map-container { margin-top: 15px; border-radius: 8px; overflow: hidden; border: 1px solid #e2e8f0; }
        #alumniMap { height: 300px; width: 100%; }
        
        .no-address { text-align: center; padding: 40px 20px; color: #94a3b8; }
        .no-address i { font-size: 3rem; display: block; margin-bottom: 15px; color: #cbd5e1; }
        .no-address p { font-size: 1.1rem; margin: 0; }
        
        /* Verification Badge */
        .verification-badge { display: inline-block; padding: 4px 12px; border-radius: 20px; font-size: 0.85rem; font-weight: 500; }
        .verification-badge.verified { background: #dcfce7; color: #166534; }
        .verification-badge.pending { background: #fef9c3; color: #854d0e; }
        .verification-badge.rejected { background: #fee2e2; color: #991b1b; }
        
        /* Status Badge */
        .status-badge { display: inline-block; padding: 4px 12px; border-radius: 20px; font-size: 0.85rem; font-weight: 500; }
        .status-badge.active { background: #dcfce7; color: #21ac56; }
        .status-badge.inactive { background: #fee2e2; color: #991b1b; }
        
        /* Professional Items */
        .professional-item { background: #f8fafc; padding: 15px; border-radius: 8px; margin-bottom: 10px; }
        .professional-item:last-child { margin-bottom: 0; }
        .professional-item .item-title { font-weight: 600; color: #1e293b; }
        .professional-item .item-subtitle { color: #64748b; font-size: 0.95rem; }
        .professional-item .item-dates { color: #94a3b8; font-size: 0.85rem; }
        
        /* Skill Tags */
        .skill-tag { display: inline-block; background: #e0e7ff; color: #3730a3; padding: 5px 14px; border-radius: 20px; font-size: 0.9rem; font-weight: 500; margin: 4px; }
        
        /* Follower Stats */
        .follower-stat { text-align: center; padding: 20px; background: #f8fafc; border-radius: 8px; }
        .follower-stat .number { font-size: 2rem; font-weight: 700; color: #1e293b; display: block; }
        .follower-stat .label { color: #64748b; font-size: 0.9rem; }
        
        /* Event Cards */
        .event-item { background: #f8fafc; padding: 15px; border-radius: 8px; margin-bottom: 10px; display: flex; justify-content: space-between; align-items: center; }
        .event-item:last-child { margin-bottom: 0; }
        .event-item .event-info { flex: 1; }
        .event-item .event-title { font-weight: 600; color: #1e293b; }
        .event-item .event-details { color: #64748b; font-size: 0.9rem; }
        .event-item .event-status { padding: 4px 12px; border-radius: 20px; font-size: 0.8rem; font-weight: 500; background: #dcfce7; color: #166534; }
        
        /* Post Cards */
        .post-item { background: #f8fafc; padding: 20px; border-radius: 8px; margin-bottom: 15px; }
        .post-item:last-child { margin-bottom: 0; }
        .post-item .post-caption { color: #1e293b; margin-bottom: 10px; }
        .post-item .post-meta { color: #94a3b8; font-size: 0.85rem; display: flex; gap: 15px; flex-wrap: wrap; }
        .post-item .post-meta i { margin-right: 4px; }
        .post-item .post-images { display: grid; grid-template-columns: repeat(auto-fill, minmax(150px, 1fr)); gap: 10px; margin-top: 10px; }
        .post-item .post-images img { width: 100%; height: 150px; object-fit: cover; border-radius: 6px; }
        .post-item .post-comments { margin-top: 10px; padding-top: 10px; border-top: 1px solid #e2e8f0; }
        .post-item .post-comment { font-size: 0.9rem; color: #475569; margin-bottom: 5px; }
        .post-item .post-comment strong { color: #1e293b; }
        
        .no-data { text-align: center; padding: 30px 20px; color: #94a3b8; }
        .no-data i { font-size: 2rem; display: block; margin-bottom: 10px; color: #cbd5e1; }
        
        /* Mailer Test Section */
        .mailer-test-section { background: #eff6ff; border: 1px dashed #3b82f6; border-radius: 12px; padding: 25px; text-align: center; margin-top: 20px; }
        .mailer-test-section h3 { color: #1e40af; margin-top: 0; }
        .btn-send-email { background: #2563eb; color: white; padding: 12px 24px; border: none; border-radius: 8px; font-size: 1rem; font-weight: 600; cursor: pointer; transition: background 0.2s; display: inline-flex; align-items: center; gap: 8px; }
        .btn-send-email:hover { background: #1d4ed8; }
        .btn-send-email:disabled { background: #93c5fd; cursor: not-allowed; }
        
        .btn-back { background: #e2e8f0; color: #475569; padding: 10px 20px; border: none; border-radius: 8px; font-weight: 500; text-decoration: none; display: inline-flex; align-items: center; gap: 8px; margin-bottom: 20px; }
        .btn-back:hover { background: #cbd5e1; }
        
        /* Responsive */
        @media (max-width: 640px) {
            .profile-header { flex-direction: column; text-align: center; }
            .profile-details { grid-template-columns: 1fr; }
            #alumniMap { height: 200px; }
            .event-item { flex-direction: column; align-items: flex-start; gap: 10px; }
            .post-item .post-images { grid-template-columns: 1fr; }
            .follower-stats { grid-template-columns: 1fr 1fr; }
        }
        
        /* Grid layouts */
        .two-col-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
        .three-col-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; }
        
        @media (max-width: 768px) {
            .two-col-grid { grid-template-columns: 1fr; }
            .three-col-grid { grid-template-columns: 1fr; }
        }
    </style>
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
                        <p class="page-subtitle">View complete details for {{ $alumnus->first_name }} {{ $alumnus->last_name }}</p>
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

                <!-- Profile Information Card -->
                <div class="profile-card">
                    <div class="profile-header">
                        @php
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
                            <h2>
                                {{ $alumnus->first_name }} {{ $alumnus->middle_name }} {{ $alumnus->last_name }}
                                @if($alumnus->verification_status)
                                    <span class="verification-badge {{ $alumnus->verification_status }}">
                                        <i class="fa-solid fa-{{ $alumnus->verification_status == 'verified' ? 'check-circle' : ($alumnus->verification_status == 'pending' ? 'clock' : 'times-circle') }}"></i>
                                        {{ ucfirst($alumnus->verification_status) }}
                                    </span>
                                @endif
                            </h2>
                            <p><i class="fa-solid fa-graduation-cap"></i> {{ $alumnus->program ?? 'N/A' }} &bull; Class of {{ optional($alumnus->year_graduated)->format('Y') ?? 'N/A' }}</p>
                            <p style="margin-top: 8px;">
                                <span class="status-badge {{ ($alumnus->account_status ?? 1) == 1 ? 'active' : 'inactive' }}">
                                    <i class="fa-solid fa-circle"></i>
                                    {{ ($alumnus->account_status ?? 1) == 1 ? 'Active' : 'Restricted' }}
                                </span>
                                @if($alumnus->is_online)
                                    <span class="status-badge active" style="margin-left: 8px;">
                                        <i class="fa-solid fa-circle"></i> Online
                                    </span>
                                @endif
                            </p>
                        </div>
                    </div>

                    <div class="profile-details">
                        <div class="detail-item"><label>Student ID</label><span>{{ $alumnus->student_id_number ?? 'N/A' }}</span></div>
                        <div class="detail-item"><label>Email Address</label><span>{{ $alumnus->email ?? 'N/A' }}</span></div>
                        <div class="detail-item"><label>Phone Number</label><span>{{ $alumnus->phone_number ?? 'N/A' }}</span></div>
                        <div class="detail-item"><label>Sex</label><span>{{ $alumnus->sex ?? 'N/A' }}</span></div>
                        <div class="detail-item"><label>Date of Birth</label><span>{{ $alumnus->date_of_birth ? date('F d, Y', strtotime($alumnus->date_of_birth)) : 'N/A' }}</span></div>
                        <div class="detail-item"><label>Joined</label><span>{{ $alumnus->created_at ? date('M d, Y', strtotime($alumnus->created_at)) : 'N/A' }}</span></div>
                        @if($alumnus->alumni_bio)
                            <div class="detail-item" style="grid-column: 1 / -1;">
                                <label>Biography</label>
                                <span style="font-weight: 400; line-height: 1.6;">{{ $alumnus->alumni_bio }}</span>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Address Information Card -->
                <div class="profile-card">
                    <h3 class="section-title">
                        <i class="fa-solid fa-location-dot"></i>
                        Address Information
                    </h3>
                    
                    @if($alumnus->addresses->isNotEmpty())
                        @foreach($alumnus->addresses as $address)
                            <div class="address-card">
                                <div class="address-type">
                                    <i class="fa-solid fa-{{ $address->address_type == 'home' ? 'house' : ($address->address_type == 'work' ? 'briefcase' : 'map-pin') }}"></i>
                                    {{ ucfirst($address->address_type ?? 'Other') }} Address
                                </div>
                                <div class="address-text">
                                    @php
                                        $addressParts = [];
                                        if ($address->street) $addressParts[] = $address->street;
                                        if ($address->barangay) $addressParts[] = $address->barangay;
                                        if ($address->municipality) $addressParts[] = $address->municipality;
                                        if ($address->province) $addressParts[] = $address->province;
                                        if ($address->region) $addressParts[] = $address->region;
                                        $fullAddress = implode(', ', $addressParts);
                                        if ($address->zip_code) $fullAddress .= ' ' . $address->zip_code;
                                    @endphp
                                    {{ $fullAddress ?: 'No address details provided' }}
                                </div>
                                
                                @if($address->latitude && $address->longitude)
                                    <div class="coordinates">
                                        <i class="fa-solid fa-globe"></i>
                                        Coordinates: {{ number_format((float)$address->latitude, 6) }}, {{ number_format((float)$address->longitude, 6) }}
                                    </div>
                                @endif
                            </div>
                        @endforeach
                        
                        @php
                            $hasCoordinates = $alumnus->addresses->filter(function($addr) {
                                return $addr->latitude && $addr->longitude;
                            })->isNotEmpty();
                        @endphp
                        
                        @if($hasCoordinates)
                            <div class="map-container">
                                <div id="alumniMap"></div>
                            </div>
                        @endif
                    @else
                        <div class="no-address">
                            <i class="fa-solid fa-map-pin"></i>
                            <p>No address information available for this alumni.</p>
                        </div>
                    @endif
                </div>

                <!-- Professional Information: Employment + Skills -->
                <div class="profile-card">
                    <h3 class="section-title">
                        <i class="fa-solid fa-briefcase"></i>
                        Professional Information
                    </h3>
                    
                    <div class="two-col-grid">
                        <!-- Employment -->
                        <div>
                            <h4 style="color: #475569; margin-bottom: 15px; font-size: 1rem;">
                                <i class="fa-solid fa-building" style="color: #3b82f6;"></i> Employment History
                            </h4>
                            @if($alumnus->employments->isNotEmpty())
                                @foreach($alumnus->employments as $employment)
                                    <div class="professional-item">
                                        <div class="item-title">{{ $employment->job_title }}</div>
                                        <div class="item-subtitle">{{ $employment->company }}</div>
                                        <div class="item-subtitle" style="font-size: 0.9rem;">
                                            <i class="fa-solid fa-location-dot"></i> {{ $employment->location }}
                                        </div>
                                        <div class="item-dates">
                                            <i class="fa-regular fa-calendar"></i>
                                            {{ date('M Y', strtotime($employment->start_date)) }} - 
                                            @if($employment->is_current)
                                                <span style="color: #16a34a; font-weight: 500;">Present</span>
                                            @elseif($employment->end_date)
                                                {{ date('M Y', strtotime($employment->end_date)) }}
                                            @else
                                                N/A
                                            @endif
                                            @if($employment->is_current)
                                                <span style="display: inline-block; background: #dcfce7; color: #166534; padding: 2px 10px; border-radius: 12px; font-size: 0.75rem; margin-left: 8px;">Current</span>
                                            @endif
                                        </div>
                                        @if($employment->career_description)
                                            <div style="margin-top: 8px; color: #64748b; font-size: 0.9rem;">
                                                {{ $employment->career_description }}
                                            </div>
                                        @endif
                                    </div>
                                @endforeach
                            @else
                                <div class="no-data">
                                    <i class="fa-solid fa-briefcase"></i>
                                    <p>No employment history available.</p>
                                </div>
                            @endif
                        </div>
                        
                        <!-- Skills -->
                        <div>
                            <h4 style="color: #475569; margin-bottom: 15px; font-size: 1rem;">
                                <i class="fa-solid fa-code" style="color: #3b82f6;"></i> Skills
                            </h4>
                            @if($alumnus->skills->isNotEmpty())
                                <div style="display: flex; flex-wrap: wrap; gap: 4px;">
                                    @foreach($alumnus->skills as $skill)
                                        <span class="skill-tag">{{ $skill->skill_name }}</span>
                                    @endforeach
                                </div>
                            @else
                                <div class="no-data">
                                    <i class="fa-solid fa-code"></i>
                                    <p>No skills listed.</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Tracer Form Status -->
                <div class="profile-card">
                    <h3 class="section-title">
                        <i class="fa-solid fa-clipboard-list"></i>
                        Tracer Form Status
                    </h3>
                    
                    @if($alumnus->tracerResponses->isNotEmpty())
                        @foreach($alumnus->tracerResponses as $response)
                            <div class="professional-item" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap;">
                                <div>
                                    <div class="item-title">Form #{{ $response->form_id }}</div>
                                    <div class="item-subtitle">
                                        Status: <span class="status-badge {{ $response->status == 'completed' ? 'active' : 'pending' }}">
                                            {{ ucfirst($response->status) }}
                                        </span>
                                    </div>
                                    <div class="item-dates">
                                        <i class="fa-regular fa-calendar"></i>
                                        Started: {{ $response->created_at ? date('M d, Y', strtotime($response->created_at)) : 'N/A' }}
                                        @if($response->submitted_at)
                                            &bull; Submitted: {{ date('M d, Y', strtotime($response->submitted_at)) }}
                                        @endif
                                    </div>
                                </div>
                                <div>
                                    <span class="event-status" style="background: {{ $response->status == 'completed' ? '#dcfce7' : '#fef9c3' }}; color: {{ $response->status == 'completed' ? '#166534' : '#854d0e' }};">
                                        <i class="fa-solid fa-{{ $response->status == 'completed' ? 'check' : 'clock' }}"></i>
                                        {{ ucfirst($response->status) }}
                                    </span>
                                </div>
                            </div>
                        @endforeach
                    @else
                        <div class="no-data">
                            <i class="fa-solid fa-clipboard-list"></i>
                            <p>No tracer form responses recorded.</p>
                        </div>
                    @endif
                </div>

                <!-- Event Registrations -->
                <div class="profile-card">
                    <h3 class="section-title">
                        <i class="fa-solid fa-calendar-check"></i>
                        Event Registrations
                    </h3>
                    
                    @php
                        $activeRegistrations = $alumnus->eventRegistrations->filter(function($reg) {
                            return $reg->event && $reg->event->status == 1;
                        });
                    @endphp
                    
                    @if($activeRegistrations->isNotEmpty())
                        @foreach($activeRegistrations as $registration)
                            <div class="event-item">
                                <div class="event-info">
                                    <div class="event-title">{{ $registration->event->title }}</div>
                                    <div class="event-details">
                                        <i class="fa-regular fa-calendar"></i>
                                        {{ date('M d, Y', strtotime($registration->event->start_date)) }} - 
                                        {{ date('M d, Y', strtotime($registration->event->end_date)) }}
                                        @if($registration->event->event_type)
                                            &bull; {{ ucfirst($registration->event->event_type) }}
                                        @endif
                                    </div>
                                    <div class="event-details">
                                        <i class="fa-regular fa-clock"></i>
                                        Capacity: {{ $registration->event->max_capacity ?? 'N/A' }}
                                        @if($registration->rsvp_date)
                                            &bull; RSVP: {{ date('M d, Y', strtotime($registration->rsvp_date)) }}
                                        @endif
                                    </div>
                                </div>
                                <div>
                                    <span class="event-status">
                                        <i class="fa-solid fa-check-circle"></i>
                                        Registered
                                    </span>
                                </div>
                            </div>
                        @endforeach
                    @else
                        <div class="no-data">
                            <i class="fa-solid fa-calendar-check"></i>
                            <p>No active event registrations.</p>
                        </div>
                    @endif
                </div>

                <!-- Social Information: Followers, Following -->
                <div class="profile-card">
                    <h3 class="section-title">
                        <i class="fa-solid fa-users"></i>
                        Social Information
                    </h3>
                    
                    <div class="three-col-grid">
                        <div class="follower-stat">
                            <span class="number">{{ $alumnus->followers->count() }}</span>
                            <span class="label"><i class="fa-solid fa-user-plus"></i> Followers</span>
                        </div>
                        <div class="follower-stat">
                            <span class="number">{{ $alumnus->following->count() }}</span>
                            <span class="label"><i class="fa-solid fa-user-friends"></i> Following</span>
                        </div>
                        <div class="follower-stat">
                            <span class="number">{{ $alumnus->posts->count() }}</span>
                            <span class="label"><i class="fa-solid fa-pen"></i> Posts</span>
                        </div>
                    </div>
                </div>

                <!-- Posts Section -->
                <div class="profile-card">
                    <h3 class="section-title">
                        <i class="fa-solid fa-newspaper"></i>
                        Alumni Posts
                    </h3>
                    
                    @if($alumnus->posts->isNotEmpty())
                        @foreach($alumnus->posts->sortByDesc('created_at') as $post)
                            <div class="post-item">
                                @if($post->caption)
                                    <div class="post-caption">{{ $post->caption }}</div>
                                @endif
                                
                                @if($post->images->isNotEmpty())
                                    <div class="post-images">
                                        @foreach($post->images as $image)
                                            <img src="{{ $image->image_path }}" alt="Post image" onerror="this.style.display='none'">
                                        @endforeach
                                    </div>
                                @endif
                                
                                <div class="post-meta">
                                    <span><i class="fa-regular fa-calendar"></i> {{ $post->created_at ? date('M d, Y h:i A', strtotime($post->created_at)) : 'N/A' }}</span>
                                    <span><i class="fa-regular fa-heart"></i> {{ $post->reactions->count() }} reactions</span>
                                    <span><i class="fa-regular fa-comment"></i> {{ $post->comments->count() }} comments</span>
                                    <span>
                                        <span class="status-badge active" style="font-size: 0.75rem; padding: 2px 10px;">
                                            <i class="fa-solid fa-eye"></i> {{ ucfirst($post->visibility ?? 'public') }}
                                        </span>
                                    </span>
                                    <span>
                                        <span class="status-badge {{ $post->moderation_status == 'approved' ? 'active' : 'pending' }}" style="font-size: 0.75rem; padding: 2px 10px;">
                                            <i class="fa-solid fa-{{ $post->moderation_status == 'approved' ? 'check' : 'clock' }}"></i>
                                            {{ ucfirst($post->moderation_status ?? 'pending') }}
                                        </span>
                                    </span>
                                </div>
                                
                                @if($post->comments->isNotEmpty())
                                    <div class="post-comments">
                                        <strong style="font-size: 0.9rem; color: #475569;">Recent Comments:</strong>
                                        @foreach($post->comments->take(3) as $comment)
                                            <div class="post-comment">
                                                <strong>{{ $comment->alumni->first_name ?? 'Unknown' }} {{ $comment->alumni->last_name ?? '' }}</strong>
                                                {{ $comment->comment }}
                                                <span style="color: #94a3b8; font-size: 0.8rem;">
                                                    ({{ $comment->created_at ? date('M d, Y', strtotime($comment->created_at)) : 'N/A' }})
                                                </span>
                                            </div>
                                        @endforeach
                                        @if($post->comments->count() > 3)
                                            <div style="color: #64748b; font-size: 0.85rem; margin-top: 5px;">
                                                And {{ $post->comments->count() - 3 }} more comments...
                                            </div>
                                        @endif
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    @else
                        <div class="no-data">
                            <i class="fa-solid fa-newspaper"></i>
                            <p>No posts from this alumni.</p>
                        </div>
                    @endif
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

    <!-- Leaflet JavaScript -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    
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

        // Initialize map with markers for all addresses that have coordinates
        document.addEventListener('DOMContentLoaded', function() {
            @php
                $addressesWithCoords = $alumnus->addresses->filter(function($addr) {
                    return $addr->latitude && $addr->longitude;
                });
            @endphp
            
            @if($addressesWithCoords->isNotEmpty())
                var map = L.map('alumniMap');
                
                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
                }).addTo(map);
                
                var markers = [];
                
                @foreach($addressesWithCoords as $address)
                    var lat = {{ $address->latitude }};
                    var lng = {{ $address->longitude }};
                    
                    var marker = L.marker([lat, lng]);
                    
                    @php
                        $popupParts = [];
                        if ($address->address_type) $popupParts[] = '<strong>' . ucfirst($address->address_type) . ' Address</strong>';
                        if ($address->street) $popupParts[] = $address->street;
                        if ($address->barangay) $popupParts[] = $address->barangay;
                        if ($address->municipality) $popupParts[] = $address->municipality;
                        if ($address->province) $popupParts[] = $address->province;
                        if ($address->region) $popupParts[] = $address->region;
                        if ($address->zip_code) $popupParts[] = 'ZIP: ' . $address->zip_code;
                        $popupText = implode('<br>', $popupParts);
                    @endphp
                    
                    marker.bindPopup(`{!! $popupText !!}`);
                    marker.addTo(map);
                    markers.push(marker);
                @endforeach
                
                if (markers.length > 1) {
                    var group = L.featureGroup(markers);
                    map.fitBounds(group.getBounds().pad(0.1));
                } else if (markers.length === 1) {
                    map.setView([markers[0].getLatLng().lat, markers[0].getLatLng().lng], 14);
                }
                
                setTimeout(function() {
                    map.invalidateSize();
                }, 500);
            @endif
        });
    </script>
</body>
</html>