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
                <a href="{{ route('announcements.index') }}" class="nav-item active">
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
                        <div class="stat-icon archived">
                            <i class="fa-solid fa-clock-rotate-left"></i>
                        </div>
                    </div>
                    <div class="stat-info">
                        <span class="stat-value">{{ $archivedAnnouncements ?? 0 }}</span>
                        <span class="stat-label">Archived</span>
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
                                
                                @if ($announcement->scheduled_post_at && $announcement->scheduled_post_at->isFuture() && (int) $announcement->status !== 0)
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
                                        $isScheduledFuture = $hasScheduled && $announcement->scheduled_post_at->isFuture();
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
                                            data-target="{{ $announcement->scheduled_post_at->timestamp * 1000 }}"
                                            data-published-date="{{ $announcement->scheduled_post_at->format('M d, Y') }}">
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
                                            method="POST" class="inline-form"
                                            onsubmit="return confirm('Restore this announcement?')">
                                            @csrf @method('PUT')
                                            <button type="submit" class="btn-action btn-restore" title="Restore">
                                                <i class="fa-solid fa-rotate-left"></i>
                                            </button>
                                        </form>
                                        
                                        {{-- ONLY show permanent delete if archived --}}
                                        <form action="{{ route('announcements.permanent-delete', $announcement->id) }}" 
                                            method="POST" class="inline-form"
                                            onsubmit="return confirm('Permanently delete this announcement? This cannot be undone.')">
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
                                            method="POST" class="inline-form"
                                            onsubmit="return confirm('Archive this announcement?')">
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

    <script>
        // Mobile menu toggle
        function toggleMobileMenu() {
            const sidebar = document.getElementById('adminSidebar');
            const overlay = document.getElementById('mobileOverlay');
            sidebar.classList.toggle('mobile-open');
            overlay.classList.toggle('active');
            document.body.style.overflow = sidebar.classList.contains('mobile-open') ? 'hidden' : '';
        }

        // Archive toggle button logic
        document.addEventListener('DOMContentLoaded', function () {
            const btn = document.getElementById('archiveToggleBtn');
            if (!btn) return;
            
            const archivedPath = new URL(btn.href).pathname.replace(/\/$/, '');
            const currentPath = window.location.pathname.replace(/\/$/, '');

            if (currentPath === archivedPath) {
                btn.classList.add('active');
                btn.innerHTML = '<i class="fa-solid fa-list"></i> <span class="btn-text">Active Announcements</span>';
                btn.href = '{{ route('announcements.index') }}';
            }
        });

        // Modal functions
        function openModal(src) {
            if (!src) return;
            const modal = document.getElementById("imageModal");
            const modalImg = document.getElementById("enlargedImage");
            modal.style.display = "flex";
            modalImg.src = src;
            document.body.style.overflow = 'hidden';
        }

        function closeModal() {
            const modal = document.getElementById("imageModal");
            modal.style.display = "none";
            document.body.style.overflow = '';
        }

        document.addEventListener('keydown', function(event) {
            if (event.key === "Escape") { closeModal(); }
        });

        document.getElementById('imageModal').addEventListener('click', function(e) {
            if (e.target === this) closeModal();
        });

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

        // Make attachment thumbnails clickable
        document.addEventListener("DOMContentLoaded", function() {
            const images = document.querySelectorAll(".attachment-thumb");
            images.forEach(img => {
                if (!img.classList.contains('placeholder')) {
                    img.style.cursor = "zoom-in";
                    img.onclick = function() {
                        openModal(this.src);
                    };
                }
            });
        });

    </script>

    <script>
    (function() {
        'use strict';
        
        window.countdownIntervals = window.countdownIntervals || {};
        
        function updateCountdown(elementId, targetTimestamp, publishedDate) {
            const el = document.getElementById(elementId);
            if (!el) {
                if (window.countdownIntervals[elementId]) {
                    clearInterval(window.countdownIntervals[elementId]);
                    delete window.countdownIntervals[elementId];
                }
                return;
            }
            
            // Get current time from user's device
            const now = Date.now();
            const distance = targetTimestamp - now;
            const textSpan = el.querySelector('.countdown-text');
            
            // Time is up
            if (distance <= 0) {
                if (window.countdownIntervals[elementId]) {
                    clearInterval(window.countdownIntervals[elementId]);
                    delete window.countdownIntervals[elementId];
                }
                
                // Update date to "Published"
                const dateItem = el.closest('.announcement-dates').querySelector('.date-item:first-child span');
                if (dateItem) {
                    dateItem.textContent = 'Published: ' + publishedDate;
                }
                
                // Smooth removal
                el.style.transition = 'opacity 0.3s ease';
                el.style.opacity = '0';
                setTimeout(() => el.remove(), 300);
                
                return;
            }
            
            // Calculate time remaining
            const days = Math.floor(distance / (1000 * 60 * 60 * 24));
            const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
            const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
            const seconds = Math.floor((distance % (1000 * 60)) / 1000);
            
            // Format display
            let display = '';
            if (days > 0) display += `${days}d `;
            display += `${String(hours).padStart(2, '0')}:${String(minutes).padStart(2, '0')}:${String(seconds).padStart(2, '0')}`;
            
            if (textSpan) {
                textSpan.textContent = display;
            }
        }
        
        document.addEventListener('DOMContentLoaded', function() {
            const countdownElements = document.querySelectorAll('[id^="countdown-"]');
            
            countdownElements.forEach(el => {
                const elementId = el.id;
                // Timestamp is already in milliseconds from Laravel
                const targetTimestamp = parseInt(el.dataset.target, 10);
                const publishedDate = el.dataset.publishedDate;
                
                if (window.countdownIntervals[elementId]) {
                    clearInterval(window.countdownIntervals[elementId]);
                }
                
                // Update immediately
                updateCountdown(elementId, targetTimestamp, publishedDate);
                
                // Set interval
                window.countdownIntervals[elementId] = setInterval(() => {
                    updateCountdown(elementId, targetTimestamp, publishedDate);
                }, 1000);
            });
        });
        
        window.addEventListener('beforeunload', function() {
            if (window.countdownIntervals) {
                Object.values(window.countdownIntervals).forEach(interval => {
                    clearInterval(interval);
                });
            }
        });
    })();
    </script>

</body>
</html>