<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Announcements | LumiNUs Admin</title>

    <!-- Fonts & Icons -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Stylesheets -->
    <link rel="stylesheet" href="/css/admin.css">
    <link rel="stylesheet" href="/css/admin-fixed-navbar.css">
    <link rel="stylesheet" href="/css/announcements_modern.css">
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
                <a href="/admin/dashboard" class="nav-item {{ request()->is('admin/dashboard') ? 'active' : '' }}">
                    <i class="fa-solid fa-chart-line"></i><span>Dashboard</span>
                </a>
                @endif
                
                @if(isset($accessibleModules['directory']))
                <a href="/admin/directory" class="nav-item {{ request()->is('admin/directory*') ? 'active' : '' }}">
                    <i class="fa-solid fa-users"></i><span>Alumni Directory</span>
                </a>
                @endif
                
                @if(isset($accessibleModules['announcements']))
                <a href="{{ route('announcements.index') }}" class="nav-item {{ request()->is('admin/announcements*') ? 'active' : '' }}">
                    <i class="fa-solid fa-bullhorn"></i><span>Announcements</span>
                </a>
                @endif
                
                @if(isset($accessibleModules['events']))
                <a href="{{ route('events.index') }}" class="nav-item {{ request()->is('admin/events*') ? 'active' : '' }}">
                    <i class="fa-solid fa-calendar-check"></i><span>Events</span>
                </a>
                @endif
                
                @if(isset($accessibleModules['perks']))
                <a href="{{ route('perks.index') }}" class="nav-item {{ request()->is('admin/perks*') ? 'active' : '' }}">
                    <i class="fa-solid fa-gift"></i><span>Perks & Discounts</span>
                </a>
                @endif
                
                @if(isset($accessibleModules['tracer']))
                <a href="/admin/alumni_tracer" class="nav-item {{ request()->is('admin/alumni_tracer*') ? 'active' : '' }}">
                    <i class="fa-solid fa-location-dot"></i><span>Alumni Tracer</span>
                </a>
                @endif
                
                @if(isset($accessibleModules['messages']))
                <a href="/admin/messages" class="nav-item {{ request()->is('admin/messages*') ? 'active' : '' }}">
                    <i class="fa-solid fa-envelope"></i><span>Messages</span>
                </a>
                @endif
                
                @if(isset($accessibleModules['settings']))
                <a href="{{ route('admin.settings') }}" class="nav-item {{ request()->is('admin/settings*') ? 'active' : '' }}">
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
                            <i class="fa-solid fa-bullhorn"></i>
                            Announcements
                        </h1>
                        <p class="page-subtitle">Share important updates and news with NU Lipa alumni</p>
                    </div>
                    
                    <div class="header-actions">
                        @if (!request()->routeIs('announcements.archived'))
                            <a href="{{ route('announcements.create') }}" class="btn btn-primary">
                                <i class="fa-solid fa-plus"></i> 
                                <span>Add New</span>
                            </a>
                            
                            <!-- Filter Buttons - Only show on active announcements page -->
                            <div class="filter-buttons" style="display: flex; gap: 0.5rem;">
                                <a href="{{ route('announcements.index', ['filter' => 'all']) }}" 
                                class="btn btn-sm {{ ($filter ?? 'all') === 'all' ? 'btn-primary' : 'btn-secondary' }}"
                                style="padding: 0.5rem 1rem; font-size: 0.875rem;">
                                    All
                                </a>
                                <a href="{{ route('announcements.index', ['filter' => 'active']) }}" 
                                class="btn btn-sm {{ ($filter ?? 'all') === 'active' ? 'btn-primary' : 'btn-secondary' }}"
                                style="padding: 0.5rem 1rem; font-size: 0.875rem;">
                                    Published
                                </a>
                                <a href="{{ route('announcements.index', ['filter' => 'scheduled']) }}" 
                                class="btn btn-sm {{ ($filter ?? 'all') === 'scheduled' ? 'btn-primary' : 'btn-secondary' }}"
                                style="padding: 0.5rem 1rem; font-size: 0.875rem;">
                                    Scheduled
                                </a>
                            </div>
                        @endif
                        
                        <a id="archiveToggleBtn"
                        href="{{ route('announcements.archived') }}"
                        class="btn btn-secondary archived-toggle">
                            <i class="fa-solid fa-box-archive"></i> 
                            <span class="btn-text">Archived</span>
                        </a>
                    </div>

                </div>
            </header>

            <!-- Stats Overview -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon-wrapper">
                        <div class="stat-icon">
                            <i class="fa-solid fa-bullhorn"></i>
                        </div>
                    </div>
                    <div class="stat-info">
                        <span class="stat-value">{{ $totalAnnouncements ?? 0 }}</span>
                        <span class="stat-label">Total Announcements</span>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon-wrapper">
                        <div class="stat-icon active">
                            <i class="fa-solid fa-check-circle"></i>
                        </div>
                    </div>
                    <div class="stat-info">
                        <span class="stat-value">{{ $activeAnnouncements ?? 0 }}</span>
                        <span class="stat-label">Active</span>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon-wrapper">
                        <div class="stat-icon views">
                            <i class="fa-regular fa-calendar"></i>
                        </div>
                    </div>
                    <div class="stat-info">
                        <span class="stat-value">{{ $scheduledAnnouncements ?? 0 }}</span>
                        <span class="stat-label">Scheduled</span>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon-wrapper">
                        <div class="stat-icon archived">
                            <i class="fa-solid fa-clock-rotate-left"></i>
                        </div>
                    </div>
                    <div class="stat-info">
                        <span class="stat-value">{{ $archivedAnnouncements ?? 0 }}</span>
                        <span class="stat-label">Archived</span>
                    </div>
                </div>
                
            </div>

            <!-- Announcements Card Grid -->
            <div class="announcements-grid">
                @forelse ($announcements as $announcement)
                    <article class="announcement-card" data-announcement-id="{{ $announcement->id }}">
                        <div class="announcement-card-wrapper">
                            <div class="announcement-card-header">
                                <div class="announcement-status-badge {{ (int) $announcement->status === 0 ? 'archived' : 'active' }}">
                                    <i class="fa-solid fa-circle"></i>
                                    <span>{{ (int) $announcement->status === 0 ? 'Archived' : 'Active' }}</span>
                                </div>
                                
                                @if ($announcement->scheduled_post_at && $announcement->scheduled_post_at->timestamp > now()->timestamp && (int) $announcement->status !== 0)
                                    <div class="announcement-scheduled-badge">
                                        <i class="fa-regular fa-clock"></i>
                                        <span>Scheduled</span>
                                    </div>
                                @endif

                            </div>
                            
                            <div class="announcement-card-body">
                                <div class="announcement-content">
                                    <h3 class="announcement-title">{{ $announcement->title }}</h3>
                                    <p class="announcement-description">{{ Str::limit($announcement->announcement_description, 150) }}</p>
                                </div>
                                
                                <!-- Date Meta -->
                                <div class="announcement-dates">
                                    @php
                                        $hasScheduled = $announcement->scheduled_post_at !== null;
                                        $isScheduledFuture = $hasScheduled && $announcement->scheduled_post_at->timestamp > now()->timestamp;
                                        $isPublished = $hasScheduled && !$isScheduledFuture;
                                        
                                        // Determine which date to show
                                        if ($isPublished) {
                                            // Was scheduled, now published - show scheduled date as published date
                                            $displayDate = $announcement->scheduled_post_at;
                                            $dateLabel = 'Published';
                                        } elseif (!$hasScheduled) {
                                            // Never scheduled - show date_posted as published
                                            $displayDate = $announcement->date_posted ?? $announcement->created_at;
                                            $dateLabel = 'Published';
                                        } else {
                                            // Scheduled for future - show when it was created/posted to system
                                            $displayDate = $announcement->date_posted ?? $announcement->created_at;
                                            $dateLabel = 'Posted';
                                        }
                                    @endphp
                                    
                                    <div class="date-item">
                                        <i class="fa-regular fa-calendar"></i>
                                        <span>{{ $dateLabel }}: {{ $displayDate->format('M d, Y') }}</span>
                                    </div>
                                    
                                    {{-- Show countdown ONLY if scheduled for the future --}}
                                    @if ($isScheduledFuture)
                                        <div class="date-item scheduled" 
                                            id="countdown-{{ $announcement->id }}" 
                                            data-target-utc="{{ $announcement->scheduled_post_at->toIso8601String() }}"
                                            data-published-date="{{ $announcement->scheduled_post_at->format('M d, Y \a\t h:i A') }}">
                                            <i class="fa-solid fa-hourglass-half"></i>
                                            <span>Posts in: <span class="countdown-text" style="font-weight: 600;">Loading...</span></span>
                                        </div>
                                    @endif
                                </div>
                                
                                <!-- Attachments Preview -->
                                <div class="announcement-attachments">
                                    <span class="attachments-label">
                                        <i class="fa-regular fa-images"></i> 
                                        {{ $announcement->images->count() }} attachment(s)
                                    </span>
                                    <div class="attachment-thumbnails">
                                        @forelse ($announcement->images->take(3) as $attachment)
                                            @php
                                                $path = $attachment->image_path;
                                                $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));
                                                $imageExtensions = ['jpg', 'jpeg', 'png', 'gif', 'svg', 'webp'];
                                            @endphp
                                            @if (in_array($extension, $imageExtensions))
                                                <div class="attachment-thumb-wrapper">
                                                    <img src="{{ $attachment->image_url }}" 
                                                         alt="Announcement attachment" 
                                                         class="attachment-thumb"
                                                         onclick="openModal(this.src)">
                                                </div>
                                            @endif
                                        @empty
                                            <div class="attachment-thumb-wrapper">
                                                <div class="attachment-thumb placeholder">
                                                    <i class="fa-regular fa-image"></i>
                                                </div>
                                            </div>
                                        @endforelse
                                        @if ($announcement->images->count() > 3)
                                            <div class="attachment-more" onclick="openModal('{{ $announcement->images->first()->image_url ?? '' }}')">
                                                <span>+{{ $announcement->images->count() - 3 }}</span>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            
                            <div class="announcement-card-footer">
                                <div class="announcement-analytics">
                                    <div class="analytics-item">
                                        <i class="fa-regular fa-eye"></i>
                                        <span>Views: {{ $announcement->views ?? 0 }}</span>
                                    </div>
                                </div>
                                
                                <div class="announcement-actions">
                                    @if ((int) $announcement->status === 0)
                                        {{-- Archived: Show Restore + Permanent Delete --}}
                                        <form action="{{ route('announcements.restore', $announcement->id) }}" 
                                            method="POST" class="inline-form">
                                            @csrf @method('PUT')
                                            <button type="submit" class="btn-action btn-restore" title="Restore">
                                                <i class="fa-solid fa-rotate-left"></i>
                                            </button>
                                        </form>
                                        
                                        {{-- ONLY show permanent delete if archived --}}
                                        <form action="{{ route('announcements.permanent-delete', $announcement->id) }}" 
                                            method="POST" class="inline-form">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="btn-action" style="background:#fee; color:#ef4444;" title="Delete Permanently">
                                                <i class="fa-solid fa-trash-can"></i>
                                            </button>
                                        </form>
                                    @else
                                        {{-- Active: Show Edit + Archive --}}
                                        <a href="{{ route('announcements.edit', $announcement->id) }}" class="btn-action btn-edit" title="Edit">
                                            <i class="fa-solid fa-pen-to-square"></i>
                                        </a>
                                        <form action="{{ route('announcements.destroy', $announcement->id) }}" 
                                            method="POST" class="inline-form">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="btn-action btn-archive" title="Archive">
                                                <i class="fa-solid fa-box-archive"></i>
                                            </button>
                                        </form>
                                    @endif
                                </div>

                            </div>
                        </div>
                    </article>
                @empty
                    <div class="empty-state full-width">
                        <div class="empty-icon-wrapper">
                            <div class="empty-icon">
                                <i class="fa-solid fa-bullhorn"></i>
                            </div>
                        </div>
                        <h3 class="empty-title">No announcements found</h3>
                        <p class="empty-description">
                            @if (request()->routeIs('announcements.archived'))
                                There are no archived announcements at the moment.
                            @else
                                Start sharing important updates and news with your alumni community.
                            @endif
                        </p>
                        @if (!request()->routeIs('announcements.archived'))
                            <a href="{{ route('announcements.create') }}" class="btn btn-primary btn-lg">
                                <i class="fa-solid fa-plus"></i> 
                                <span>Create First Announcement</span>
                            </a>
                        @endif
                    </div>
                @endforelse
            </div>

            <!-- Pagination -->
            @if ($announcements->hasPages())
            <div class="pagination-wrapper">
                {{ $announcements->links() }}
            </div>
            @endif
        </main>
    </div>

    <!-- Image Modal -->
    <div id="imageModal" class="modal-overlay" onclick="closeModal()">
        <div class="modal-content-wrapper">
            <button class="modal-close" onclick="closeModal()" title="Close">
                <i class="fa-solid fa-xmark"></i>
            </button>
            <img id="enlargedImage" class="modal-image" src="" alt="Enlarged announcement image">
        </div>
    </div>

    <!-- Warning Modal -->
    <div id="warningModal" class="warning-modal-overlay">
        <div class="warning-modal">
            <div class="warning-modal-icon" id="warningModalIcon">
                <i class="fa-solid fa-triangle-exclamation"></i>
            </div>
            <div class="warning-modal-content">
                <h3 class="warning-modal-title" id="warningModalTitle">Confirm Action</h3>
                <p class="warning-modal-message" id="warningModalMessage">Are you sure you want to proceed with this action?</p>
            </div>
            <div class="warning-modal-actions">
                <button class="btn btn-secondary" id="warningModalCancel">
                    <i class="fa-solid fa-xmark"></i> Cancel
                </button>
                <button class="btn btn-danger" id="warningModalConfirm">
                    <i class="fa-solid fa-check"></i> Confirm
                </button>
            </div>
        </div>
    </div>

    <script>
