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

    <!-- Chart.js CDN -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <!-- Leaflet.js for Maps -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

    <!-- Add after Leaflet CSS/JS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.5.0/dist/MarkerCluster.css" />
    <link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.5.0/dist/MarkerCluster.Default.css" />
    <script src="https://unpkg.com/leaflet.markercluster@1.5.0/dist/leaflet.markercluster.js"></script>

    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <!-- Export libraries -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.31/jspdf.plugin.autotable.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
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

            @php
                $currentAdmin = null;
                $adminId = session('admin_id');
                if ($adminId) {
                    $currentAdmin = \App\Models\Admin::find($adminId);
                }
                $accessibleModules = $currentAdmin ? $currentAdmin->getAccessibleModules() : [];
            @endphp

            <nav class="sidebar-nav">
                <p class="nav-section-title">Admin Menu</p>
                
                @if(isset($accessibleModules['dashboard']))
                <a href="/admin/dashboard" class="nav-item active">
                    <i class="fa-solid fa-chart-line"></i><span>Dashboard</span>
                </a>
                @endif
                
                @if(isset($accessibleModules['directory']))
                <a href="/admin/directory" class="nav-item">
                    <i class="fa-solid fa-users"></i><span>Alumni Directory</span>
                </a>
                @endif
                
                @if(isset($accessibleModules['announcements']))
                <a href="{{ route('announcements.index') }}" class="nav-item">
                    <i class="fa-solid fa-bullhorn"></i><span>Announcements</span>
                </a>
                @endif
                
                @if(isset($accessibleModules['events']))
                <a href="{{ route('events.index') }}" class="nav-item">
                    <i class="fa-solid fa-calendar-check"></i><span>Events</span>
                </a>
                @endif
                
                @if(isset($accessibleModules['perks']))
                <a href="{{ route('perks.index') }}" class="nav-item">
                    <i class="fa-solid fa-gift"></i><span>Perks & Discounts</span>
                </a>
                @endif
                
                @if(isset($accessibleModules['tracer']))
                <a href="/admin/alumni_tracer" class="nav-item">
                    <i class="fa-solid fa-location-dot"></i><span>Alumni Tracer</span>
                </a>
                @endif
                
                @if(isset($accessibleModules['messages']))
                <a href="/admin/messages" class="nav-item">
                    <i class="fa-solid fa-envelope"></i><span>Messages</span>
                </a>
                @endif
                
                @if(isset($accessibleModules['settings']))
                <a href="{{ route('admin.settings') }}" class="nav-item">
                    <i class="fa-solid fa-gear"></i><span>Settings</span>
                </a>
                @endif
            </nav>
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
                            Admin Control Center
                        </h1>
                        <p class="page-subtitle">Overview of alumni engagement and platform activity</p>
                    </div>
                </div>
            </header>

            <!-- TAB NAVIGATION -->
            <div class="tab-navigation">
                <button class="tab-btn active" onclick="showTab('moderation')">
                    <i class="fa-solid fa-flag"></i>
                    <span>Moderation</span>
                    @if($totalReports > 0)
                        <span class="badge-notification">{{ $totalReports }}</span>
                    @endif
                </button>
                <button class="tab-btn" onclick="showTab('users')">
                    <i class="fa-solid fa-users-gear"></i>
                    <span>User Analytics</span>
                </button>
                <button class="tab-btn" onclick="showTab('tracer')">
                    <i class="fa-solid fa-file-lines"></i>
                    <span>Tracer Analytics</span>
                </button>
                <button class="tab-btn" onclick="showTab('events')">
                    <i class="fa-solid fa-calendar-check"></i>
                    <span>Event Analytics</span>
                </button>
            </div>

            <!-- ============================================ -->
            <!-- TAB 1: MODERATION QUEUE (Action Tab) -->
            <!-- ============================================ -->
            <div id="tab-moderation" class="tab-content active">
                <section class="dashboard-section">
                    <div class="section-header">
                        <h2 class="section-title">
                            <i class="fa-solid fa-flag"></i>
                            Moderation Queue
                        </h2>
                        <div class="section-actions">
                            <span class="report-count-badge">
                                <i class="fa-solid fa-circle-exclamation"></i>
                                {{ $totalReports }} pending report{{ $totalReports !== 1 ? 's' : '' }}
                            </span>
                        </div>
                    </div>

                    <!-- Moderation Stats -->
                    <div class="moderation-stats-grid">
                        <div class="mod-stat-card">
                            <div class="mod-stat-icon reported-posts">
                                <i class="fa-solid fa-file-lines"></i>
                            </div>
                            <div class="mod-stat-info">
                                <span class="mod-stat-value">{{ $reportedPosts->count() }}</span>
                                <span class="mod-stat-label">Reported Posts</span>
                            </div>
                        </div>
                        <div class="mod-stat-card">
                            <div class="mod-stat-icon reported-comments">
                                <i class="fa-solid fa-comment"></i>
                            </div>
                            <div class="mod-stat-info">
                                <span class="mod-stat-value">{{ $reportedComments->count() }}</span>
                                <span class="mod-stat-label">Reported Comments</span>
                            </div>
                        </div>
                        <div class="mod-stat-card">
                            <div class="mod-stat-icon total-reports">
                                <i class="fa-solid fa-flag"></i>
                            </div>
                            <div class="mod-stat-info">
                                <span class="mod-stat-value">{{ $totalReports }}</span>
                                <span class="mod-stat-label">Total Reports</span>
                            </div>
                        </div>
                    </div>

                    <!-- Frequent Violators Section -->
                    <div class="dash-card mt-20">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fa-solid fa-triangle-exclamation" style="color: #D97706;"></i>
                                Frequent Violators
                                <span class="badge-count" style="background: #FEF3C7; color: #D97706;">{{ $frequentViolators->count() }}</span>
                            </h3>
                            <div class="card-actions">
                                <button class="btn-expand" onclick="openModal('violators')">
                                    <i class="fa-solid fa-expand"></i>
                                    <span>Expand All</span>
                                </button>
                            </div>
                        </div>
                        <div class="list-wrapper">
                            @forelse($frequentViolators as $violator)
                            <div class="violator-item" onclick="openModal('violator', {{ $violator->id }})" style="cursor: pointer;">
                                <div class="violator-avatar">
                                    @if($violator->alumni_photo)
                                        <img src="{{ $violator->alumni_photo }}" alt="{{ $violator->first_name }}" class="violator-photo">
                                    @else
                                        <div class="violator-initials">{{ strtoupper(substr($violator->first_name, 0, 1) . substr($violator->last_name, 0, 1)) }}</div>
                                    @endif
                                </div>
                                <div class="violator-info">
                                    <div class="violator-name">
                                        {{ $violator->first_name }} {{ $violator->last_name }}
                                        <span class="violator-badge" style="background: {{ $violator->total_reports >= 5 ? '#EF4444' : ($violator->total_reports >= 3 ? '#F59E0B' : '#3B82F6') }}; color: white;">
                                            {{ $violator->total_reports }} report{{ $violator->total_reports > 1 ? 's' : '' }}
                                        </span>
                                    </div>
                                    <div class="violator-details">
                                        <span class="violator-meta">
                                            <i class="fa-regular fa-envelope"></i>
                                            {{ $violator->email }}
                                        </span>
                                        <span class="violator-meta">
                                            <i class="fa-regular fa-calendar"></i>
                                            Joined {{ optional($violator->created_at)->format('M Y') ?? 'N/A' }}
                                        </span>
                                        <span class="violator-meta">
                                            <i class="fa-solid fa-flag"></i>
                                            {{ $violator->post_reports }} post{{ $violator->post_reports > 1 ? 's' : '' }},
                                            {{ $violator->comment_reports }} comment{{ $violator->comment_reports > 1 ? 's' : '' }}
                                        </span>
                                    </div>
                                    @if($violator->report_reasons)
                                    <div class="violator-reasons">
                                        <i class="fa-solid fa-comment"></i>
                                        <span>Report reasons: {{ Str::limit($violator->report_reasons, 100) }}</span>
                                    </div>
                                    @endif
                                </div>
                                <div class="violator-actions">
                                    <button class="btn-action btn-expand-item" onclick="event.stopPropagation(); openModal('violator', {{ $violator->id }})" title="View Details">
                                        <i class="fa-solid fa-expand"></i>
                                    </button>
                                    <a href="{{ route('admin.alumni.show', $violator->id) }}" class="btn-action btn-view" title="View Profile" onclick="event.stopPropagation();">
                                        <i class="fa-solid fa-eye"></i>
                                    </a>
                                    @if($violator->account_status == 1)
                                    <button class="btn-action btn-restrict" onclick="event.stopPropagation(); restrictUser({{ $violator->id }})" title="Restrict User">
                                        <i class="fa-solid fa-user-slash"></i>
                                    </button>
                                    @else
                                    <button class="btn-action btn-unrestrict" onclick="event.stopPropagation(); restrictUser({{ $violator->id }})" title="Unrestrict User" style="background: #D1FAE5; color: #065F46;">
                                        <i class="fa-solid fa-user-check"></i>
                                    </button>
                                    @endif
                                    <a href="/admin/messages?chat={{ $violator->id }}" class="btn-action btn-message" title="Send Message" onclick="event.stopPropagation();">
                                        <i class="fa-solid fa-comment-dots"></i>
                                    </a>
                                </div>
                            </div>
                            @empty
                            <div class="empty-state">
                                <i class="fa-solid fa-check-circle" style="color: #10B981; font-size: 2.5rem;"></i>
                                <p style="font-weight: 500; color: var(--gray-600);">No frequent violators!</p>
                                <p style="font-size: 0.875rem; color: var(--gray-400);">All alumni are following community standards.</p>
                            </div>
                            @endforelse
                        </div>
                    </div>

                    <!-- Reported Posts Section -->
                    <div class="dash-card mt-20">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fa-solid fa-file-lines"></i>
                                Reported Posts
                                <span class="badge-count">{{ $reportedPosts->count() }}</span>
                            </h3>
                            <div class="card-actions">
                                <button class="btn-expand" onclick="openModal('posts')">
                                    <i class="fa-solid fa-expand"></i>
                                    <span>Expand All</span>
                                </button>
                            </div>
                        </div>
                        <div class="list-wrapper">
                            @forelse($reportedPosts as $post)
                            <div class="moderation-item priority-{{ $post->report_count >= 3 ? 'high' : ($post->report_count >= 2 ? 'medium' : 'low') }}" onclick="openModal('post', {{ $post->id }})" style="cursor: pointer;">
                                <div class="mod-item-left">
                                    <div class="priority-indicator">
                                        <span class="priority-dot priority-{{ $post->report_count >= 3 ? 'high' : ($post->report_count >= 2 ? 'medium' : 'low') }}"></span>
                                        <span class="report-count-badge-small">{{ $post->report_count }} report{{ $post->report_count > 1 ? 's' : '' }}</span>
                                    </div>
                                    <div class="mod-item-content">
                                        <p class="mod-item-text">{{ Str::limit($post->caption ?? 'No caption', 100) }}</p>
                                        <div class="mod-item-meta">
                                            <span class="mod-meta-author">
                                                <i class="fa-solid fa-user"></i>
                                                {{ $post->alumni ? $post->alumni->first_name . ' ' . $post->alumni->last_name : 'Unknown' }}
                                            </span>
                                            <span class="mod-meta-date">
                                                <i class="fa-regular fa-clock"></i>
                                                {{ $post->created_at ? $post->created_at->format('M d, Y') : 'Date not available' }}
                                            </span>
                                            @if($post->report_reasons)
                                            <span class="mod-meta-reasons" title="{{ $post->report_reasons }}">
                                                <i class="fa-solid fa-comment"></i>
                                                {{ Str::limit($post->report_reasons, 40) }}
                                            </span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                <div class="mod-item-actions">
                                    <button class="btn-action btn-expand-item" onclick="event.stopPropagation(); openModal('post', {{ $post->id }})" title="View Details">
                                        <i class="fa-solid fa-expand"></i>
                                    </button>
                                    <button class="btn-action view-post" onclick="event.stopPropagation(); viewPost({{ $post->id }})" title="View Post">
                                        <i class="fa-solid fa-eye"></i>
                                    </button>
                                    <button class="btn-action approve-post" onclick="event.stopPropagation(); moderatePost({{ $post->id }}, 'approve')" title="Approve (Dismiss Reports)">
                                        <i class="fa-solid fa-check"></i>
                                    </button>
                                    <button class="btn-action hide-post" onclick="event.stopPropagation(); moderatePost({{ $post->id }}, 'hide')" title="Hide Post">
                                        <i class="fa-solid fa-eye-slash"></i>
                                    </button>
                                    <button class="btn-action delete-post" onclick="event.stopPropagation(); moderatePost({{ $post->id }}, 'delete')" title="Delete Post">
                                        <i class="fa-solid fa-trash"></i>
                                    </button>
                                    <button class="btn-action restrict-user" onclick="event.stopPropagation(); restrictUser({{ $post->alumni_id }})" title="Restrict User">
                                        <i class="fa-solid fa-user-slash"></i>
                                    </button>
                                </div>
                            </div>
                            @empty
                            <div class="empty-state">
                                <i class="fa-solid fa-check-circle" style="color: #10B981;"></i>
                                <p>No reported posts! All posts are clean.</p>
                            </div>
                            @endforelse
                        </div>
                    </div>

                    <!-- Reported Comments Section -->
                    <div class="dash-card mt-20">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fa-solid fa-comments"></i>
                                Reported Comments
                                <span class="badge-count">{{ $reportedComments->count() }}</span>
                            </h3>
                            <div class="card-actions">
                                <button class="btn-expand" onclick="openModal('comments')">
                                    <i class="fa-solid fa-expand"></i>
                                    <span>Expand All</span>
                                </button>
                            </div>
                        </div>
                        <div class="list-wrapper">
                            @forelse($reportedComments as $comment)
                            <div class="moderation-item priority-{{ $comment->report_count >= 3 ? 'high' : ($comment->report_count >= 2 ? 'medium' : 'low') }}" onclick="openModal('comment', {{ $comment->id }})" style="cursor: pointer;">
                                <div class="mod-item-left">
                                    <div class="priority-indicator">
                                        <span class="priority-dot priority-{{ $comment->report_count >= 3 ? 'high' : ($comment->report_count >= 2 ? 'medium' : 'low') }}"></span>
                                        <span class="report-count-badge-small">{{ $comment->report_count }} report{{ $comment->report_count > 1 ? 's' : '' }}</span>
                                    </div>
                                    <div class="mod-item-content">
                                        <p class="mod-item-text">"{{ Str::limit($comment->comment ?? 'Comment deleted', 100) }}"</p>
                                        <div class="mod-item-meta">
                                            <span class="mod-meta-author">
                                                <i class="fa-solid fa-user"></i>
                                                {{ $comment->alumni ? $comment->alumni->first_name . ' ' . $comment->alumni->last_name : 'Unknown User' }}
                                            </span>
                                            <span class="mod-meta-date">
                                                <i class="fa-regular fa-clock"></i>
                                                {{ $comment->created_at ? $comment->created_at->format('M d, Y') : 'Date not available' }}
                                            </span>
                                            @if($comment->post_caption)
                                            <span class="mod-meta-post">
                                                <i class="fa-solid fa-file-lines"></i>
                                                {{ Str::limit($comment->post_caption, 30) }}
                                            </span>
                                            @endif
                                            @if($comment->report_reasons)
                                            <span class="mod-meta-reasons" title="{{ $comment->report_reasons }}">
                                                <i class="fa-solid fa-comment"></i>
                                                {{ Str::limit($comment->report_reasons, 40) }}
                                            </span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                <div class="mod-item-actions">
                                    <button class="btn-action btn-expand-item" onclick="event.stopPropagation(); openModal('comment', {{ $comment->id }})" title="View Details">
                                        <i class="fa-solid fa-expand"></i>
                                    </button>
                                    <button class="btn-action view-comment" onclick="event.stopPropagation(); viewComment({{ $comment->id }})" title="View Comment">
                                        <i class="fa-solid fa-eye"></i>
                                    </button>
                                    <button class="btn-action approve-comment" onclick="event.stopPropagation(); moderateComment({{ $comment->id }}, 'approve')" title="Approve (Dismiss Reports)">
                                        <i class="fa-solid fa-check"></i>
                                    </button>
                                    <button class="btn-action delete-comment" onclick="event.stopPropagation(); moderateComment({{ $comment->id }}, 'delete')" title="Delete Comment">
                                        <i class="fa-solid fa-trash"></i>
                                    </button>
                                    <button class="btn-action restrict-user" onclick="event.stopPropagation(); restrictUser({{ $comment->alumni_id }})" title="Restrict User">
                                        <i class="fa-solid fa-user-slash"></i>
                                    </button>
                                </div>
                            </div>
                            @empty
                            <div class="empty-state">
                                <i class="fa-solid fa-check-circle" style="color: #10B981;"></i>
                                <p>No reported comments! All comments are clean.</p>
                            </div>
                            @endforelse
                        </div>
                    </div>
                </section>
            </div>

            <!-- ============================================ -->
            <!-- TAB 2: USER ANALYTICS -->
            <!-- ============================================ -->
            <div id="tab-users" class="tab-content">
                <section class="dashboard-section">
                    <div class="section-header">
                        <h2 class="section-title">
                            <i class="fa-solid fa-users-gear"></i>
                            User Analytics
                        </h2>
                        <div class="section-actions">
                            <button class="btn btn-sm btn-export" onclick="exportData('users', 'pdf')">
                                <i class="fa-solid fa-file-pdf"></i> PDF
                            </button>
                            <button class="btn btn-sm btn-export" onclick="exportData('users', 'excel')">
                                <i class="fa-solid fa-file-excel"></i> Excel
                            </button>
                        </div>
                    </div>

                     <!-- ========== NEW: ALUMNI LOCATION MAP ========== -->
                    <div class="dash-card mb-20">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fa-solid fa-map-location-dot"></i>
                                Alumni Location Map
                            </h3>
                            <div class="card-actions">
                                <span class="badge-count">{{ isset($alumniLocations) ? $alumniLocations->count() : 0 }} Alumni Located</span>
                            </div>
                        </div>
                        <div id="alumniMap" style="height: 400px; width: 100%; border-radius: var(--radius-lg);"></div>
                        <div style="margin-top: 0.75rem; font-size: 0.8rem; color: var(--gray-500); text-align: center;">
                            <i class="fa-solid fa-info-circle"></i> 
                            Hover over markers to see alumni names. Click for more details.
                        </div>
                    </div>

                    <!-- User Stats -->
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
                                <div class="stat-icon pending">
                                    <i class="fa-solid fa-hourglass-half"></i>
                                </div>
                            </div>
                            <div class="stat-info">
                                <span class="stat-value">{{ number_format($pendingVerificationCount) }}</span>
                                <span class="stat-label">Pending Verification</span>
                            </div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-icon-wrapper">
                                <div class="stat-icon active">
                                    <i class="fa-solid fa-user-plus"></i>
                                </div>
                            </div>
                            <div class="stat-info">
                                <span class="stat-value">{{ number_format($userGrowth->sum('count')) }}</span>
                                <span class="stat-label">New Users (12 months)</span>
                            </div>
                        </div>
                    </div>

                    <!-- User Growth Chart -->
                    <div class="chart-card mt-20">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fa-solid fa-chart-line"></i>
                                User Growth (Last 12 Months)
                            </h3>
                        </div>
                        <div class="chart-container-wrapper">
                            <div class="chart-container">
                                <canvas id="userGrowthChart"></canvas>
                            </div>
                        </div>
                    </div>

                    <div class="two-col-grid mt-20">
                        <!-- Alumni by Program -->
                        <div class="dash-card">
                            <div class="card-header">
                                <h3 class="card-title">
                                    <i class="fa-solid fa-graduation-cap"></i>
                                    Alumni by Program
                                </h3>
                            </div>
                            <div class="chart-container" style="height: 250px;">
                                <canvas id="programChart"></canvas>
                            </div>
                        </div>

                        <!-- Alumni by Year -->
                        <div class="dash-card">
                            <div class="card-header">
                                <h3 class="card-title">
                                    <i class="fa-solid fa-calendar"></i>
                                    Alumni by Year Graduated
                                </h3>
                            </div>
                            <div class="chart-container" style="height: 250px;">
                                <canvas id="yearChart"></canvas>
                            </div>
                        </div>
                    </div>

                    <div class="two-col-grid mt-20">
                        <!-- Alumni by Type -->
                        <div class="dash-card">
                            <div class="card-header">
                                <h3 class="card-title">
                                    <i class="fa-solid fa-layer-group"></i>
                                    Alumni by Type
                                </h3>
                            </div>
                            <div class="chart-container" style="height: 200px;">
                                <canvas id="typeChart"></canvas>
                            </div>
                        </div>

                        <!-- Alumni by Region -->
                        <div class="dash-card">
                            <div class="card-header">
                                <h3 class="card-title">
                                    <i class="fa-solid fa-location-dot"></i>
                                    Alumni by Region
                                </h3>
                            </div>
                            <div class="chart-container" style="height: 200px;">
                                <canvas id="regionChart"></canvas>
                            </div>
                        </div>
                    </div>
                </section>
            </div>

            <!-- ============================================ -->
            <!-- TAB 3: TRACER ANALYTICS -->
            <!-- ============================================ -->
            <div id="tab-tracer" class="tab-content">
                <section class="dashboard-section">
                    <div class="section-header">
                        <h2 class="section-title">
                            <i class="fa-solid fa-file-lines"></i>
                            Tracer Analytics
                        </h2>
                        <div class="section-actions">
                            <button class="btn btn-sm btn-export" onclick="exportData('tracer', 'pdf')">
                                <i class="fa-solid fa-file-pdf"></i> PDF
                            </button>
                            <button class="btn btn-sm btn-export" onclick="exportData('tracer', 'excel')">
                                <i class="fa-solid fa-file-excel"></i> Excel
                            </button>
                        </div>
                    </div>

                    <!-- Tracer Stats -->
                    <div class="stats-grid">
                        <div class="stat-card">
                            <div class="stat-icon-wrapper">
                                <div class="stat-icon views">
                                    <i class="fa-solid fa-file-pen"></i>
                                </div>
                            </div>
                            <div class="stat-info">
                                <span class="stat-value">{{ number_format($totalTracerResponses) }}</span>
                                <span class="stat-label">Total Responses</span>
                            </div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-icon-wrapper">
                                <div class="stat-icon success">
                                    <i class="fa-solid fa-check-circle"></i>
                                </div>
                            </div>
                            <div class="stat-info">
                                <span class="stat-value">{{ number_format($completedTracerResponses) }}</span>
                                <span class="stat-label">Completed</span>
                            </div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-icon-wrapper">
                                <div class="stat-icon warning">
                                    <i class="fa-solid fa-clock"></i>
                                </div>
                            </div>
                            <div class="stat-info">
                                <span class="stat-value">{{ number_format($inProgressTracerResponses) }}</span>
                                <span class="stat-label">In Progress</span>
                            </div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-icon-wrapper">
                                <div class="stat-icon info">
                                    <i class="fa-solid fa-percent"></i>
                                </div>
                            </div>
                            <div class="stat-info">
                                <span class="stat-value">{{ $tracerCompletionRate }}%</span>
                                <span class="stat-label">Completion Rate</span>
                            </div>
                        </div>
                    </div>

                    <div class="two-col-grid mt-20">
                        <!-- Tracer Responses Over Time -->
                        <div class="dash-card">
                            <div class="card-header">
                                <h3 class="card-title">
                                    <i class="fa-solid fa-chart-line"></i>
                                    Responses Over Time
                                </h3>
                            </div>
                            <div class="chart-container" style="height: 250px;">
                                <canvas id="tracerOverTimeChart"></canvas>
                            </div>
                        </div>

                        <!-- Tracer by Form -->
                        <div class="dash-card">
                            <div class="card-header">
                                <h3 class="card-title">
                                    <i class="fa-solid fa-list-ul"></i>
                                    Responses by Form
                                </h3>
                            </div>
                            <div class="chart-container" style="height: 250px;">
                                <canvas id="tracerByFormChart"></canvas>
                            </div>
                        </div>
                    </div>
                </section>
            </div>

            <!-- ============================================ -->
            <!-- TAB 4: EVENT ANALYTICS -->
            <!-- ============================================ -->
            <div id="tab-events" class="tab-content">
                <section class="dashboard-section">
                    <div class="section-header">
                        <h2 class="section-title">
                            <i class="fa-solid fa-calendar-check"></i>
                            Event Analytics
                        </h2>
                        <div class="section-actions">
                            <button class="btn btn-sm btn-export" onclick="exportData('events', 'pdf')">
                                <i class="fa-solid fa-file-pdf"></i> PDF
                            </button>
                            <button class="btn btn-sm btn-export" onclick="exportData('events', 'excel')">
                                <i class="fa-solid fa-file-excel"></i> Excel
                            </button>
                        </div>
                    </div>

                    <!-- Event Stats -->
                    <div class="stats-grid">
                        <div class="stat-card">
                            <div class="stat-icon-wrapper">
                                <div class="stat-icon total">
                                    <i class="fa-solid fa-calendar"></i>
                                </div>
                            </div>
                            <div class="stat-info">
                                <span class="stat-value">{{ number_format($totalEvents) }}</span>
                                <span class="stat-label">Total Events</span>
                            </div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-icon-wrapper">
                                <div class="stat-icon active">
                                    <i class="fa-solid fa-calendar-check"></i>
                                </div>
                            </div>
                            <div class="stat-info">
                                <span class="stat-value">{{ number_format($activeEvents) }}</span>
                                <span class="stat-label">Active Events</span>
                            </div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-icon-wrapper">
                                <div class="stat-icon upcoming">
                                    <i class="fa-solid fa-calendar-plus"></i>
                                </div>
                            </div>
                            <div class="stat-info">
                                <span class="stat-value">{{ number_format($upcomingEvents) }}</span>
                                <span class="stat-label">Upcoming Events</span>
                            </div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-icon-wrapper">
                                <div class="stat-icon registrations">
                                    <i class="fa-solid fa-user-plus"></i>
                                </div>
                            </div>
                            <div class="stat-info">
                                <span class="stat-value">{{ number_format($totalRegistrations) }}</span>
                                <span class="stat-label">Total Registrations</span>
                            </div>
                        </div>
                    </div>

                    <div class="two-col-grid mt-20">
                        <!-- Registrations Over Time -->
                        <div class="dash-card">
                            <div class="card-header">
                                <h3 class="card-title">
                                    <i class="fa-solid fa-chart-line"></i>
                                    Registrations Over Time
                                </h3>
                            </div>
                            <div class="chart-container" style="height: 250px;">
                                <canvas id="registrationsOverTimeChart"></canvas>
                            </div>
                        </div>

                        <!-- Top Events -->
                        <div class="dash-card">
                            <div class="card-header">
                                <h3 class="card-title">
                                    <i class="fa-solid fa-trophy"></i>
                                    Top Events
                                </h3>
                            </div>
                            <div class="chart-container" style="height: 250px;">
                                <canvas id="topEventsChart"></canvas>
                            </div>
                        </div>
                    </div>
                </section>
            </div>
        </main>
    </div>

     <!-- ============================================ -->
            <!-- MODALS -->
            <!-- ============================================ -->

            <!-- Violator Detail Modal -->
            <div id="violatorModal" class="modal-overlay" onclick="if(event.target === this) closeModal('violatorModal')">
                <div class="modal-content-wrapper" style="max-width: 800px;">
                    <div class="modal-card">
                        <div class="modal-header">
                            <div>
                                <h2 class="modal-title" id="violatorModalTitle">
                                    <i class="fa-solid fa-user"></i>
                                    Violator Details
                                </h2>
                                <p class="modal-subtitle" id="violatorModalSubtitle">Full report details for this alumni</p>
                            </div>
                            <button class="modal-close" onclick="closeModal('violatorModal')">
                                <i class="fa-solid fa-xmark"></i>
                            </button>
                        </div>
                        <div class="modal-body" id="violatorModalBody">
                            <div class="modal-loading">
                                <i class="fa-solid fa-spinner fa-spin"></i>
                                <p>Loading details...</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Post Detail Modal -->
            <div id="postModal" class="modal-overlay" onclick="if(event.target === this) closeModal('postModal')">
                <div class="modal-content-wrapper" style="max-width: 800px;">
                    <div class="modal-card">
                        <div class="modal-header">
                            <div>
                                <h2 class="modal-title" id="postModalTitle">
                                    <i class="fa-solid fa-file-lines"></i>
                                    Post Report Details
                                </h2>
                                <p class="modal-subtitle" id="postModalSubtitle">Full details of reported post</p>
                            </div>
                            <button class="modal-close" onclick="closeModal('postModal')">
                                <i class="fa-solid fa-xmark"></i>
                            </button>
                        </div>
                        <div class="modal-body" id="postModalBody">
                            <div class="modal-loading">
                                <i class="fa-solid fa-spinner fa-spin"></i>
                                <p>Loading details...</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Comment Detail Modal -->
            <div id="commentModal" class="modal-overlay" onclick="if(event.target === this) closeModal('commentModal')">
                <div class="modal-content-wrapper" style="max-width: 800px;">
                    <div class="modal-card">
                        <div class="modal-header">
                            <div>
                                <h2 class="modal-title" id="commentModalTitle">
                                    <i class="fa-solid fa-comment"></i>
                                    Comment Report Details
                                </h2>
                                <p class="modal-subtitle" id="commentModalSubtitle">Full details of reported comment</p>
                            </div>
                            <button class="modal-close" onclick="closeModal('commentModal')">
                                <i class="fa-solid fa-xmark"></i>
                            </button>
                        </div>
                        <div class="modal-body" id="commentModalBody">
                            <div class="modal-loading">
                                <i class="fa-solid fa-spinner fa-spin"></i>
                                <p>Loading details...</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Post Interactions Modal -->
            <div id="interactionsModal" class="interactions-modal" style="display: none;">
                <div class="interactions-modal-overlay" onclick="closeInteractionsModal()"></div>
                <div class="interactions-modal-content">
                    <div class="interactions-modal-header">
                        <div class="interactions-modal-title">
                            <i class="fa-regular fa-heart"></i>
                            <span id="interactionsModalTitle">Interactions</span>
                            <span class="interactions-count" id="interactionsCount">(0)</span>
                        </div>
                        <button class="interactions-modal-close" onclick="closeInteractionsModal()">
                            <i class="fa-solid fa-xmark"></i>
                        </button>
                    </div>
                    <div class="interactions-modal-body">
                        <!-- Tabs -->
                        <div class="interactions-tabs">
                            <button class="interactions-tab active" data-tab="likes" onclick="switchInteractionTab('likes')">
                                <i class="fa-regular fa-heart"></i> Likes
                            </button>
                            <button class="interactions-tab" data-tab="comments" onclick="switchInteractionTab('comments')">
                                <i class="fa-regular fa-comment"></i> Comments
                            </button>
                            <button class="interactions-tab" data-tab="reposts" onclick="switchInteractionTab('reposts')">
                                <i class="fa-solid fa-retweet"></i> Reposts
                            </button>
                        </div>
                        
                        <!-- Content Panels -->
                        <div class="interactions-panels">
                            <!-- Likes Panel -->
                            <div class="interactions-panel active" id="likesPanel">
                                <div class="interactions-list" id="likesList">
                                    <div class="interactions-loading">
                                        <i class="fa-solid fa-spinner fa-spin"></i> Loading likes...
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Comments Panel -->
                            <div class="interactions-panel" id="commentsPanel">
                                <div class="interactions-list" id="commentsList">
                                    <div class="interactions-loading">
                                        <i class="fa-solid fa-spinner fa-spin"></i> Loading comments...
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Reposts Panel -->
                            <div class="interactions-panel" id="repostsPanel">
                                <div class="interactions-list" id="repostsList">
                                    <div class="interactions-loading">
                                        <i class="fa-solid fa-spinner fa-spin"></i> Loading reposts...
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

    <!-- ============================================ -->
    <!-- JAVASCRIPT -->
    <!-- ============================================ -->
    <script>
    // ========== MOBILE MENU TOGGLE ==========
    function toggleMobileMenu() {
        const sidebar = document.getElementById('adminSidebar');
        const overlay = document.getElementById('mobileOverlay');
        sidebar.classList.toggle('mobile-open');
        overlay.classList.toggle('active');
        document.body.style.overflow = sidebar.classList.contains('mobile-open') ? 'hidden' : '';
    }

    document.querySelectorAll('.nav-item').forEach(item => {
        item.addEventListener('click', function() {
            if (window.innerWidth <= 1024) {
                toggleMobileMenu();
            }
        });
    });


    // ========== CHART DATA ==========
    const chartData = @json($chartData);

    // ========== MODERATION DATA ==========
    const moderationData = {
        frequentViolators: @json($frequentViolators),
        reportedPosts: @json($reportedPosts),
        reportedComments: @json($reportedComments)
    };

    // ========== INITIALIZE CHARTS ==========
    let charts = {};

    function initCharts() {
        // User Growth Chart
        const userGrowthCtx = document.getElementById('userGrowthChart');
        if (userGrowthCtx && chartData.user_growth.labels.length > 0) {
            charts.userGrowth = new Chart(userGrowthCtx, {
                type: 'line',
                data: {
                    labels: chartData.user_growth.labels,
                    datasets: [{
                        label: 'New Alumni',
                        data: chartData.user_growth.data,
                        borderColor: '#32418C',
                        backgroundColor: 'rgba(50, 65, 140, 0.1)',
                        fill: true,
                        tension: 0.4,
                        pointBackgroundColor: '#32418C',
                        pointBorderColor: '#fff',
                        pointBorderWidth: 2,
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false }
                    },
                    scales: {
                        y: { beginAtZero: true, ticks: { stepSize: 1 } }
                    }
                }
            });
        }

        // Program Chart
        const programCtx = document.getElementById('programChart');
        if (programCtx && chartData.alumni_by_program.labels.length > 0) {
            charts.program = new Chart(programCtx, {
                type: 'bar',
                data: {
                    labels: chartData.alumni_by_program.labels,
                    datasets: [{
                        label: 'Alumni',
                        data: chartData.alumni_by_program.data,
                        backgroundColor: 'rgba(50, 65, 140, 0.7)',
                        borderColor: '#32418C',
                        borderWidth: 1,
                        borderRadius: 4,
                    }]
                },
                options: {
                    indexAxis: 'y',
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: {
                        x: { beginAtZero: true, ticks: { stepSize: 1 } }
                    }
                }
            });
        }

        // Year Chart
        const yearCtx = document.getElementById('yearChart');
        if (yearCtx && chartData.alumni_by_year.labels.length > 0) {
            charts.year = new Chart(yearCtx, {
                type: 'bar',
                data: {
                    labels: chartData.alumni_by_year.labels,
                    datasets: [{
                        label: 'Alumni',
                        data: chartData.alumni_by_year.data,
                        backgroundColor: 'rgba(251, 209, 23, 0.7)',
                        borderColor: '#FBD117',
                        borderWidth: 1,
                        borderRadius: 4,
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: {
                        y: { beginAtZero: true, ticks: { stepSize: 1 } }
                    }
                }
            });
        }

        // Type Chart
        const typeCtx = document.getElementById('typeChart');
        if (typeCtx && chartData.alumni_by_type.labels.length > 0) {
            const colors = ['#32418C', '#FBD117'];
            charts.type = new Chart(typeCtx, {
                type: 'doughnut',
                data: {
                    labels: chartData.alumni_by_type.labels.map(l => l === 'shs' ? 'SHS' : 'College'),
                    datasets: [{
                        data: chartData.alumni_by_type.data,
                        backgroundColor: colors,
                        borderWidth: 2,
                        borderColor: '#fff',
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: { font: { size: 11 } }
                        }
                    }
                }
            });
        }

        // Region Chart
        const regionCtx = document.getElementById('regionChart');
        if (regionCtx && chartData.alumni_by_region.labels.length > 0) {
            charts.region = new Chart(regionCtx, {
                type: 'bar',
                data: {
                    labels: chartData.alumni_by_region.labels,
                    datasets: [{
                        label: 'Alumni',
                        data: chartData.alumni_by_region.data,
                        backgroundColor: 'rgba(16, 185, 129, 0.7)',
                        borderColor: '#10B981',
                        borderWidth: 1,
                        borderRadius: 4,
                    }]
                },
                options: {
                    indexAxis: 'y',
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: {
                        x: { beginAtZero: true, ticks: { stepSize: 1 } }
                    }
                }
            });
        }

        // Tracer Over Time Chart
        const tracerOverTimeCtx = document.getElementById('tracerOverTimeChart');
        if (tracerOverTimeCtx && chartData.tracer_over_time.labels.length > 0) {
            charts.tracerOverTime = new Chart(tracerOverTimeCtx, {
                type: 'line',
                data: {
                    labels: chartData.tracer_over_time.labels,
                    datasets: [{
                        label: 'Responses',
                        data: chartData.tracer_over_time.data,
                        borderColor: '#3B82F6',
                        backgroundColor: 'rgba(59, 130, 246, 0.1)',
                        fill: true,
                        tension: 0.4,
                        pointBackgroundColor: '#3B82F6',
                        pointBorderColor: '#fff',
                        pointBorderWidth: 2,
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: {
                        y: { beginAtZero: true, ticks: { stepSize: 1 } }
                    }
                }
            });
        }

        // Tracer By Form Chart
        const tracerByFormCtx = document.getElementById('tracerByFormChart');
        if (tracerByFormCtx && chartData.tracer_by_form.labels.length > 0) {
            charts.tracerByForm = new Chart(tracerByFormCtx, {
                type: 'bar',
                data: {
                    labels: chartData.tracer_by_form.labels,
                    datasets: [{
                        label: 'Responses',
                        data: chartData.tracer_by_form.data,
                        backgroundColor: 'rgba(59, 130, 246, 0.7)',
                        borderColor: '#3B82F6',
                        borderWidth: 1,
                        borderRadius: 4,
                    }]
                },
                options: {
                    indexAxis: 'y',
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: {
                        x: { beginAtZero: true, ticks: { stepSize: 1 } }
                    }
                }
            });
        }

        // Registrations Over Time Chart
        const registrationsOverTimeCtx = document.getElementById('registrationsOverTimeChart');
        if (registrationsOverTimeCtx && chartData.registrations_over_time.labels.length > 0) {
            charts.registrationsOverTime = new Chart(registrationsOverTimeCtx, {
                type: 'line',
                data: {
                    labels: chartData.registrations_over_time.labels,
                    datasets: [{
                        label: 'Registrations',
                        data: chartData.registrations_over_time.data,
                        borderColor: '#10B981',
                        backgroundColor: 'rgba(16, 185, 129, 0.1)',
                        fill: true,
                        tension: 0.4,
                        pointBackgroundColor: '#10B981',
                        pointBorderColor: '#fff',
                        pointBorderWidth: 2,
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: {
                        y: { beginAtZero: true, ticks: { stepSize: 1 } }
                    }
                }
            });
        }

        // Top Events Chart
        const topEventsCtx = document.getElementById('topEventsChart');
        if (topEventsCtx && chartData.top_events.labels.length > 0) {
            charts.topEvents = new Chart(topEventsCtx, {
                type: 'bar',
                data: {
                    labels: chartData.top_events.labels,
                    datasets: [{
                        label: 'Registrations',
                        data: chartData.top_events.data,
                        backgroundColor: 'rgba(251, 209, 23, 0.7)',
                        borderColor: '#FBD117',
                        borderWidth: 1,
                        borderRadius: 4,
                    }]
                },
                options: {
                    indexAxis: 'y',
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: {
                        x: { beginAtZero: true, ticks: { stepSize: 1 } }
                    }
                }
            });
        }
    }

    // ========== EXPORT FUNCTIONS ==========
    function exportData(tab, format) {
        // Get data based on tab
        let data = [];
        let title = '';

        switch(tab) {
            case 'users':
                title = 'User Analytics Report';
                data = [
                    ['Metric', 'Value'],
                    ['Verified Alumni', '{{ $verifiedAlumniCount }}'],
                    ['Pending Verification', '{{ $pendingVerificationCount }}'],
                    ['New Users (12 months)', '{{ $userGrowth->sum('count') }}'],
                    ['', ''],
                    ['Program', 'Count'],
                    @foreach($alumniByProgram as $program)
                    ['{{ $program->program }}', '{{ $program->count }}'],
                    @endforeach
                    ['', ''],
                    ['Year', 'Count'],
                    @foreach($alumniByYear as $year)
                    ['{{ $year['year'] }}', '{{ $year['count'] }}'],
                    @endforeach
                ];
                break;

            case 'tracer':
                title = 'Tracer Analytics Report';
                data = [
                    ['Metric', 'Value'],
                    ['Total Responses', '{{ $totalTracerResponses }}'],
                    ['Completed', '{{ $completedTracerResponses }}'],
                    ['In Progress', '{{ $inProgressTracerResponses }}'],
                    ['Completion Rate', '{{ $tracerCompletionRate }}%'],
                    ['', ''],
                    ['Form', 'Responses'],
                    @foreach($tracerByForm as $form)
                    ['{{ $form->form_title }}', '{{ $form->response_count }}'],
                    @endforeach
                ];
                break;

            case 'events':
                title = 'Event Analytics Report';
                data = [
                    ['Metric', 'Value'],
                    ['Total Events', '{{ $totalEvents }}'],
                    ['Active Events', '{{ $activeEvents }}'],
                    ['Upcoming Events', '{{ $upcomingEvents }}'],
                    ['Total Registrations', '{{ $totalRegistrations }}'],
                    ['', ''],
                    ['Event', 'Registrations'],
                    @foreach($topEvents as $event)
                    ['{{ $event->title }}', '{{ $event->registration_count }}'],
                    @endforeach
                ];
                break;
        }

        if (format === 'pdf') {
            exportPDF(data, title);
        } else {
            exportExcel(data, title);
        }
    }

    function exportPDF(data, title) {
        const { jsPDF } = window.jspdf;
        const doc = new jsPDF('p', 'mm', 'a4');
        
        doc.setFontSize(16);
        doc.text(title, 14, 22);
        doc.setFontSize(10);
        doc.text('Generated: ' + new Date().toLocaleString(), 14, 30);
        
        doc.autoTable({
            head: [data[0]],
            body: data.slice(1),
            startY: 38,
            styles: { fontSize: 9 },
            headStyles: { fillColor: [50, 65, 140] },
            margin: { left: 14, right: 14 },
        });
        
        doc.save(title + '.pdf');
    }

    function exportExcel(data, title) {
        const wb = XLSX.utils.book_new();
        const ws = XLSX.utils.aoa_to_sheet(data);
        XLSX.utils.book_append_sheet(wb, ws, 'Report');
        XLSX.writeFile(wb, title + '.xlsx');
    }

    // ========== MODERATION ACTIONS ==========
    function moderatePost(postId, action) {
        if (!confirm('Are you sure you want to ' + action + ' this post?')) return;
        
        fetch('/admin/moderate/post', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({ id: postId, action: action })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            alert('An error occurred. Please try again.');
        });
    }

    function moderateComment(commentId, action) {
        if (!confirm('Are you sure you want to ' + action + ' this comment?')) return;
        
        fetch('/admin/moderate/comment', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({ id: commentId, action: action })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            alert('An error occurred. Please try again.');
        });
    }

function restrictUser(alumniId) {
    // Check if the user is already restricted
    const userRow = document.querySelector(`.violator-item[data-id="${alumniId}"]`) || 
                    document.querySelector(`.moderation-item[data-id="${alumniId}"]`);
    
    const isRestricted = userRow?.dataset?.status === '0';
    
    if (isRestricted) {
        if (!confirm('This user is currently restricted. Do you want to unrestrict them?')) return;
        
        fetch('/admin/restrict-user', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({ 
                alumni_id: alumniId, 
                restrict: 0 
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            alert('An error occurred. Please try again.');
        });
        return;
    }
    
    // Show restriction reason modal with new reasons
    const reasonOptions = [
        'Spam or Fraud',
        'Nudity or Sexual Content', 
        'Hate Speech or Symbols',
        'Violence or Dangerous Organizations',
        'Bullying or Harassment',
        'Sale of Illegal or Regulated Goods',
        'Intellectual Property Violation',
        'Other'
    ];
    
    let reasonHtml = reasonOptions.map(r => 
        `<option value="${r.toLowerCase().replace(/ /g, '_')}">${r}</option>`
    ).join('');
    
    const reasonModal = `
        <div id="restrictReasonModal" class="modal-overlay active" style="display:flex;">
            <div class="modal-content-wrapper" style="max-width: 500px;">
                <div class="modal-card">
                    <div class="modal-header">
                        <div>
                            <h2 class="modal-title">
                                <i class="fa-solid fa-user-slash"></i>
                                Restrict User
                            </h2>
                            <p class="modal-subtitle">Select a reason for restricting this user</p>
                        </div>
                        <button class="modal-close" onclick="closeRestrictModal()">
                            <i class="fa-solid fa-xmark"></i>
                        </button>
                    </div>
                    <div class="modal-body">
                        <form id="restrictForm" onsubmit="submitRestrict(event, ${alumniId})">
                            <div class="form-group" style="margin-bottom: 1rem;">
                                <label for="restrictionReason" style="display: block; font-weight: 600; margin-bottom: 0.5rem; font-size: 0.875rem;">
                                    Reason for Restriction <span style="color: var(--danger);">*</span>
                                </label>
                                <select id="restrictionReason" required style="width: 100%; padding: 0.75rem; border: 2px solid var(--gray-200); border-radius: var(--radius-lg); font-size: 0.9375rem; font-family: inherit;">
                                    <option value="">Select a reason...</option>
                                    ${reasonHtml}
                                </select>
                            </div>
                            <div class="form-group" id="customReasonGroup" style="display: none; margin-bottom: 1rem;">
                                <label for="customReason" style="display: block; font-weight: 600; margin-bottom: 0.5rem; font-size: 0.875rem;">
                                    Please specify:
                                </label>
                                <input type="text" id="customReason" placeholder="Enter custom reason..." style="width: 100%; padding: 0.75rem; border: 2px solid var(--gray-200); border-radius: var(--radius-lg); font-size: 0.9375rem; font-family: inherit;">
                            </div>
                            <div class="form-group" style="margin-bottom: 1rem;">
                                <label for="restrictionComment" style="display: block; font-weight: 600; margin-bottom: 0.5rem; font-size: 0.875rem;">
                                    Additional Comments <span style="font-weight: 400; color: var(--gray-500);">(Optional)</span>
                                </label>
                                <textarea id="restrictionComment" rows="3" placeholder="Add any additional notes..." style="width: 100%; padding: 0.75rem; border: 2px solid var(--gray-200); border-radius: var(--radius-lg); font-size: 0.9375rem; font-family: inherit; resize: vertical; min-height: 80px;"></textarea>
                            </div>
                            <div style="display: flex; gap: 0.5rem; justify-content: flex-end; margin-top: 1rem; padding-top: 1rem; border-top: 2px solid var(--gray-100);">
                                <button type="button" class="btn btn-secondary" onclick="closeRestrictModal()">Cancel</button>
                                <button type="submit" class="btn btn-warning">
                                    <i class="fa-solid fa-lock"></i> Restrict User
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    // Remove existing modal if present
    const existingModal = document.getElementById('restrictReasonModal');
    if (existingModal) existingModal.remove();
    
    // Add modal to body
    document.body.insertAdjacentHTML('beforeend', reasonModal);
    document.body.style.overflow = 'hidden';
    
    // Handle "Other" selection
    document.getElementById('restrictionReason').addEventListener('change', function() {
        const customGroup = document.getElementById('customReasonGroup');
        if (this.value === 'other') {
            customGroup.style.display = 'block';
        } else {
            customGroup.style.display = 'none';
        }
    });
}

function closeRestrictModal() {
    const modal = document.getElementById('restrictReasonModal');
    if (modal) {
        modal.remove();
        document.body.style.overflow = '';
    }
}

function submitRestrict(event, alumniId) {
    event.preventDefault();
    
    const reason = document.getElementById('restrictionReason').value;
    const customReason = document.getElementById('customReason')?.value || '';
    const comment = document.getElementById('restrictionComment')?.value || '';
    
    if (!reason) {
        alert('Please select a reason for restriction.');
        return;
    }
    
    const finalReason = reason === 'other' ? customReason : reason;
    
    if (reason === 'other' && !customReason.trim()) {
        alert('Please specify a custom reason.');
        return;
    }
    
    fetch('/admin/restrict-user', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({ 
            alumni_id: alumniId, 
            restrict: 1,
            restriction_reason: finalReason,
            restriction_comment: comment
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            closeRestrictModal();
            location.reload();
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        alert('An error occurred. Please try again.');
    });
}

    function viewPost(postId) {
        // Open the dedicated view post page in a new tab
        window.open('/admin/posts/' + postId + '/view', '_blank');
    }

    function viewComment(commentId) {
        // Implement view comment modal or redirect
        window.open('/admin/comments/' + commentId, '_blank');
    }

    // ========== INITIALIZE ON PAGE LOAD ==========
    document.addEventListener('DOMContentLoaded', function() {
        initCharts();
        
        // Initialize map if users tab is active by default
        const usersTab = document.getElementById('tab-users');
        if (usersTab && usersTab.classList.contains('active')) {
            // Use requestAnimationFrame to ensure DOM is fully rendered
            requestAnimationFrame(() => {
                setTimeout(() => {
                    initAlumniMap();
                }, 300);
            });
        }
        
        // Resize charts and map on window resize
        let resizeTimer;
        window.addEventListener('resize', function() {
            clearTimeout(resizeTimer);
            resizeTimer = setTimeout(() => {
                Object.values(charts).forEach(chart => {
                    if (chart && chart.resize) chart.resize();
                });
                if (alumniMap && mapInitialized) {
                    alumniMap.invalidateSize();
                }
            }, 200);
        });
    });

    // ========== MODAL FUNCTIONS ==========
    function openModal(type, id) {
        let modalId, bodyId, titleId, subtitleId;
        
        switch(type) {
            case 'violators':
                modalId = 'violatorModal';
                bodyId = 'violatorModalBody';
                titleId = 'violatorModalTitle';
                subtitleId = 'violatorModalSubtitle';
                document.getElementById(titleId).innerHTML = '<i class="fa-solid fa-users"></i> All Frequent Violators';
                document.getElementById(subtitleId).textContent = 'Showing all alumni with reports';
                loadAllViolators(bodyId);
                break;
                
            case 'violator':
                modalId = 'violatorModal';
                bodyId = 'violatorModalBody';
                titleId = 'violatorModalTitle';
                subtitleId = 'violatorModalSubtitle';
                document.getElementById(titleId).innerHTML = '<i class="fa-solid fa-user"></i> Violator Details';
                document.getElementById(subtitleId).textContent = 'Full report details for this alumni';
                loadViolatorDetails(id, bodyId);
                break;
                
            case 'posts':
                modalId = 'postModal';
                bodyId = 'postModalBody';
                subtitleId = 'postModalSubtitle';
                document.getElementById('postModalTitle').innerHTML = '<i class="fa-solid fa-file-lines"></i> All Reported Posts';
                document.getElementById(subtitleId).textContent = 'Showing all reported posts';
                loadAllPosts(bodyId);
                break;
                
            case 'post':
                modalId = 'postModal';
                bodyId = 'postModalBody';
                subtitleId = 'postModalSubtitle';
                document.getElementById('postModalTitle').innerHTML = '<i class="fa-solid fa-file-lines"></i> Post Report Details';
                document.getElementById(subtitleId).textContent = 'Full details of reported post';
                loadPostDetails(id, bodyId);
                break;
                
            case 'comments':
                modalId = 'commentModal';
                bodyId = 'commentModalBody';
                subtitleId = 'commentModalSubtitle';
                document.getElementById('commentModalTitle').innerHTML = '<i class="fa-solid fa-comments"></i> All Reported Comments';
                document.getElementById(subtitleId).textContent = 'Showing all reported comments';
                loadAllComments(bodyId);
                break;
                
            case 'comment':
                modalId = 'commentModal';
                bodyId = 'commentModalBody';
                subtitleId = 'commentModalSubtitle';
                document.getElementById('commentModalTitle').innerHTML = '<i class="fa-solid fa-comment"></i> Comment Report Details';
                document.getElementById(subtitleId).textContent = 'Full details of reported comment';
                loadCommentDetails(id, bodyId);
                break;
        }
        
        document.getElementById(modalId).classList.add('active');
        document.body.style.overflow = 'hidden';
    }

    function closeModal(modalId) {
        document.getElementById(modalId).classList.remove('active');
        document.body.style.overflow = '';
    }

    // ========== LOAD DATA INTO MODALS ==========

    function loadViolatorDetails(id, bodyId) {
        const body = document.getElementById(bodyId);
        const violators = moderationData.frequentViolators;
        const violator = violators.find(v => v.id === id);
        
        if (violator) {
            body.innerHTML = `
                <div class="modal-detail-section">
                    <div class="detail-row">
                        <span class="detail-label">Name</span>
                        <span class="detail-value">${violator.first_name} ${violator.last_name}</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Email</span>
                        <span class="detail-value">${violator.email}</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Account Status</span>
                        <span class="detail-value">${violator.account_status == 1 ? 'Active' : 'Restricted'}</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Joined</span>
                        <span class="detail-value">${violator.created_at ? new Date(violator.created_at).toLocaleDateString('en-US', { month: 'long', year: 'numeric' }) : 'N/A'}</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Total Reports</span>
                        <span class="detail-value"><strong>${violator.total_reports}</strong></span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Post Reports</span>
                        <span class="detail-value">${violator.post_reports}</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Comment Reports</span>
                        <span class="detail-value">${violator.comment_reports}</span>
                    </div>
                    ${violator.report_reasons ? `
                    <div class="detail-row full-width">
                        <span class="detail-label">Report Reasons</span>
                        <span class="detail-value">${violator.report_reasons}</span>
                    </div>
                    ` : ''}
                    <div class="detail-actions">
                        <a href="/admin/alumni/${violator.id}/view" class="btn btn-primary btn-sm">
                            <i class="fa-solid fa-eye"></i> View Profile
                        </a>
                        ${violator.account_status == 1 ? 
                            `<button class="btn btn-warning btn-sm" onclick="restrictUser(${violator.id})">
                                <i class="fa-solid fa-user-slash"></i> Restrict User
                            </button>` :
                            `<button class="btn btn-success btn-sm" onclick="restrictUser(${violator.id})">
                                <i class="fa-solid fa-user-check"></i> Unrestrict User
                            </button>`
                        }
                        <a href="/admin/messages?chat=${violator.id}" class="btn btn-secondary btn-sm">
                            <i class="fa-solid fa-comment-dots"></i> Send Message
                        </a>
                    </div>
                </div>
            `;
        } else {
            body.innerHTML = `<div class="empty-state"><p>Violator not found</p></div>`;
        }
    }

    function loadAllViolators(bodyId) {
    const body = document.getElementById(bodyId);
    const violators = moderationData.frequentViolators;
    
    if (!violators || violators.length === 0) {
        body.innerHTML = `<div class="empty-state"><p>No violators found</p></div>`;
        return;
    }
    
    let html = `<div class="modal-list">`;
    violators.forEach(v => {
        html += `
            <div class="modal-list-item">
                <div class="modal-list-avatar">
                    ${v.alumni_photo ? 
                        `<img src="${v.alumni_photo}" alt="${v.first_name}">` :
                        `<div class="modal-list-initials">${v.first_name.charAt(0)}${v.last_name.charAt(0)}</div>`
                    }
                </div>
                <div class="modal-list-info">
                    <div class="modal-list-name">${v.first_name} ${v.last_name}</div>
                    <div class="modal-list-meta">
                        <span>${v.email}</span>
                        <span>${v.total_reports} reports</span>
                        <span>${v.post_reports} posts, ${v.comment_reports} comments</span>
                    </div>
                    ${v.report_reasons ? `<div class="modal-list-reasons">${v.report_reasons}</div>` : ''}
                </div>
                <div class="modal-list-actions">
                    <a href="/admin/alumni/${v.id}/view" class="btn-action btn-view" title="View Profile">
                        <i class="fa-solid fa-eye"></i>
                    </a>
                    ${v.account_status == 1 ? 
                        `<button class="btn-action btn-restrict" onclick="restrictUser(${v.id})" title="Restrict User">
                            <i class="fa-solid fa-user-slash"></i>
                        </button>` :
                        `<button class="btn-action btn-unrestrict" onclick="restrictUser(${v.id})" title="Unrestrict User" style="background: #D1FAE5; color: #065F46;">
                            <i class="fa-solid fa-user-check"></i>
                        </button>`
                    }
                </div>
            </div>
        `;
    });
    html += `</div>`;
    body.innerHTML = html;
}

function loadPostDetails(id, bodyId) {
    const body = document.getElementById(bodyId);
    const posts = moderationData.reportedPosts;
    const post = posts.find(p => p.id === id);
    
    if (post) {
        // Get the full post data including images
        fetch(`/admin/posts/${id}/full`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const fullPost = data.post;
                renderPostDetailModal(body, fullPost, post);
            } else {
                // Fallback to basic info if API fails
                renderBasicPostDetail(body, post);
            }
        })
        .catch(error => {
            console.error('Error loading full post:', error);
            // Fallback to basic info
            renderBasicPostDetail(body, post);
        });
    } else {
        body.innerHTML = `<div class="empty-state"><p>Post not found</p></div>`;
    }
}

function renderPostDetailModal(body, fullPost, reportData) {
    // Get author info
    const author = fullPost.alumni || {};
    const authorName = author ? `${author.first_name || ''} ${author.last_name || ''}`.trim() : 'Unknown';
    const initials = author ? (author.first_name?.[0] || '?') + (author.last_name?.[0] || '') : '?';
    
    // Get profile photo
    let authorPhoto = '';
    const photoPath = trim((author?.alumni_photo || author?.card_photo || ''));
    if (photoPath) {
        if (photoPath.match(/^https?:\/\//i)) {
            authorPhoto = photoPath;
        } else if (photoPath.startsWith('/storage/') || photoPath.startsWith('storage/')) {
            authorPhoto = photoPath.startsWith('/') ? photoPath : '/' + photoPath;
        } else {
            const supabaseUrl = '{{ rtrim(config("filesystems.disks.s3.url", ""), "/") }}';
            authorPhoto = supabaseUrl ? supabaseUrl + '/' + ltrim(photoPath, '/') : '';
        }
    }
    
    // Build images HTML
    let imagesHtml = '';
    if (fullPost.images && fullPost.images.length > 0) {
        const imageCount = fullPost.images.length;
        const gridClass = imageCount === 1 ? 'grid-1' : (imageCount === 2 ? 'grid-2' : (imageCount === 3 ? 'grid-3' : 'grid-4'));
        
        imagesHtml = `
            <div class="modal-post-images ${gridClass}">
                ${fullPost.images.map(img => {
                    const imgPath = ltrim(img.image_path || '', '/');
                    const supabaseUrl = '{{ rtrim(config("filesystems.disks.s3.url", ""), "/") }}';
                    const imgUrl = supabaseUrl ? supabaseUrl + '/' + imgPath : imgPath;
                    return `<img src="${imgUrl}" alt="Post image" onclick="window.open('${imgUrl}', '_blank')" loading="lazy" onerror="this.style.display='none'">`;
                }).join('')}
            </div>
        `;
    }
    
    // Build comments HTML (top 5)
    let commentsHtml = '';
    if (fullPost.comments && fullPost.comments.length > 0) {
        const topComments = fullPost.comments.slice(0, 5);
        commentsHtml = `
            <div class="modal-post-comments">
                <div class="comments-header">
                    <i class="fa-regular fa-comment-dots"></i>
                    Comments (${fullPost.comments.length})
                </div>
                ${topComments.map(c => {
                    const cAuthor = c.alumni || {};
                    const cName = cAuthor ? `${cAuthor.first_name || ''} ${cAuthor.last_name || ''}`.trim() : 'Unknown';
                    return `
                        <div class="modal-comment-item">
                            <div class="modal-comment-avatar">${(cAuthor.first_name?.[0] || '?') + (cAuthor.last_name?.[0] || '')}</div>
                            <div class="modal-comment-body">
                                <div class="modal-comment-author">${cName}</div>
                                <div class="modal-comment-text">${escapeHtml(c.comment || '')}</div>
                                <div class="modal-comment-time">${c.created_at ? new Date(c.created_at).toLocaleString() : ''}</div>
                            </div>
                        </div>
                    `;
                }).join('')}
                ${fullPost.comments.length > 5 ? `<div class="more-comments">+ ${fullPost.comments.length - 5} more comments</div>` : ''}
            </div>
        `;
    }
    
    // Build reactions summary
    let reactionsHtml = '';
    if (fullPost.reactions && fullPost.reactions.length > 0) {
        const reactionCounts = {};
        fullPost.reactions.forEach(r => {
            reactionCounts[r.reaction] = (reactionCounts[r.reaction] || 0) + 1;
        });
        const reactionEmojis = {
            'like': '❤️',
            'love': '😍',
            'insightful': '💡',
            'support': '🤝'
        };
        reactionsHtml = Object.entries(reactionCounts).map(([type, count]) => 
            `${reactionEmojis[type] || '👍'} ${count}`
        ).join(' ');
    }
    
    body.innerHTML = `
        <div class="modal-post-full">
            <!-- Author Info -->
            <div class="modal-post-author">
                <div class="modal-post-avatar" style="${authorPhoto ? 'padding: 0; overflow: hidden; background: none;' : ''}">
                    ${authorPhoto ? `<img src="${authorPhoto}" alt="${authorName}" onerror="this.style.display='none'; this.parentElement.innerHTML='${initials}';">` : initials}
                </div>
                <div class="modal-post-author-info">
                    <div class="modal-post-author-name">${authorName}</div>
                    <div class="modal-post-author-meta">
                        <span><i class="fa-regular fa-calendar"></i> ${fullPost.created_at ? new Date(fullPost.created_at).toLocaleString() : 'N/A'}</span>
                        <span class="post-badge visibility ${fullPost.visibility || 'public'}">
                            <i class="fa-solid fa-${(fullPost.visibility || 'public') === 'public' ? 'globe' : 'lock'}"></i>
                            ${fullPost.visibility || 'Public'}
                        </span>
                        <span class="post-badge moderation ${fullPost.moderation_status || 'pending'}">
                            <i class="fa-solid fa-${(fullPost.moderation_status || 'pending') === 'approved' ? 'check' : 'clock'}"></i>
                            ${fullPost.moderation_status || 'Pending'}
                        </span>
                    </div>
                </div>
            </div>
            
            <!-- Caption -->
            ${fullPost.caption ? `<div class="modal-post-caption">${escapeHtml(fullPost.caption)}</div>` : ''}
            
            <!-- Images -->
            ${imagesHtml}
            
            <!-- Reactions Summary -->
            ${reactionsHtml ? `<div class="modal-post-reactions">${reactionsHtml}</div>` : ''}
            
            <!-- Comments -->
            ${commentsHtml}
            
            <!-- Report Details -->
            <div class="modal-post-report-details">
                <div class="report-details-header">
                    <i class="fa-solid fa-flag" style="color: var(--danger);"></i>
                    Report Details
                    <span class="report-count-badge-small">${reportData.report_count} report${reportData.report_count > 1 ? 's' : ''}</span>
                </div>
                <div class="report-details-body">
                    <div class="detail-row">
                        <span class="detail-label">Report Reasons</span>
                        <span class="detail-value">${reportData.report_reasons || 'No specific reasons provided'}</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Status</span>
                        <span class="detail-value">${fullPost.moderation_status || 'pending'}</span>
                    </div>
                    ${reportData.created_at ? `
                    <div class="detail-row">
                        <span class="detail-label">First Reported</span>
                        <span class="detail-value">${new Date(reportData.created_at).toLocaleString()}</span>
                    </div>
                    ` : ''}
                </div>
            </div>
            
            <!-- Actions -->
            <div class="detail-actions">
                <button class="btn btn-primary btn-sm" onclick="viewPost(${reportData.id})">
                    <i class="fa-solid fa-eye"></i> View Full Post
                </button>
                <button class="btn btn-success btn-sm" onclick="moderatePost(${reportData.id}, 'approve')">
                    <i class="fa-solid fa-check"></i> Approve
                </button>
                <button class="btn btn-warning btn-sm" onclick="moderatePost(${reportData.id}, 'hide')">
                    <i class="fa-solid fa-eye-slash"></i> Hide
                </button>
                <button class="btn btn-danger btn-sm" onclick="moderatePost(${reportData.id}, 'delete')">
                    <i class="fa-solid fa-trash"></i> Delete
                </button>
                <button class="btn btn-danger btn-sm" onclick="restrictUser(${fullPost.alumni_id})">
                    <i class="fa-solid fa-user-slash"></i> Restrict User
                </button>
            </div>
        </div>
    `;
}

function renderBasicPostDetail(body, post) {
    body.innerHTML = `
        <div class="modal-detail-section">
            <div class="detail-row full-width">
                <span class="detail-label">Post Content</span>
                <span class="detail-value" style="font-size: 1rem; background: var(--gray-50); padding: 1rem; border-radius: var(--radius);">${post.caption || 'No caption'}</span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Author</span>
                <span class="detail-value">${post.alumni ? post.alumni.first_name + ' ' + post.alumni.last_name : 'Unknown'}</span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Posted Date</span>
                <span class="detail-value">${post.created_at ? new Date(post.created_at).toLocaleDateString('en-US', { month: 'long', day: 'numeric', year: 'numeric' }) : 'Date not available'}</span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Report Count</span>
                <span class="detail-value"><strong>${post.report_count}</strong></span>
            </div>
            <div class="detail-row full-width">
                <span class="detail-label">Report Reasons</span>
                <span class="detail-value">${post.report_reasons || 'No specific reasons provided'}</span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Status</span>
                <span class="detail-value">${post.moderation_status || 'unknown'}</span>
            </div>
            <div class="detail-actions">
                <button class="btn btn-primary btn-sm" onclick="viewPost(${post.id})">
                    <i class="fa-solid fa-eye"></i> View Full Post
                </button>
                <button class="btn btn-success btn-sm" onclick="moderatePost(${post.id}, 'approve')">
                    <i class="fa-solid fa-check"></i> Approve
                </button>
                <button class="btn btn-warning btn-sm" onclick="moderatePost(${post.id}, 'hide')">
                    <i class="fa-solid fa-eye-slash"></i> Hide
                </button>
                <button class="btn btn-danger btn-sm" onclick="moderatePost(${post.id}, 'delete')">
                    <i class="fa-solid fa-trash"></i> Delete
                </button>
            </div>
        </div>
    `;
}

// Helper function to trim strings
function trim(str) {
    return (str || '').trim();
}

// Helper function to left trim
function ltrim(str, char) {
    if (!str) return '';
    char = char || '/';
    while (str.startsWith(char)) {
        str = str.substring(1);
    }
    return str;
}

  function loadAllPosts(bodyId) {
    const body = document.getElementById(bodyId);
    const posts = moderationData.reportedPosts;
    
    if (!posts || posts.length === 0) {
        body.innerHTML = `<div class="empty-state"><p>No reported posts</p></div>`;
        return;
    }
    
    let html = `<div class="modal-list">`;
    posts.forEach(p => {
        html += `
            <div class="modal-list-item">
                <div class="modal-list-info">
                    <div class="modal-list-name">${p.caption ? p.caption.substring(0, 80) + '...' : 'No caption'}</div>
                    <div class="modal-list-meta">
                        <span>By: ${p.alumni ? p.alumni.first_name + ' ' + p.alumni.last_name : 'Unknown'}</span>
                        <span>${p.report_count} reports</span>
                        <span>${p.created_at ? new Date(p.created_at).toLocaleDateString() : 'N/A'}</span>
                    </div>
                    ${p.report_reasons ? `<div class="modal-list-reasons">${p.report_reasons}</div>` : ''}
                </div>
                <div class="modal-list-actions">
                    <button class="btn-action btn-view" onclick="viewPost(${p.id})" title="View Post">
                        <i class="fa-solid fa-eye"></i>
                    </button>
                    <button class="btn-action btn-approve" onclick="moderatePost(${p.id}, 'approve')" title="Approve">
                        <i class="fa-solid fa-check"></i>
                    </button>
                    <button class="btn-action btn-hide" onclick="moderatePost(${p.id}, 'hide')" title="Hide">
                        <i class="fa-solid fa-eye-slash"></i>
                    </button>
                    <button class="btn-action btn-delete" onclick="moderatePost(${p.id}, 'delete')" title="Delete">
                        <i class="fa-solid fa-trash"></i>
                    </button>
                </div>
            </div>
        `;
    });
    html += `</div>`;
    body.innerHTML = html;
}

    function loadCommentDetails(id, bodyId) {
        const body = document.getElementById(bodyId);
        const comments = moderationData.reportedComments;
        const comment = comments.find(c => c.id === id);
        
        if (comment) {
            body.innerHTML = `
                <div class="modal-detail-section">
                    <div class="detail-row full-width">
                        <span class="detail-label">Comment Content</span>
                        <span class="detail-value" style="font-size: 1rem; background: var(--gray-50); padding: 1rem; border-radius: var(--radius);">${comment.comment || 'Comment deleted'}</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Author</span>
                        <span class="detail-value">${comment.alumni ? comment.alumni.first_name + ' ' + comment.alumni.last_name : 'Unknown User'}</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Commented Date</span>
                        <span class="detail-value">${comment.created_at ? new Date(comment.created_at).toLocaleDateString('en-US', { month: 'long', day: 'numeric', year: 'numeric' }) : 'Date not available'}</span>
                    </div>
                    ${comment.post_caption ? `
                    <div class="detail-row full-width">
                        <span class="detail-label">Related Post</span>
                        <span class="detail-value">${comment.post_caption}</span>
                    </div>
                    ` : ''}
                    <div class="detail-row">
                        <span class="detail-label">Report Count</span>
                        <span class="detail-value"><strong>${comment.report_count}</strong></span>
                    </div>
                    <div class="detail-row full-width">
                        <span class="detail-label">Report Reasons</span>
                        <span class="detail-value">${comment.report_reasons || 'No specific reasons provided'}</span>
                    </div>
                    <div class="detail-actions">
                        <button class="btn btn-primary btn-sm" onclick="viewComment(${comment.id})">
                            <i class="fa-solid fa-eye"></i> View Full Comment
                        </button>
                        <button class="btn btn-success btn-sm" onclick="moderateComment(${comment.id}, 'approve')">
                            <i class="fa-solid fa-check"></i> Approve
                        </button>
                        <button class="btn btn-danger btn-sm" onclick="moderateComment(${comment.id}, 'delete')">
                            <i class="fa-solid fa-trash"></i> Delete
                        </button>
                    </div>
                </div>
            `;
        } else {
            body.innerHTML = `<div class="empty-state"><p>Comment not found</p></div>`;
        }
    }

 function loadAllComments(bodyId) {
    const body = document.getElementById(bodyId);
    const comments = moderationData.reportedComments;
    
    if (!comments || comments.length === 0) {
        body.innerHTML = `<div class="empty-state"><p>No reported comments</p></div>`;
        return;
    }
    
    let html = `<div class="modal-list">`;
    comments.forEach(c => {
        html += `
            <div class="modal-list-item">
                <div class="modal-list-info">
                    <div class="modal-list-name">"${c.comment ? c.comment.substring(0, 80) + '...' : 'Comment deleted'}"</div>
                    <div class="modal-list-meta">
                        <span>By: ${c.alumni ? c.alumni.first_name + ' ' + c.alumni.last_name : 'Unknown User'}</span>
                        <span>${c.report_count} reports</span>
                        ${c.post_caption ? `<span>On: ${c.post_caption.substring(0, 30)}...</span>` : ''}
                    </div>
                    ${c.report_reasons ? `<div class="modal-list-reasons">${c.report_reasons}</div>` : ''}
                </div>
                <div class="modal-list-actions">
                    <button class="btn-action btn-view" onclick="viewComment(${c.id})" title="View Comment">
                        <i class="fa-solid fa-eye"></i>
                    </button>
                    <button class="btn-action btn-approve" onclick="moderateComment(${c.id}, 'approve')" title="Approve">
                        <i class="fa-solid fa-check"></i>
                    </button>
                    <button class="btn-action btn-delete" onclick="moderateComment(${c.id}, 'delete')" title="Delete">
                        <i class="fa-solid fa-trash"></i>
                    </button>
                </div>
            </div>
        `;
    });
    html += `</div>`;
    body.innerHTML = html;
}

let currentPostId = null;
let currentTab = 'likes';

function openInteractionsModal(postId, tab = 'likes') {
    currentPostId = postId;
    currentTab = tab;
    
    const modal = document.getElementById('interactionsModal');
    modal.style.display = 'flex';
    modal.classList.add('active');
    document.body.style.overflow = 'hidden';
    
    // Set title
    const titleMap = {
        'likes': 'Likes',
        'comments': 'Comments',
        'reposts': 'Reposts'
    };
    document.getElementById('interactionsModalTitle').textContent = titleMap[tab] || 'Interactions';
    
    // Switch tab
    switchInteractionTab(tab);
    
    // Load data
    loadInteractions(postId, tab);
}

function closeInteractionsModal() {
    const modal = document.getElementById('interactionsModal');
    modal.style.display = 'none';
    modal.classList.remove('active');
    document.body.style.overflow = '';
    currentPostId = null;
}

function switchInteractionTab(tab) {
    currentTab = tab;
    
    // Update tabs
    document.querySelectorAll('.interactions-tab').forEach(t => {
        t.classList.remove('active');
        if (t.dataset.tab === tab) {
            t.classList.add('active');
        }
    });
    
    // Update panels
    document.querySelectorAll('.interactions-panel').forEach(p => {
        p.classList.remove('active');
    });
    const panel = document.getElementById(tab + 'Panel');
    if (panel) {
        panel.classList.add('active');
    }
    
    // Update title
    const titleMap = {
        'likes': 'Likes',
        'comments': 'Comments',
        'reposts': 'Reposts'
    };
    document.getElementById('interactionsModalTitle').textContent = titleMap[tab] || 'Interactions';
    
    // Load data if we have a post ID
    if (currentPostId) {
        loadInteractions(currentPostId, tab);
    }
}

function loadInteractions(postId, type) {
    const listId = type + 'List';
    const list = document.getElementById(listId);
    
    if (!list) {
        console.error('List element not found:', listId);
        return;
    }
    
    // Show loading
    list.innerHTML = `
        <div class="interactions-loading">
            <i class="fa-solid fa-spinner fa-spin"></i> Loading ${type}...
        </div>
    `;
    
    // Get CSRF token
    const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    
    // Make AJAX request
    fetch(`/admin/posts/${postId}/interactions?type=${type}`, {
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': token
        }
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Network response was not ok');
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            renderInteractions(listId, data.data, type);
            const countEl = document.getElementById('interactionsCount');
            if (countEl) {
                countEl.textContent = `(${data.total || 0})`;
            }
        } else {
            list.innerHTML = `
                <div class="interactions-empty">
                    <i class="fa-regular fa-circle-xmark"></i>
                    <p>${data.message || 'Failed to load interactions.'}</p>
                </div>
            `;
        }
    })
    .catch(error => {
        console.error('Error loading interactions:', error);
        list.innerHTML = `
            <div class="interactions-empty">
                <i class="fa-regular fa-circle-xmark"></i>
                <p>Error loading ${type}. Please try again.</p>
            </div>
        `;
    });
}

function renderInteractions(listId, items, type) {
    const list = document.getElementById(listId);
    
    if (!list) return;
    
    if (!items || items.length === 0) {
        const iconMap = {
            'likes': 'fa-regular fa-heart',
            'comments': 'fa-regular fa-comment',
            'reposts': 'fa-solid fa-retweet'
        };
        list.innerHTML = `
            <div class="interactions-empty">
                <i class="${iconMap[type] || 'fa-regular fa-circle'}" style="font-size: 2.5rem;"></i>
                <p>No ${type} yet</p>
            </div>
        `;
        return;
    }
    
    let html = '';
    items.forEach(item => {
        const initials = (item.first_name?.[0] || '?') + (item.last_name?.[0] || '');
        const fullName = `${item.first_name || 'Unknown'} ${item.last_name || ''}`.trim();
        const timeAgo = item.created_at ? timeAgoHelper(item.created_at) : '';
        
        // Check if user has a profile photo
        let avatarHtml = '';
        if (item.profile_photo) {
            avatarHtml = `<img src="${item.profile_photo}" alt="${fullName}" style="width: 100%; height: 100%; object-fit: cover; border-radius: 50%;">`;
        } else {
            avatarHtml = initials.toUpperCase();
        }
        
        let detailHtml = '';
        if (type === 'comments' && item.comment) {
            detailHtml = `<div class="interactions-item-detail"><i class="fa-regular fa-comment"></i> ${escapeHtml(item.comment)}</div>`;
        } else if (type === 'reposts' && item.caption) {
            detailHtml = `<div class="interactions-item-detail"><i class="fa-regular fa-retweet"></i> ${escapeHtml(item.caption)}</div>`;
        }
        
        html += `
            <div class="interactions-item">
                <div class="interactions-item-avatar" style="${item.profile_photo ? 'padding: 0; overflow: hidden; background: none;' : ''}">
                    ${avatarHtml}
                </div>
                <div class="interactions-item-info">
                    <div class="interactions-item-name">${escapeHtml(fullName)}</div>
                    ${detailHtml}
                </div>
                ${timeAgo ? `<div class="interactions-item-time">${timeAgo}</div>` : ''}
            </div>
        `;
    });
    
    list.innerHTML = html;
}

function timeAgoHelper(dateString) {
    const now = new Date();
    const past = new Date(dateString);
    const diffMs = now - past;
    const diffMins = Math.floor(diffMs / 60000);
    const diffHours = Math.floor(diffMs / 3600000);
    const diffDays = Math.floor(diffMs / 86400000);
    
    if (diffMins < 1) return 'Just now';
    if (diffMins < 60) return diffMins + 'm ago';
    if (diffHours < 24) return diffHours + 'h ago';
    if (diffDays < 7) return diffDays + 'd ago';
    return past.toLocaleDateString();
}

function escapeHtml(text) {
    if (!text) return '';
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}
// Close modal on ESC key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        const modal = document.getElementById('interactionsModal');
        if (modal && modal.style.display === 'flex') {
            closeInteractionsModal();
        }
        // Also close restrict modal
        const restrictModal = document.getElementById('restrictReasonModal');
        if (restrictModal && restrictModal.style.display === 'flex') {
            closeRestrictModal();
        }
    }
});
// Close modal on overlay click
document.querySelector('.interactions-modal-overlay')?.addEventListener('click', function(e) {
    if (e.target === this) {
        closeInteractionsModal();
    }
});


// ========== ALUMNI LOCATION MAP ==========
let alumniMap = null;
let mapInitialized = false;

function isElementVisible(el) {
    if (!el) return false;
    const rect = el.getBoundingClientRect();
    const style = window.getComputedStyle(el);
    return style.display !== 'none' && 
           style.visibility !== 'hidden' && 
           rect.width > 0 && 
           rect.height > 0;
}

function initAlumniMap() {
    if (mapInitialized) {
        console.log('Map already initialized');
        return;
    }
    
    const mapContainer = document.getElementById('alumniMap');
    if (!mapContainer) {
        console.warn('Map container not found');
        return;
    }

    if (!isElementVisible(mapContainer)) {
        console.warn('Map container is not visible');
        return;
    }

    mapContainer.innerHTML = '';

    try {
        // Get the data - ensure it's properly parsed
        let alumniLocations = @json($alumniLocations ?? []);
        
        // If it's a string, parse it
        if (typeof alumniLocations === 'string') {
            alumniLocations = JSON.parse(alumniLocations);
        }
        
        // Ensure it's an array
        if (!Array.isArray(alumniLocations)) {
            alumniLocations = [];
        }
        
        console.log('Parsed alumni locations:', alumniLocations);
        console.log('Number of locations:', alumniLocations.length);
        
        if (!alumniLocations || alumniLocations.length === 0) {
            mapContainer.innerHTML = `
                <div class="map-loading">
                    <i class="fa-solid fa-map-location-dot" style="font-size: 3rem; color: var(--gray-400);"></i>
                    <p style="color: var(--gray-500); font-weight: 500;">No alumni location data available</p>
                    <p style="color: var(--gray-400); font-size: 0.875rem; max-width: 300px; text-align: center;">
                        Alumni need to add their address information with valid coordinates
                    </p>
                </div>
            `;
            return;
        }

        // Filter and validate locations with detailed logging
        const validLocations = alumniLocations.filter((alumni, index) => {
            // Log raw values
            console.log(`Processing location ${index}:`, {
                id: alumni.id,
                name: `${alumni.first_name} ${alumni.last_name}`,
                raw_lat: alumni.latitude,
                raw_lng: alumni.longitude,
                lat_type: typeof alumni.latitude,
                lng_type: typeof alumni.longitude
            });
            
            const lat = parseFloat(alumni.latitude);
            const lng = parseFloat(alumni.longitude);
            
            const isValid = !isNaN(lat) && !isNaN(lng) && 
                           lat !== 0 && lng !== 0 &&
                           lat >= -90 && lat <= 90 &&
                           lng >= -180 && lng <= 180 &&
                           alumni.latitude !== null && 
                           alumni.longitude !== null &&
                           alumni.latitude !== '' && 
                           alumni.longitude !== '';
            
            console.log(`Location ${index} validation:`, {
                name: `${alumni.first_name} ${alumni.last_name}`,
                lat: lat,
                lng: lng,
                isValid: isValid,
                values: { lat, lng }
            });
            
            return isValid;
        });

        console.log('Valid locations count:', validLocations.length);
        
        if (validLocations.length > 0) {
            console.log('Sample valid location:', validLocations[0]);
            console.log('All valid locations:', validLocations.map(l => ({
                name: `${l.first_name} ${l.last_name}`,
                lat: parseFloat(l.latitude),
                lng: parseFloat(l.longitude)
            })));
        }

        if (validLocations.length === 0) {
            mapContainer.innerHTML = `
                <div class="map-loading">
                    <i class="fa-solid fa-map-location-dot" style="font-size: 3rem; color: var(--gray-400);"></i>
                    <p style="color: var(--gray-500); font-weight: 500;">No valid location data available</p>
                    <p style="color: var(--gray-400); font-size: 0.875rem; max-width: 300px; text-align: center;">
                        Please check that alumni have valid coordinates (latitude and longitude)
                    </p>
                </div>
            `;
            return;
        }

        // Set explicit dimensions
        mapContainer.style.height = '400px';
        mapContainer.style.width = '100%';

        // Initialize map with global-friendly settings
        alumniMap = L.map('alumniMap', {
            center: [12.8797, 121.7740], // Default center
            zoom: 5,
            minZoom: 2,                  // Prevents zooming out into infinite grey space
            worldCopyJump: true,         // Seamless scrolling across the International Date Line
            maxBoundsViscosity: 1.0
        });

        // Tile Layer with continuous world rendering
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; OpenStreetMap contributors',
            maxZoom: 19,
            noWrap: false
        }).addTo(alumniMap);

        const alumniIcon = L.divIcon({
            className: '', // Keep this empty to drop external classes
            html: `<div style="
                background: #32418C; 
                width: 30px; 
                height: 30px; 
                border-radius: 50%; 
                border: 3px solid #FBD117; 
                display: flex; 
                align-items: center; 
                justify-content: center; 
                color: white; 
                cursor: pointer; 
                box-shadow: 0 2px 10px rgba(50,65,140,0.3);
                margin: 0 !important;  /* Forces the icon to ignore global margins */
                padding: 0 !important; /* Forces the icon to ignore global padding */
            ">
                <i class="fa-solid fa-user" style="font-size: 12px; margin: 0; padding: 0;"></i>
            </div>`,
            iconSize: [30, 30],
            iconAnchor: [15, 15], // Exactly centers the 30x30 div on the coordinate
            popupAnchor: [0, -15] // Places the popup tail just above the circle
        });

        // Create a feature group
        const markerGroup = L.markerClusterGroup({
            spiderfyOnMaxZoom: true,
            showCoverageOnHover: false,
            zoomToBoundsOnClick: true
        }).addTo(alumniMap);

        // Add markers
        let addedCount = 0;
        const markers = [];
        
        validLocations.forEach((alumni) => {
            const lat = parseFloat(alumni.latitude);
            const lng = parseFloat(alumni.longitude);
            
            if (isNaN(lat) || isNaN(lng)) {
                console.warn('Skipping invalid coordinates for:', alumni.first_name, alumni.last_name);
                return;
            }
            
            addedCount++;
            console.log(`Adding marker for ${alumni.first_name} ${alumni.last_name} at [${lat}, ${lng}]`);

            const popupContent = `
                <div style="padding: 12px; min-width: 220px;">
                    <div style="display: flex; align-items: center; margin-bottom: 10px;">
                        ${alumni.alumni_photo ? 
                            `<img src="${alumni.alumni_photo}" style="width: 40px; height: 40px; border-radius: 50%; margin-right: 10px; object-fit: cover;">` :
                            `<div style="width: 40px; height: 40px; border-radius: 50%; background: #32418C; color: white; display: flex; align-items: center; justify-content: center; margin-right: 10px; font-weight: bold;">
                                ${alumni.first_name.charAt(0)}${alumni.last_name.charAt(0)}
                            </div>`
                        }
                        <div>
                            <strong style="display: block; font-size: 14px;">${alumni.first_name} ${alumni.last_name}</strong>
                            <small style="color: #666; font-size: 12px;">
                                <i class="fa-solid fa-location-dot"></i> 
                                ${alumni.region || 'Location not specified'}
                            </small>
                        </div>
                    </div>
                    ${alumni.province || alumni.municipality || alumni.barangay ? `
                        <div style="font-size: 12px; color: #555; margin-bottom: 8px; padding: 4px 8px; background: #f5f5f5; border-radius: 4px;">
                            ${[alumni.barangay, alumni.municipality, alumni.province].filter(Boolean).join(', ')}
                        </div>
                    ` : ''}
                    <div style="display: flex; gap: 8px; border-top: 1px solid #eee; padding-top: 8px;">
                        <a href="/admin/alumni/${alumni.id}/view" style="flex: 1; text-align: center; padding: 6px; background: #32418C; color: white; text-decoration: none; border-radius: 4px; font-size: 12px;">
                            <i class="fa-solid fa-eye"></i> View
                        </a>
                        <a href="/admin/messages?chat=${alumni.id}" style="flex: 1; text-align: center; padding: 6px; background: #FBD117; color: #32418C; text-decoration: none; border-radius: 4px; font-size: 12px;">
                            <i class="fa-solid fa-comment"></i> Message
                        </a>
                    </div>
                </div>
            `;

            const marker = L.marker([lat, lng], { 
                icon: alumniIcon,
                interactive: true
            });
            
        // 1. Enable autoClose so only one popup shows at a time
            marker.bindPopup(popupContent, {
                maxWidth: 280,
                minWidth: 220,
                closeButton: true,
                autoClose: true, 
                closeOnClick: true
            });

            markers.push(marker);
            markerGroup.addLayer(marker);
        });

        console.log(`Added ${addedCount} markers to map`);

        mapInitialized = true;
        
        // Fit bounds
        if (addedCount > 0) {
            try {
                const bounds = markerGroup.getBounds();
                if (bounds && bounds.isValid()) {
                    alumniMap.fitBounds(bounds.pad(0.15));
                } else {
                    const first = validLocations[0];
                    if (first) {
                        alumniMap.setView([parseFloat(first.latitude), parseFloat(first.longitude)], 10);
                    }
                }
            } catch (e) {
                console.warn('Could not fit bounds:', e);
            }
        }

        // Force resize
        setTimeout(() => {
            if (alumniMap) {
                alumniMap.invalidateSize();
            }
        }, 500);

    } catch (error) {
        console.error('Error initializing map:', error);
        mapContainer.innerHTML = `
            <div class="map-loading">
                <i class="fa-solid fa-triangle-exclamation" style="font-size: 3rem; color: red;"></i>
                <p>Error loading map: ${error.message}</p>
            </div>
        `;
    }
}

// ========== TAB NAVIGATION ==========
function showTab(tabName) {
    // Hide all tabs
    document.querySelectorAll('.tab-content').forEach(tab => {
        tab.classList.remove('active');
    });
    
    // Deselect all buttons
    document.querySelectorAll('.tab-btn').forEach(btn => {
        btn.classList.remove('active');
    });
    
    // Show the selected tab
    const targetTab = document.getElementById('tab-' + tabName);
    if (targetTab) {
        targetTab.classList.add('active');
    }
    
    // Highlight the clicked button
    const clickedBtn = document.querySelector(`.tab-btn[onclick*="${tabName}"]`);
    if (clickedBtn) {
        clickedBtn.classList.add('active');
    }

    // Refresh charts and handle map
    setTimeout(() => {
        // Refresh charts
        Object.values(charts).forEach(chart => {
            if (chart && chart.resize) {
                chart.resize();
            }
        });
        
        // Handle map initialization for users tab
        if (tabName === 'users') {
            if (!mapInitialized) {
                // Initialize map if not already done
                initAlumniMap();
            } else if (alumniMap) {
                // If map exists, invalidate size to ensure proper display
                setTimeout(() => {
                    alumniMap.invalidateSize();
                }, 100);
            }
        }
    }, 200);
}

// ========== INITIALIZE ON PAGE LOAD ==========
document.addEventListener('DOMContentLoaded', function() {
    initCharts();
    
    // Initialize map if users tab is active by default
    const usersTab = document.getElementById('tab-users');
    if (usersTab && usersTab.classList.contains('active')) {
        // Small delay to ensure DOM is fully rendered
        setTimeout(() => {
            initAlumniMap();
        }, 500);
    }
    
    // Add event listener for tab changes using MutationObserver
    const observer = new MutationObserver((mutations) => {
        mutations.forEach((mutation) => {
            if (mutation.type === 'attributes' && mutation.attributeName === 'class') {
                const usersTab = document.getElementById('tab-users');
                if (usersTab && usersTab.classList.contains('active') && !mapInitialized) {
                    setTimeout(() => {
                        initAlumniMap();
                    }, 300);
                }
            }
        });
    });
    
    // Observe the users tab for class changes
    if (usersTab) {
        observer.observe(usersTab, { attributes: true });
    }
    
    // Resize charts and map on window resize
    let resizeTimer;
    window.addEventListener('resize', function() {
        clearTimeout(resizeTimer);
        resizeTimer = setTimeout(() => {
            Object.values(charts).forEach(chart => {
                if (chart && chart.resize) chart.resize();
            });
            if (alumniMap && mapInitialized) {
                alumniMap.invalidateSize();
            }
        }, 200);
    });
});

    </script>

</body>
</html>