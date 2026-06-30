<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Perks and Discounts | LumiNUs Admin</title>

    <!-- Fonts & Icons -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Stylesheets -->
    {{-- <link rel="stylesheet" href="/css/admin_dashboard.css"> --}}
    <link rel="stylesheet" href="/css/admin.css">
    <link rel="stylesheet" href="/css/perks_modern.css">
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
                    {{-- <img src="/assets/logos/NU_Lipa_Alumni_Office.png" alt="NU Lipa Alumni Affairs" class="logo-nu"> --}}
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
                <a href="/admin/announcements" class="nav-item">
                    <i class="fa-solid fa-bullhorn"></i>
                    <span>Announcements</span>
                </a>
                <a href="/admin/events" class="nav-item">
                    <i class="fa-solid fa-calendar-check"></i>
                    <span>Events</span>
                </a>
                <a href="/admin/perks" class="nav-item active">
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
                <a href="/admin/settings" class="nav-item">
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
                            <i class="fa-solid fa-gift"></i>
                            Perks & Discounts
                        </h1>
                        <p class="page-subtitle">Manage exclusive offers for NU Lipa alumni members</p>
                    </div>
                    <div class="header-actions">
                        @if (!request()->routeIs('perks.archived'))
                            <a href="{{ route('perks.create') }}" class="btn btn-primary">
                                <i class="fa-solid fa-plus"></i> 
                                <span>Add New Perk</span>
                            </a>
                        @endif
                        <a id="archiveToggleBtn"
                           href="{{ route('perks.archived') }}"
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
                            <i class="fa-solid fa-gift"></i>
                        </div>
                    </div>
                    <div class="stat-info">
                        <span class="stat-value">{{ $perks->total() }}</span>
                        <span class="stat-label">Total Perks</span>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon-wrapper">
                        <div class="stat-icon active">
                            <i class="fa-solid fa-check-circle"></i>
                        </div>
                    </div>
                    <div class="stat-info">
                        <span class="stat-value">{{ $perks->where('status', 1)->count() }}</span>
                        <span class="stat-label">Active Perks</span>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon-wrapper">
                        <div class="stat-icon archived">
                            <i class="fa-solid fa-clock-rotate-left"></i>
                        </div>
                    </div>
                    <div class="stat-info">
                        <span class="stat-value">{{ $perks->where('status', 0)->count() }}</span>
                        <span class="stat-label">Archived</span>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon-wrapper">
                        <div class="stat-icon views">
                            <i class="fa-regular fa-eye"></i>
                        </div>
                    </div>
                    <div class="stat-info">
                        <span class="stat-value">{{ $perks->sum('views') ?? 0 }}</span>
                        <span class="stat-label">Total Views</span>
                    </div>
                </div>
            </div>

            <!-- Perks Card Grid -->
            <div class="perks-grid">
                @forelse ($perks as $perk)
                    <article class="perk-card" data-perk-id="{{ $perk->id }}">
                        <div class="perk-card-wrapper">
                            <div class="perk-card-header">
                                <div class="perk-status-badge {{ $perk->status == 1 || is_null($perk->status) ? 'active' : 'archived' }}">
                                    <i class="fa-solid fa-circle"></i>
                                    <span>{{ $perk->status == 1 || is_null($perk->status) ? 'Active' : 'Archived' }}</span>
                                </div>
                                <div class="perk-id-badge">#{{ $perk->id }}</div>
                            </div>
                            
                            <div class="perk-card-body">
                                <div class="perk-content">
                                    <h3 class="perk-title">{{ $perk->title }}</h3>
                                    <p class="perk-description">{{ Str::limit($perk->description, 120) }}</p>
                                </div>
                                
                                <!-- Gallery Preview -->
                                <div class="perk-gallery-preview">
                                    <span class="gallery-label">
                                        <i class="fa-regular fa-images"></i> 
                                        {{ $perk->images->count() }} photo(s)
                                    </span>
                                    <div class="gallery-thumbnails">
                                        @if ($perk->images->isNotEmpty())
                                            @foreach ($perk->images->take(3) as $image)
                                                <div class="gallery-thumb-wrapper">
                                                    <img src="{{ $image->image_url }}" 
                                                         alt="Perk image" 
                                                         class="gallery-thumb"
                                                         onclick="openModal(this.src)"
                                                         onerror="this.src='/assets/FINAL-NULIPA.jpg'">
                                                </div>
                                            @endforeach
                                            @if ($perk->images->count() > 3)
                                                <div class="gallery-more" onclick="openModal('{{ $perk->images->first()->image_url }}')">
                                                    <span>+{{ $perk->images->count() - 3 }}</span>
                                                </div>
                                            @endif
                                        @else
                                            <div class="gallery-thumb-wrapper">
                                                <img src="{{ asset('assets/FINAL-NULIPA.jpg') }}" 
                                                     alt="No image" 
                                                     class="gallery-thumb placeholder">
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            
                            <div class="perk-card-footer">
                                <div class="perk-analytics">
                                    <div class="analytics-item">
                                        <i class="fa-regular fa-calendar"></i>
                                        <span>Until {{ $perk->valid_until->format('M d') }}</span>
                                    </div>
                                    <div class="analytics-item">
                                        <i class="fa-regular fa-eye"></i>
                                        <span>{{ $perk->views ?? 0 }}</span>
                                    </div>
                                </div>
                                <div class="perk-actions">
                                    @if ((int) $perk->status === 1 || is_null($perk->status))
                                        {{-- Active: Show Edit + Archive --}}
                                        <a href="{{ route('perks.edit', $perk) }}" class="btn-action btn-edit" title="Edit Perk">
                                            <i class="fa-solid fa-pen-to-square"></i>
                                        </a>
                                        <form action="{{ route('perks.destroy', $perk) }}" method="POST" class="inline-form">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="btn-action btn-archive" title="Archive Perk">
                                                <i class="fa-solid fa-box-archive"></i>
                                            </button>
                                        </form>
                                    @else
                                        {{-- Archived: Show Restore + Permanent Delete --}}
                                        <form action="{{ route('perks.restore', $perk) }}" method="POST" class="inline-form">
                                            @csrf @method('PUT')
                                            <button type="submit" class="btn-action btn-unarchive" title="Restore Perk">
                                                <i class="fa-solid fa-rotate-left"></i>
                                            </button>
                                        </form>
                                        
                                        {{-- Permanent Delete (only for archived) --}}
                                        <form action="{{ route('perks.permanent-delete', $perk->id) }}" 
                                            method="POST" class="inline-form"
                                            onsubmit="return confirm('Permanently delete this perk? This cannot be undone. All associated images will be removed.')">
                                            @csrf 
                                            @method('DELETE')
                                            <button type="submit" class="btn-action" style="background:#fee; color:#ef4444;" title="Delete Permanently">
                                                <i class="fa-solid fa-trash-can"></i>
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
                                <i class="fa-solid fa-gift"></i>
                            </div>
                        </div>
                        <h3 class="empty-title">No perks found</h3>
                        <p class="empty-description">
                            @if (request()->routeIs('perks.archived'))
                                There are no archived perks at the moment.
                            @else
                                Get started by creating your first alumni perk or discount offer.
                            @endif
                        </p>
                        @if (!request()->routeIs('perks.archived'))
                            <a href="{{ route('perks.create') }}" class="btn btn-primary btn-lg">
                                <i class="fa-solid fa-plus"></i> 
                                <span>Create First Perk</span>
                            </a>
                        @endif
                    </div>
                @endforelse
            </div>

            <!-- Pagination -->
            @if ($perks->hasPages())
            <div class="pagination-wrapper">
                {{ $perks->links() }}
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
            <img id="enlargedImage" class="modal-image" src="" alt="Enlarged perk image">
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
                btn.innerHTML = '<i class="fa-solid fa-list"></i> <span class="btn-text">Active Perks</span>';
                btn.href = '{{ route('perks.index') }}';
            }
        });

        // Modal functions
        function openModal(src) {
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
    </script>
</body>
</html>