// Mobile menu toggle
function toggleMobileMenu() {
    const sidebar = document.getElementById('adminSidebar');
    const overlay = document.getElementById('mobileOverlay');
    if (sidebar) sidebar.classList.toggle('mobile-open');
    if (overlay) overlay.classList.toggle('active');
    document.body.style.overflow = sidebar && sidebar.classList.contains('mobile-open') ? 'hidden' : '';
}

// ========================================
// COUNTDOWN TIMER SYSTEM (UTC-Aware)
// ========================================

let countdownIntervals = {};

/**
 * Ensures a timestamp string is treated as UTC
 * Same pattern used in the messages module
 */
function ensureUTCTimestamp(timestamp) {
    if (!timestamp) return new Date().toISOString();
    
    if (typeof timestamp === 'string') {
        // Already has timezone indicator (Z, +08:00, etc.)
        if (timestamp.endsWith('Z') || timestamp.includes('+') || timestamp.includes('-', 10)) {
            return timestamp;
        }
        
        // Missing timezone - assume UTC by appending Z
        if (timestamp.includes('T')) {
            return timestamp + 'Z';
        }
        
        // Force UTC interpretation
        const date = new Date(timestamp + 'Z');
        return date.toISOString();
    }
    
    return new Date(timestamp).toISOString();
}

