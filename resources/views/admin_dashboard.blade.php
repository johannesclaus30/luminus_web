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

    // ========== TAB NAVIGATION ==========
    function showTab(tabName) {
        document.querySelectorAll('.tab-content').forEach(tab => {
            tab.classList.remove('active');
        });
        document.querySelectorAll('.tab-btn').forEach(btn => {
            btn.classList.remove('active');
        });
        
        const targetTab = document.getElementById('tab-' + tabName);
        if (targetTab) {
            targetTab.classList.add('active');
        }
        
        const clickedBtn = document.querySelector(`.tab-btn[onclick*="${tabName}"]`);
        if (clickedBtn) {
            clickedBtn.classList.add('active');
        }

        // Refresh charts when tab becomes visible
        setTimeout(() => {
            const charts = Chart.instances;
            charts.forEach(chart => chart.resize());
        }, 100);
    }

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
        if (!confirm('Are you sure you want to restrict this user? This will suspend their account.')) return;
        
        fetch('/admin/restrict-user', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({ alumni_id: alumniId })
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

    function viewPost(postId) {
        // Implement view post modal or redirect
        window.open('/admin/posts/' + postId, '_blank');
    }

    function viewComment(commentId) {
        // Implement view comment modal or redirect
        window.open('/admin/comments/' + commentId, '_blank');
    }

    // ========== INITIALIZE ON PAGE LOAD ==========
    document.addEventListener('DOMContentLoaded', function() {
        initCharts();

        // Resize charts on window resize
        let resizeTimer;
        window.addEventListener('resize', function() {
            clearTimeout(resizeTimer);
            resizeTimer = setTimeout(() => {
                Object.values(charts).forEach(chart => {
                    if (chart && chart.resize) chart.resize();
                });
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
        } else {
            body.innerHTML = `<div class="empty-state"><p>Post not found</p></div>`;
        }
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
    </script>

</body>
</html>