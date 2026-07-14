<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard | LumiNUs Admin</title>

    <!-- Fonts & Icons -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    
    <!-- Stylesheets -->
    <link rel="stylesheet" href="/css/admin.css">
    <link rel="stylesheet" href="/css/dashboard_modern.css">
    <link rel="icon" type="image/png" href="/assets/logos/LumiNUs_Icon.png">

    <!-- Leaflet & Chart.js CDN -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
                <a href="/admin/dashboard" class="nav-item active">
                    <i class="fa-solid fa-chart-line"></i>
                    <span>Dashboard</span>
                </a>
                <a href="{{ route('admin.directory') }}" class="nav-item">
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
                <a href="{{ route('admin.settings') }}" class="nav-item">
                    <i class="fa-solid fa-gear"></i>
                    <span>Settings</span>
                </a>
            </nav>
            
            {{-- <div class="sidebar-footer">
                <a href="{{ route('admin.logout') }}" class="nav-item logout-btn">
                    <i class="fa-solid fa-right-from-bracket"></i>
                    <span>Sign Out</span>
                </a>
            </div> --}}
        </aside>

        <!-- Main Content -->
        <main class="admin-main">
            <!-- Mobile Menu Toggle -->
            <button class="mobile-menu-toggle" id="mobileMenuToggle" onclick="toggleMobileMenu()">
                <i class="fa-solid fa-bars"></i>
            </button>

            <header class="page-header">
                <div class="header-content">
                    <div class="header-title-section">
                        <h1 class="page-title">
                            <i class="fa-solid fa-chart-line"></i>
                            Admin Dashboard
                        </h1>
                        <p class="page-subtitle">Overview of alumni engagement and platform activity</p>
                    </div>
                </div>
            </header>

            <!-- TAB NAVIGATION -->
            <div class="tab-navigation">
                <button class="tab-btn active" onclick="showTab('main')">
                    <i class="fa-solid fa-chart-pie"></i>
                    <span>Main Dashboard</span>
                </button>
                <button class="tab-btn" onclick="showTab('events')">
                    <i class="fa-solid fa-calendar-check"></i>
                    <span>Events</span>
                </button>
                <button class="tab-btn" onclick="showTab('alumni')">
                    <i class="fa-solid fa-users"></i>
                    <span>Alumni Info</span>
                </button>
                <button class="tab-btn" onclick="showTab('tracer')">
                    <i class="fa-solid fa-file-lines"></i>
                    <span>Tracer Forms</span>
                </button>
            </div>

            <!-- MAIN DASHBOARD TAB -->
            <div id="tab-main" class="tab-content active">
                
                <!-- KEY METRICS -->
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-icon-wrapper">
                            <div class="stat-icon verified">
                                <i class="fa-solid fa-circle-check"></i>
                            </div>
                        </div>
                        <div class="stat-info">
                            <span class="stat-value">{{ number_format($verifiedAlumniCount) }}</span>
                            <span class="stat-label">Verified Alumni</span>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon-wrapper">
                            <div class="stat-icon active">
                                <i class="fa-solid fa-calendar-check"></i>
                            </div>
                        </div>
                        <div class="stat-info">
                            <span class="stat-value">{{ number_format($activeEventsCount) }}</span>
                            <span class="stat-label">Active Events</span>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon-wrapper">
                            <div class="stat-icon views">
                                <i class="fa-solid fa-file-pen"></i>
                            </div>
                        </div>
                        <div class="stat-info">
                            <span class="stat-value">{{ number_format($totalTracerResponses) }}</span>
                            <span class="stat-label">Tracer Responses</span>
                        </div>
                    </div>
                </div>

                <!-- CHARTS SECTION -->
                <section class="dashboard-section">
                    <div class="chart-card">
                        <div class="card-header">
                            <div class="card-header-left">
                                <h3 class="card-title" id="chart-title">
                                    <i class="fa-solid fa-chart-bar"></i>
                                    Alumni by Year Graduated
                                </h3>
                            </div>
                            <div class="chart-toggle">
                                <button class="toggle-btn active" onclick="showChart('year')" id="btn-year">
                                    <i class="fa-solid fa-calendar"></i>
                                    <span>By Year</span>
                                </button>
                                <button class="toggle-btn" onclick="showChart('program')" id="btn-program">
                                    <i class="fa-solid fa-graduation-cap"></i>
                                    <span>By Program</span>
                                </button>
                            </div>
                        </div>
                        <div class="chart-container-wrapper">
                            <div class="chart-container">
                                <canvas id="yearChart"></canvas>
                                <canvas id="programChart" style="display: none;"></canvas>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- RECENT ACTIVITY -->
                <section class="dashboard-section">
                    <h2 class="section-title">
                        <i class="fa-solid fa-clock-rotate-left"></i>
                        Recent Activity
                    </h2>
                    <div class="activity-grid">
                        <div class="dash-card">
                            <div class="card-header">
                                <h3 class="card-title">
                                    <i class="fa-solid fa-bullhorn"></i>
                                    Recent Announcements
                                </h3>
                                <a href="{{ route('announcements.index') }}" class="link-text">
                                    View All <i class="fa-solid fa-arrow-right"></i>
                                </a>
                            </div>
                            <div class="list-wrapper">
                                @forelse($recentAnnouncements as $announcement)
                                <div class="activity-item">
                                    <div class="activity-icon">
                                        <i class="fa-solid fa-bullhorn"></i>
                                    </div>
                                    <div class="activity-content">
                                        <p class="activity-title">{{ Str::limit($announcement->title, 40) }}</p>
                                        <p class="activity-meta">
                                            <i class="fa-regular fa-calendar"></i>
                                            {{ $announcement->date_posted->format('M d, Y') }}
                                        </p>
                                    </div>
                                </div>
                                @empty
                                <div class="empty-state">
                                    <i class="fa-solid fa-bullhorn"></i>
                                    <p>No announcements yet</p>
                                </div>
                                @endforelse
                            </div>
                        </div>

                        <div class="dash-card">
                            <div class="card-header">
                                <h3 class="card-title">
                                    <i class="fa-solid fa-file-lines"></i>
                                    Latest Tracer Forms
                                </h3>
                                <a href="{{ url('/admin/alumni_tracer') }}" class="link-text">
                                    View All <i class="fa-solid fa-arrow-right"></i>
                                </a>
                            </div>
                            <div class="list-wrapper">
                                @forelse($recentTracerForms as $form)
                                <div class="activity-item">
                                    <div class="activity-icon tracer">
                                        <i class="fa-solid fa-file-signature"></i>
                                    </div>
                                    <div class="activity-content">
                                        <p class="activity-title">{{ Str::limit($form->form_title, 35) }}</p>
                                        <p class="activity-meta">
                                            <i class="fa-regular fa-clock"></i>
                                            {{ $form->created_at->diffForHumans() }}
                                        </p>
                                    </div>
                                    <span class="badge badge-green">
                                        <i class="fa-solid fa-circle"></i>
                                        Active
                                    </span>
                                </div>
                                @empty
                                <div class="empty-state">
                                    <i class="fa-solid fa-file-lines"></i>
                                    <p>No tracer forms yet</p>
                                </div>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </section>
            </div>

            <!-- EVENTS TAB -->
            <div id="tab-events" class="tab-content">
                <section class="dashboard-section">
                    <h2 class="section-title">
                        <i class="fa-solid fa-map-location-dot"></i>
                        Events Management
                    </h2>
                    
                    <div class="dash-card">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fa-solid fa-map-pin"></i>
                                Event Geomap
                            </h3>
                            <span class="card-subtitle">Live event locations</span>
                        </div>
                        <div class="map-wrapper" id="event-map"></div>
                    </div>

                    <div class="dash-card mt-20">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fa-solid fa-list-check"></i>
                                Upcoming Events with Registrations
                            </h3>
                            <a href="{{ route('events.index') }}" class="link-text">
                                Manage Events <i class="fa-solid fa-arrow-right"></i>
                            </a>
                        </div>
                        <div class="list-wrapper">
                            @forelse($upcomingEvents as $event)
                            <div class="event-item-detailed">
                                <div class="event-main">
                                    <div class="event-status-indicator {{ now()->between($event->start_date, $event->end_date) ? 'ongoing' : 'upcoming' }}">
                                        <i class="fa-solid fa-circle"></i>
                                    </div>
                                    <div class="event-details">
                                        <p class="event-title">{{ $event->title }}</p>
                                        <div class="event-meta-row">
                                            <span class="event-meta">
                                                <i class="fa-solid fa-location-dot"></i>
                                                {{ $event->venue_name ?? 'TBA' }}
                                            </span>
                                            <span class="event-meta">
                                                <i class="fa-regular fa-calendar"></i>
                                                {{ $event->start_date->format('M d, Y') }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                <div class="event-stats">
                                    <div class="registration-badge">
                                        <span class="reg-count">{{ $event->registration_count ?? 0 }}</span>
                                        <span class="reg-label">Registered</span>
                                    </div>
                                    <span class="event-date-badge">
                                        <i class="fa-regular fa-calendar"></i>
                                        {{ $event->start_date->format('M d') }}
                                    </span>
                                </div>
                            </div>
                            @empty
                            <div class="empty-state">
                                <i class="fa-solid fa-calendar-xmark"></i>
                                <p>No upcoming events scheduled</p>
                            </div>
                            @endforelse
                        </div>
                    </div>
                </section>
            </div>

            <!-- ALUMNI INFO TAB -->
            <div id="tab-alumni" class="tab-content">
                <section class="dashboard-section">
                    <h2 class="section-title">
                        <i class="fa-solid fa-users-gear"></i>
                        Alumni Information
                    </h2>
                    
                    <div class="dash-card">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fa-solid fa-chart-simple"></i>
                                Detailed Alumni Statistics
                            </h3>
                        </div>
                        <div class="stats-detail-grid">
                            <div class="stat-detail-box">
                                <div class="stat-detail-icon">
                                    <i class="fa-solid fa-user-check"></i>
                                </div>
                                <h4>Total Verified Alumni</h4>
                                <p class="stat-big">{{ number_format($verifiedAlumniCount) }}</p>
                            </div>
                            <div class="stat-detail-box">
                                <div class="stat-detail-icon">
                                    <i class="fa-solid fa-trophy"></i>
                                </div>
                                <h4>Top Program</h4>
                                <p class="stat-big">{{ $chartData['programs'][0] ?? 'N/A' }}</p>
                                <p class="stat-small">{{ $chartData['programs_count'][0] ?? 0 }} alumni</p>
                            </div>
                            <div class="stat-detail-box">
                                <div class="stat-detail-icon">
                                    <i class="fa-solid fa-graduation-cap"></i>
                                </div>
                                <h4>Most Recent Batch</h4>
                                <p class="stat-big">{{ $chartData['years'][0] ?? 'N/A' }}</p>
                                <p class="stat-small">{{ $chartData['years_count'][0] ?? 0 }} graduates</p>
                            </div>
                        </div>
                    </div>

                    <div class="dash-card mt-20">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fa-solid fa-chart-pie"></i>
                                Alumni Distribution by Program
                            </h3>
                        </div>
                        <div class="program-distribution">
                            @foreach($chartData['programs'] as $index => $program)
                            <div class="distribution-item">
                                <div class="distribution-header">
                                    <span class="distribution-name">{{ $program }}</span>
                                    <span class="distribution-count">{{ $chartData['programs_count'][$index] }}</span>
                                </div>
                                <div class="distribution-bar">
                                    <div class="distribution-fill" style="width: {{ ($chartData['programs_count'][$index] / max($chartData['programs_count'])) * 100 }}%"></div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </section>
            </div>

            <!-- TRACER FORMS TAB -->
            <div id="tab-tracer" class="tab-content">
                <section class="dashboard-section">
                    <h2 class="section-title">
                        <i class="fa-solid fa-file-signature"></i>
                        Tracer Forms Management
                    </h2>
                    
                    <div class="dash-card">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fa-solid fa-list-ul"></i>
                                All Tracer Forms
                            </h3>
                            <a href="{{ url('/admin/alumni_tracer') }}" class="btn btn-primary btn-sm">
                                <i class="fa-solid fa-plus"></i>
                                <span>Create New</span>
                            </a>
                        </div>
                        <div class="list-wrapper">
                            @forelse($recentTracerForms as $form)
                            <div class="tracer-item">
                                <div class="tracer-icon">
                                    <i class="fa-solid fa-file-lines"></i>
                                </div>
                                <div class="tracer-info">
                                    <p class="tracer-title">{{ $form->form_title }}</p>
                                    <p class="tracer-meta">
                                        <i class="fa-regular fa-clock"></i>
                                        Published {{ $form->created_at->diffForHumans() }}
                                    </p>
                                </div>
                                <div class="tracer-status">
                                    <span class="badge {{ $form->status == 1 ? 'badge-green' : 'badge-gray' }}">
                                        <i class="fa-solid fa-circle"></i>
                                        {{ $form->status == 1 ? 'Active' : 'Draft' }}
                                    </span>
                                </div>
                            </div>
                            @empty
                            <div class="empty-state">
                                <i class="fa-solid fa-file-circle-plus"></i>
                                <p>No tracer forms published yet</p>
                            </div>
                            @endforelse
                        </div>
                    </div>
                </section>
            </div>

        </main>
    </div>

    <script>
    // ========== MOBILE MENU TOGGLE ==========
    function toggleMobileMenu() {
        const sidebar = document.getElementById('adminSidebar');
        const overlay = document.getElementById('mobileOverlay');
        sidebar.classList.toggle('mobile-open');
        overlay.classList.toggle('active');
        document.body.style.overflow = sidebar.classList.contains('mobile-open') ? 'hidden' : '';
    }

    // Close sidebar on nav item click (mobile)
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
                document.getElementById('adminSidebar')?.classList.remove('mobile-open');
                document.getElementById('mobileOverlay')?.classList.remove('active');
                document.body.style.overflow = '';
            }
        }, 250);
    });

    // ========== TAB NAVIGATION ==========
    let mapInitialized = false;
    
    function showTab(tabName) {
        // Hide all tabs
        document.querySelectorAll('.tab-content').forEach(tab => {
            tab.classList.remove('active');
        });
        
        // Remove active class from all buttons
        document.querySelectorAll('.tab-btn').forEach(btn => {
            btn.classList.remove('active');
        });
        
        // Show selected tab
        const targetTab = document.getElementById('tab-' + tabName);
        if (targetTab) {
            targetTab.classList.add('active');
        }
        
        // Add active class to clicked button
        const clickedBtn = document.querySelector(`.tab-btn[onclick*="${tabName}"]`);
        if (clickedBtn) {
            clickedBtn.classList.add('active');
        }
        
        // Initialize map ONLY when Events tab is shown
        if (tabName === 'events' && !mapInitialized) {
            setTimeout(initializeMap, 200);
        }
    }

    // ========== MAP INITIALIZATION ==========
    function initializeMap() {
        const eventLocations = @json($eventLocations);
        const mapContainer = document.getElementById('event-map');
        
        if (!eventLocations || eventLocations.length === 0) {
            mapContainer.innerHTML = `
                <div class="map-empty-state">
                    <i class="fa-solid fa-map-location-dot"></i>
                    <p>No event locations available</p>
                </div>`;
            mapInitialized = true;
            return;
        }
        
        const map = L.map('event-map', {
            zoomControl: true,
            scrollWheelZoom: false
        }).setView([14.6091, 121.0223], 12);
        
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
            maxZoom: 19
        }).addTo(map);
        
        const bounds = [];
        eventLocations.forEach(evt => {
            const lat = parseFloat(evt.latitude);
            const lng = parseFloat(evt.longitude);
            
            if (!isNaN(lat) && !isNaN(lng)) {
                const marker = L.marker([lat, lng]).addTo(map);
                marker.bindPopup(`
                    <div class="map-popup">
                        <strong>${evt.title}</strong>
                        <span><i class="fa-solid fa-location-dot"></i> ${evt.venue_name || 'TBA'}</span>
                        <span><i class="fa-regular fa-calendar"></i> ${evt.start_date} - ${evt.end_date}</span>
                    </div>
                `);
                bounds.push([lat, lng]);
            }
        });
        
        if (bounds.length > 0) {
            map.fitBounds(bounds, { padding: [40, 40], maxZoom: 14 });
        }
        
        setTimeout(() => {
            map.invalidateSize();
        }, 100);
        
        mapInitialized = true;
    }

    // ========== CHART TOGGLE ==========
    let yearChartInstance = null;
    let programChartInstance = null;
    
    function showChart(type) {
        const yearChart = document.getElementById('yearChart');
        const programChart = document.getElementById('programChart');
        const btnYear = document.getElementById('btn-year');
        const btnProgram = document.getElementById('btn-program');
        const chartTitle = document.getElementById('chart-title');
        
        if (type === 'year') {
            yearChart.style.display = 'block';
            programChart.style.display = 'none';
            btnYear.classList.add('active');
            btnProgram.classList.remove('active');
            chartTitle.innerHTML = '<i class="fa-solid fa-chart-bar"></i> Alumni by Year Graduated';
            
            if (!yearChartInstance && chartData.years?.length > 0) {
                yearChartInstance = new Chart(yearChart, {
                    type: 'bar',
                    data: {
                        labels: chartData.years,
                        datasets: [{
                            label: 'Number of Alumni',
                            data: chartData.years_count,
                            backgroundColor: 'rgba(50, 65, 140, 0.8)',
                            borderColor: 'rgba(50, 65, 140, 1)',
                            borderWidth: 1,
                            borderRadius: 8
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: { legend: { display: false } },
                        scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } }
                    }
                });
            }
        } else {
            yearChart.style.display = 'none';
            programChart.style.display = 'block';
            btnYear.classList.remove('active');
            btnProgram.classList.add('active');
            chartTitle.innerHTML = '<i class="fa-solid fa-chart-bar"></i> Alumni by Program';
            
            if (!programChartInstance && chartData.programs?.length > 0) {
                programChartInstance = new Chart(programChart, {
                    type: 'bar',
                    data: {
                        labels: chartData.programs,
                        datasets: [{
                            label: 'Number of Alumni',
                            data: chartData.programs_count,
                            backgroundColor: 'rgba(251, 209, 23, 0.8)',
                            borderColor: 'rgba(251, 209, 23, 1)',
                            borderWidth: 1,
                            borderRadius: 8
                        }]
                    },
                    options: {
                        indexAxis: 'y',
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: { legend: { display: false } },
                        scales: { x: { beginAtZero: true } }
                    }
                });
            }
        }
    }

    // ========== INITIALIZE ON PAGE LOAD ==========
    const chartData = @json($chartData);
    
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize Year chart (visible by default)
        const yearChart = document.getElementById('yearChart');
        if (yearChart && chartData.years?.length > 0) {
            yearChartInstance = new Chart(yearChart, {
                type: 'bar',
                data: {
                    labels: chartData.years,
                    datasets: [{
                        label: 'Number of Alumni',
                        data: chartData.years_count,
                        backgroundColor: 'rgba(50, 65, 140, 0.8)',
                        borderColor: 'rgba(50, 65, 140, 1)',
                        borderWidth: 1,
                        borderRadius: 8
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } }
                }
            });
        }
        
        if (document.getElementById('tab-events')?.classList.contains('active')) {
            setTimeout(initializeMap, 200);
        }
    });
</script>

</body>
</html>