function startCountdown(element) {
    const targetUtc = element.getAttribute('data-target-utc');
    const publishedDate = element.getAttribute('data-published-date');
    const countdownText = element.querySelector('.countdown-text');
    
    if (!targetUtc || !countdownText) {
        console.log('Countdown skipped - missing data:', element.id, {targetUtc, countdownText});
        return;
    }
    
    // Use the same UTC pattern as messages module
    const utcTimestamp = ensureUTCTimestamp(targetUtc);
    const targetDate = new Date(utcTimestamp);
    const targetTimeMs = targetDate.getTime();
    
    console.log('Starting countdown for:', element.id);
    console.log('  UTC timestamp:', utcTimestamp);
    console.log('  Local target:', targetDate.toString());
    console.log('  Timezone:', Intl.DateTimeFormat().resolvedOptions().timeZone);
    
    // Clear any existing interval
    if (countdownIntervals[element.id]) {
        clearInterval(countdownIntervals[element.id]);
    }
    
    function updateCountdown() {
        const now = Date.now();
        const distance = targetTimeMs - now;
        
        if (distance < 0) {
            clearInterval(countdownIntervals[element.id]);
            delete countdownIntervals[element.id];
            
            const parentSpan = countdownText.parentElement;
            parentSpan.innerHTML = 'Published: ' + publishedDate;
            
            const icon = element.querySelector('.fa-hourglass-half');
            if (icon) {
                icon.className = 'fa-solid fa-check-circle';
                icon.style.color = '#10b981';
            }
            
            element.classList.remove('scheduled');
            element.classList.add('published');
            
            const card = element.closest('.announcement-card');
            if (card) {
                const scheduledBadge = card.querySelector('.announcement-scheduled-badge');
                if (scheduledBadge) {
                    scheduledBadge.style.display = 'none';
                }
            }
            
            console.log('Countdown finished for:', element.id);
            return;
        }
        
        const days = Math.floor(distance / (1000 * 60 * 60 * 24));
        const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
        const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
        const seconds = Math.floor((distance % (1000 * 60)) / 1000);
        
        let displayText = '';
        if (days > 0) {
            displayText = days + 'd ' + hours + 'h ' + minutes + 'm ' + seconds + 's';
        } else if (hours > 0) {
            displayText = hours + 'h ' + minutes + 'm ' + seconds + 's';
        } else if (minutes > 0) {
            displayText = minutes + 'm ' + seconds + 's';
        } else {
            displayText = seconds + 's';
        }
        
        countdownText.textContent = displayText;
    }
    
    updateCountdown();
    countdownIntervals[element.id] = setInterval(updateCountdown, 1000);
}

