<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Events | LumiNUs Admin</title>

    <!-- Fonts & Icons -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Stylesheets -->
    <link rel="stylesheet" href="/css/admin.css">
    <link rel="stylesheet" href="/css/events_modern.css">

    <!-- Leaflet CSS & JS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

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
                <a href="/admin/announcements" class="nav-item">
                    <i class="fa-solid fa-bullhorn"></i>
                    <span>Announcements</span>
                </a>
                <a href="/admin/events" class="nav-item active">
                    <i class="fa-solid fa-calendar-check"></i>
                    <span>Events</span>
                </a>
                <a href="/admin/perks" class="nav-item">
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
                            <i class="fa-solid fa-calendar-check"></i>
                            Events
                        </h1>
                        <p class="page-subtitle">Organize alumni gatherings, webinars, and hybrid meet-ups</p>
                    </div>
                    <div class="header-actions">
                        @if (!request()->routeIs('events.archived'))
                            <a href="{{ route('events.create') }}" class="btn btn-primary">
                                <i class="fa-solid fa-plus"></i> 
                                <span>Add New Event</span>
                            </a>
                        @endif
                        <a id="archiveToggleBtn"
                           href="{{ route('events.archived') }}"
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
                            <i class="fa-solid fa-calendar-check"></i>
                        </div>
                    </div>
                    <div class="stat-info">
                        <span class="stat-value">{{ $totalEvents ?? 0 }}</span>
                        <span class="stat-label">Total Events</span>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon-wrapper">
                        <div class="stat-icon active">
                            <i class="fa-solid fa-check-circle"></i>
                        </div>
                    </div>
                    <div class="stat-info">
                        <span class="stat-value">{{ $activeEvents ?? 0 }}</span>
                        <span class="stat-label">Active Events</span>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon-wrapper">
                        <div class="stat-icon archived">
                            <i class="fa-solid fa-clock-rotate-left"></i>
                        </div>
                    </div>
                    <div class="stat-info">
                        <span class="stat-value">{{ $archivedEvents ?? 0 }}</span>
                        <span class="stat-label">Archived</span>
                    </div>
                </div>
            </div>

            <!-- Events Card Grid -->
            <div class="events-grid">
                @forelse ($events as $event)
                    <article class="event-card" data-event-id="{{ $event->id }}">
                        <div class="event-card-wrapper">
                            <div class="event-card-header">
                                <div class="event-status-badge {{ ($event->status == 1 || is_null($event->status)) ? 'active' : 'archived' }}">
                                    <i class="fa-solid fa-circle"></i>
                                    <span>{{ ($event->status == 1 || is_null($event->status)) ? 'Active' : 'Archived' }}</span>
                                </div>
                                <div class="event-type-badge badge-{{ strtolower($event->event_type) }}">
                                    {{ $event->event_type }}
                                </div>
                            </div>
                            
                            <div class="event-card-body">
                                <div class="event-content">
                                    <h3 class="event-title">{{ $event->title }}</h3>
                                    @if($event->description)
                                        <p class="event-description">{{ Str::limit($event->description, 120) }}</p>
                                    @endif
                                </div>

                                <!-- Event Meta (date, platform, venue) -->
                                <div class="event-meta">
                                    <div class="meta-item">
                                        <i class="fa-regular fa-calendar"></i>
                                        <span>
                                            @if($event->start_date)
                                                {{ $event->start_date->format('M d, Y') }}
                                                @if($event->end_date) – {{ $event->end_date->format('M d, Y') }}@endif
                                            @else
                                                N/A
                                            @endif
                                        </span>
                                    </div>
                                    @if(in_array($event->event_type, ['Online','Hybrid']))
                                        <div class="meta-item">
                                            <i class="fa-solid fa-globe"></i>
                                            <span>{{ $event->platform ?: 'No platform' }}</span>
                                        </div>
                                    @endif
                                    @if(in_array($event->event_type, ['In-Person','Hybrid']))
                                        <div class="meta-item">
                                            <i class="fa-solid fa-location-dot"></i>
                                            <span>{{ $event->venue ? $event->venue->name : 'No venue' }}</span>
                                        </div>
                                    @endif
                                </div>

                                {{-- Two‑column split when a map is available --}}
                                @if(in_array($event->event_type, ['In-Person','Hybrid']) && $event->venue && $event->venue->latitude && $event->venue->longitude)
                                    <div class="event-attachments-map-split">
                                        {{-- Left: Attachments --}}
                                        <div class="event-gallery-preview split-left">
                                            <span class="gallery-label">
                                                <i class="fa-regular fa-images"></i> 
                                                {{ $event->images->count() }} photo(s)
                                            </span>
                                            <div class="gallery-thumbnails">
                                                @if ($event->images->isNotEmpty())
                                                    @foreach ($event->images->take(3) as $image)
                                                        <div class="gallery-thumb-wrapper">
                                                            <img src="{{ $image->image_url }}" 
                                                                alt="Event image" 
                                                                class="gallery-thumb"
                                                                onclick="openModal(this.src)"
                                                                onerror="this.src='/assets/FINAL-NULIPA.jpg'">
                                                        </div>
                                                    @endforeach
                                                    @if ($event->images->count() > 3)
                                                        <div class="gallery-more" onclick="openModal('{{ $event->images->first()->image_url }}')">
                                                            <span>+{{ $event->images->count() - 3 }}</span>
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

                                        {{-- Right: Map --}}
                                        <div class="event-mini-map-wrapper split-right">
                                            <div id="venue-map-{{ $event->id }}" 
                                                class="venue-mini-map" 
                                                data-lat="{{ $event->venue->latitude }}" 
                                                data-lng="{{ $event->venue->longitude }}">
                                            </div>
                                        </div>
                                    </div>
                                @else
                                    {{-- No map – attachments full width --}}
                                    <div class="event-gallery-preview">
                                        <span class="gallery-label">
                                            <i class="fa-regular fa-images"></i> 
                                            {{ $event->images->count() }} photo(s)
                                        </span>
                                        <div class="gallery-thumbnails">
                                            {{-- ... same gallery markup ... --}}
                                        </div>
                                    </div>
                                @endif
                            </div>
                            
                            <div class="event-card-footer">
                                <div class="event-analytics">
                                    <div class="analytics-item">
                                        <i class="fa-solid fa-users"></i>
                                        <span>Cap: {{ $event->max_capacity }}</span>
                                    </div>
                                    <div class="analytics-item">
                                        <i class="fa-regular fa-calendar"></i>
                                        <span>{{ $event->start_date ? $event->start_date->format('M d') : 'TBA' }}</span>
                                    </div>
                                </div>
                                <div class="event-actions">
                                    @if ((int) $event->status === 1 || is_null($event->status))
                                        <a href="{{ route('events.edit', $event) }}" class="btn-action btn-edit" title="Edit Event">
                                            <i class="fa-solid fa-pen-to-square"></i>
                                        </a>
                                        <a href="#" class="btn-action btn-manage" title="Manage Attendees">
                                            <i class="fa-solid fa-clipboard-list"></i>
                                        </a>
                                        <form action="{{ route('events.destroy', $event) }}" 
                                              method="POST" 
                                              class="inline-form"
                                              data-confirm="Archive this event?">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn-action btn-archive" title="Archive Event">
                                                <i class="fa-solid fa-box-archive"></i>
                                            </button>
                                        </form>
                                    @else
                                        <form action="{{ route('events.restore', $event) }}" 
                                              method="POST" 
                                              class="inline-form"
                                              data-confirm="Restore this event?">
                                            @csrf
                                            @method('PUT')
                                            <button type="submit" class="btn-action btn-unarchive" title="Restore Event">
                                                <i class="fa-solid fa-rotate-left"></i>
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
                                <i class="fa-solid fa-calendar-xmark"></i>
                            </div>
                        </div>
                        <h3 class="empty-title">No events found</h3>
                        <p class="empty-description">
                            @if (request()->routeIs('events.archived'))
                                There are no archived events at the moment.
                            @else
                                Start planning your first alumni event or gathering.
                            @endif
                        </p>
                        @if (!request()->routeIs('events.archived'))
                            <a href="{{ route('events.create') }}" class="btn btn-primary btn-lg">
                                <i class="fa-solid fa-plus"></i> 
                                <span>Create First Event</span>
                            </a>
                        @endif
                    </div>
                @endforelse
            </div>

            <!-- Pagination -->
            @if ($events->hasPages())
            <div class="pagination-wrapper">
                {{ $events->links() }}
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
            <img id="enlargedImage" class="modal-image" src="" alt="Enlarged event image">
        </div>
    </div>

    <!-- Confirmation Modal -->
    <div id="confirmModal" class="confirm-modal-overlay" style="display:none;">
        <div class="confirm-modal-box">
            <div class="confirm-modal-icon">⚠️</div>
            <h3 id="confirmTitle" class="confirm-modal-title">Confirm Action</h3>
            <p id="confirmMessage" class="confirm-modal-message">Are you sure you want to proceed?</p>
            <div class="confirm-modal-actions">
                <button id="confirmCancel" class="confirm-btn confirm-btn-cancel">Cancel</button>
                <button id="confirmOk" class="confirm-btn confirm-btn-ok">Confirm</button>
            </div>
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
                btn.innerHTML = '<i class="fa-solid fa-list"></i> <span class="btn-text">Active Events</span>';
                btn.href = '{{ route('events.index') }}';
            }
        });

        // Image modal functions
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

        // Confirmation Modal Logic
        document.addEventListener('DOMContentLoaded', function() {
            const confirmModal   = document.getElementById('confirmModal');
            const confirmTitle   = document.getElementById('confirmTitle');
            const confirmMessage = document.getElementById('confirmMessage');
            const confirmOk      = document.getElementById('confirmOk');
            const confirmCancel  = document.getElementById('confirmCancel');

            let pendingForm = null;

            function openConfirmModal(form, message) {
                pendingForm = form;
                confirmMessage.textContent = message || 'Are you sure?';
                confirmTitle.textContent = form.action.includes('restore') ? 'Restore Event' : 'Archive Event';
                confirmModal.style.display = 'flex';
            }

            function closeConfirmModal() {
                confirmModal.style.display = 'none';
                pendingForm = null;
            }

            // Intercept all forms with data-confirm
            document.querySelectorAll('form[data-confirm]').forEach(function (form) {
                form.addEventListener('submit', function (e) {
                    e.preventDefault();
                    openConfirmModal(this, this.dataset.confirm);
                });
            });

            confirmOk.addEventListener('click', function () {
                if (pendingForm) {
                    pendingForm.submit();
                }
                closeConfirmModal();
            });

            confirmCancel.addEventListener('click', closeConfirmModal);

            confirmModal.addEventListener('click', function (e) {
                if (e.target === confirmModal) {
                    closeConfirmModal();
                }
            });

            // Initialize Leaflet mini maps
            document.querySelectorAll('.venue-mini-map').forEach(function (mapDiv) {
                const lat = parseFloat(mapDiv.dataset.lat);
                const lng = parseFloat(mapDiv.dataset.lng);
                if (isNaN(lat) || isNaN(lng)) return;

                const map = L.map(mapDiv.id, {
                    center: [lat, lng],
                    zoom: 15,
                    scrollWheelZoom: false,
                    dragging: false,
                    zoomControl: false
                });

                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    attribution: '&copy; OpenStreetMap contributors'
                }).addTo(map);

                L.marker([lat, lng]).addTo(map);
                setTimeout(function () { map.invalidateSize(); }, 100);
            });
        });
    </script>
</body>
</html>