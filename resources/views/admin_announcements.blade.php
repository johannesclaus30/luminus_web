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
                                <span>Add New Announcement</span>
                            </a>
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
                                @if ($announcement->scheduled_post_at && (int) $announcement->status !== 0)
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
                                    <div class="date-item">
                                        <i class="fa-regular fa-calendar"></i>
                                        <span>Posted: {{ optional($announcement->date_posted)->format('M d, Y') ?? 'N/A' }}</span>
                                    </div>
                                    @if ($announcement->scheduled_post_at)
                                        <div class="date-item scheduled">
                                            <i class="fa-solid fa-paper-plane"></i>
                                            <span>Scheduled: {{ $announcement->scheduled_post_at->format('M d, h:i A') }}</span>
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
                                        <form action="{{ route('announcements.restore', $announcement->id) }}" 
                                              method="POST" 
                                              class="inline-form"
                                              onsubmit="return confirm('Restore this announcement?')">
                                            @csrf
                                            @method('PUT')
                                            <button type="submit" class="btn-action btn-restore" title="Restore Announcement">
                                                <i class="fa-solid fa-rotate-left"></i>
                                            </button>
                                        </form>
                                    @else
                                        <a href="{{ route('announcements.edit', $announcement->id) }}" class="btn-action btn-edit" title="Edit Announcement">
                                            <i class="fa-solid fa-pen-to-square"></i>
                                        </a>
                                        <form action="{{ route('announcements.destroy', $announcement->id) }}" 
                                              method="POST" 
                                              class="inline-form"
                                              onsubmit="return confirm('Archive this announcement?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn-action btn-archive" title="Archive Announcement">
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
</body>
</html>