function initializeCountdowns() {
    const countdownElements = document.querySelectorAll('[id^="countdown-"]');
    console.log('Found countdown elements:', countdownElements.length);
    
    countdownElements.forEach(element => {
        const targetUtc = element.getAttribute('data-target-utc');
        console.log('Element:', element.id, 'UTC:', targetUtc);
        if (targetUtc) {
            startCountdown(element);
        }
    });
}

window.addEventListener('beforeunload', function() {
    Object.keys(countdownIntervals).forEach(key => {
        clearInterval(countdownIntervals[key]);
    });
    countdownIntervals = {};
});

// ========================================
// WARNING MODAL SYSTEM
// ========================================

document.addEventListener('DOMContentLoaded', function() {
    // Initialize countdowns
    initializeCountdowns();
    
    // Modal elements
    const warningOverlay = document.getElementById('warningModal');
    const warningTitle = document.getElementById('warningModalTitle');
    const warningMessage = document.getElementById('warningModalMessage');
    const warningIcon = document.getElementById('warningModalIcon');
    const confirmBtn = document.getElementById('warningModalConfirm');
    const cancelBtn = document.getElementById('warningModalCancel');
    
    let pendingForm = null;
    
    function closeWarningModal() {
        if (warningOverlay) warningOverlay.classList.remove('active');
        document.body.style.overflow = '';
        pendingForm = null;
    }
    
    if (cancelBtn) cancelBtn.addEventListener('click', closeWarningModal);
    
    if (warningOverlay) {
        warningOverlay.addEventListener('click', function(e) {
            if (e.target === warningOverlay) closeWarningModal();
        });
    }
    
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && warningOverlay && warningOverlay.classList.contains('active')) {
            closeWarningModal();
        }
    });
    
    if (confirmBtn) {
        confirmBtn.addEventListener('click', function() {
            if (pendingForm) pendingForm.submit();
            closeWarningModal();
        });
    }
    
    function showWarningModal(config) {
        const {
            title = 'Confirm Action',
            message = 'Are you sure?',
            iconType = 'warning',
            confirmText = 'Confirm',
            confirmClass = 'btn-danger'
        } = config;
        
        if (!warningOverlay || !warningTitle || !warningMessage || !warningIcon || !confirmBtn) return;
        
        warningTitle.textContent = title;
        warningMessage.innerHTML = message;
        warningIcon.className = 'warning-modal-icon ' + iconType;
        
        const iconElement = warningIcon.querySelector('i');
        if (iconElement) {
            if (iconType === 'danger') {
                iconElement.className = 'fa-solid fa-triangle-exclamation';
            } else if (iconType === 'success') {
                iconElement.className = 'fa-solid fa-circle-question';
            } else {
                iconElement.className = 'fa-solid fa-triangle-exclamation';
            }
        }
        
        confirmBtn.className = 'btn ' + confirmClass;
        confirmBtn.innerHTML = '<i class="fa-solid fa-check"></i> ' + confirmText;
        
        warningOverlay.classList.add('active');
        document.body.style.overflow = 'hidden';
        confirmBtn.focus();
    }
    
    document.querySelectorAll('.inline-form').forEach(function(form) {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const submitButton = form.querySelector('button[type="submit"]');
            if (!submitButton) return;
            
            const isArchive = submitButton.classList.contains('btn-archive');
            const isRestore = submitButton.classList.contains('btn-restore');
            const hasTrashIcon = submitButton.querySelector('.fa-trash-can') !== null;
            
            pendingForm = form;
            
            if (isArchive) {
                showWarningModal({
                    title: 'Archive Announcement',
                    message: 'Are you sure you want to <strong>archive</strong> this announcement?<br><small>It will be moved to the archived section and hidden from alumni.</small>',
                    iconType: 'warning',
                    confirmText: 'Archive',
                    confirmClass: 'btn-warning'
                });
            } else if (isRestore) {
                showWarningModal({
                    title: 'Restore Announcement',
                    message: 'Are you sure you want to <strong>restore</strong> this announcement?<br><small>It will be moved back to active announcements and visible to alumni.</small>',
                    iconType: 'success',
                    confirmText: 'Restore',
                    confirmClass: 'btn-success'
                });
            } else if (hasTrashIcon) {
                showWarningModal({
                    title: 'Delete Permanently',
                    message: '<strong style="color: #ef4444;">⚠️ Warning: This action cannot be undone!</strong><br><br>Are you absolutely sure you want to <strong>permanently delete</strong> this announcement?<br><small>All associated data, images, and attachments will be permanently removed.</small>',
                    iconType: 'danger',
                    confirmText: 'Delete Permanently',
                    confirmClass: 'btn-danger'
                });
            }
        });
    });
    
    // Archive toggle button logic
    const btn = document.getElementById('archiveToggleBtn');
    if (btn) {
        const archivedPath = new URL(btn.href).pathname.replace(/\/$/, '');
        const currentPath = window.location.pathname.replace(/\/$/, '');
        if (currentPath === archivedPath) {
            btn.classList.add('active');
            btn.innerHTML = '<i class="fa-solid fa-list"></i> <span class="btn-text">Active Announcements</span>';
            btn.href = '{{ route('announcements.index') }}';
        }
    }
    
    // Make attachment thumbnails clickable
    document.querySelectorAll(".attachment-thumb").forEach(function(img) {
        if (!img.classList.contains('placeholder')) {
            img.style.cursor = "zoom-in";
            img.addEventListener('click', function() {
                openModal(this.src);
            });
        }
    });
    
    // Close sidebar on nav item click (mobile)
    document.querySelectorAll('.nav-item').forEach(function(item) {
        item.addEventListener('click', function() {
            if (window.innerWidth <= 1024) {
                toggleMobileMenu();
            }
        });
    });
});

// ========================================
// IMAGE MODAL FUNCTIONS
// ========================================

function openModal(src) {
    if (!src) return;
    const modal = document.getElementById("imageModal");
    const modalImg = document.getElementById("enlargedImage");
    if (modal && modalImg) {
        modal.style.display = "flex";
        modalImg.src = src;
        document.body.style.overflow = 'hidden';
    }
}

function closeModal() {
    const modal = document.getElementById("imageModal");
    if (modal) {
        modal.style.display = "none";
        document.body.style.overflow = '';
    }
}

document.addEventListener('keydown', function(event) {
    if (event.key === "Escape") { 
        closeModal(); 
    }
});

const imageModal = document.getElementById('imageModal');
if (imageModal) {
    imageModal.addEventListener('click', function(e) {
        if (e.target === this) closeModal();
    });
}

// Handle window resize
let resizeTimer;
window.addEventListener('resize', function() {
    clearTimeout(resizeTimer);
    resizeTimer = setTimeout(function() {
        if (window.innerWidth > 1024) {
            const sidebar = document.getElementById('adminSidebar');
            const overlay = document.getElementById('mobileOverlay');
            if (sidebar) sidebar.classList.remove('mobile-open');
            if (overlay) overlay.classList.remove('active');
            document.body.style.overflow = '';
        }
    }, 250);
});
</script>

</body>
</html>