<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Event | LumiNUs Admin</title>

    <!-- Fonts & Icons -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Stylesheets -->
    <link rel="stylesheet" href="/css/admin.css">
    <link rel="stylesheet" href="/css/events_modern.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>
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
                <a href="{{ route('announcements.index') }}" class="nav-item">
                    <i class="fa-solid fa-bullhorn"></i>
                    <span>Announcements</span>
                </a>
                <a href="{{ route('events.index') }}" class="nav-item active">
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
                            <i class="fa-solid fa-pen-to-square"></i>
                            Edit Event
                        </h1>
                        <p class="page-subtitle">Update event details for NU Lipa alumni</p>
                    </div>
                    
                    <div class="header-actions">
                        <a href="{{ route('events.index') }}" class="btn btn-secondary">
                            <i class="fa-solid fa-arrow-left"></i> 
                            <span>Back to Events</span>
                        </a>
                    </div>
                </div>
            </header>

            @if ($errors->any())
                <div class="upload-status status-error" style="margin-bottom: 1.5rem; padding: 1rem 1.25rem; border-radius: var(--radius-lg); background: #fee2e2; color: #ef4444; border: 1px solid #ef4444; display: flex; align-items: flex-start; gap: 0.75rem;">
                    <i class="fa-solid fa-circle-exclamation" style="margin-top: 0.125rem; flex-shrink: 0;"></i>
                    <div>
                        <strong style="display: block; margin-bottom: 0.375rem;">Please fix the following errors:</strong>
                        <ul style="margin:0; padding-left:1.25rem;">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            @endif

            <form action="{{ route('events.update', $event) }}" 
                  method="POST" 
                  enctype="multipart/form-data" 
                  id="eventForm" 
                  class="form-card">
                @csrf
                @method('PUT')
                
                <div id="deleted-media-container"></div>

                <!-- Form Guide Alert -->
                <div class="rule-alert" style="margin-bottom: 2rem;">
                    <i class="fa-solid fa-circle-info"></i>
                    <div>
                        <strong>Required fields:</strong> event title, event mode, event dates, max capacity, and description.
                        <br><strong>On-site / hybrid:</strong> fill in the venue address and pin it on the map.
                        <br><strong>Online / hybrid:</strong> choose the platform and add the platform URL if needed.
                    </div>
                </div>
                
                <div class="events-details-layout">
                    <!-- Left Panel - Main Form Fields -->
                    <div class="form-left-panel">
                        <!-- Event Title -->
                        <div class="form-group">
                            <label for="title" class="form-label">Event Title</label>
                            <input type="text" id="title" name="title" class="form-control" value="{{ old('title', $event->title) }}" required>
                            <small style="color: var(--gray-500); font-size: 0.8125rem; display: block; margin-top: 0.375rem;">Use the official event title shown to attendees.</small>
                        </div>

                        <!-- Event Mode + Max Capacity Row -->
                        <div class="form-row-2col">
                            <div class="form-group">
                                <label for="event_type" class="form-label">Event Mode</label>
                                @php
                                    $selectedEventType = old('event_type', $event->event_type);
                                @endphp
                                <select id="event_type" name="event_type" class="form-control" required>
                                    <option value="" disabled {{ $selectedEventType ? '' : 'selected' }}>Choose event mode</option>
                                    @foreach (['In-Person' => 'On-Site / Face to Face', 'Online' => 'Online / Virtual', 'Hybrid' => 'Hybrid'] as $eventValue => $eventLabel)
                                        <option value="{{ $eventValue }}" {{ $selectedEventType === $eventValue ? 'selected' : '' }}>{{ $eventLabel }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="form-group">
                                <label for="max_capacity" class="form-label">Max Capacity</label>
                                <input type="number" id="max_capacity" name="max_capacity" class="form-control" placeholder="e.g. 100" min="1" value="{{ old('max_capacity', $event->max_capacity) }}" required>
                            </div>
                        </div>

                        <!-- Event Dates Row -->
                        <div class="form-group">
                            <label class="form-label">Event Dates</label>
                            <div class="date-inputs-row">
                                <div class="date-input-group">
                                    <span class="date-label">From</span>
                                    <input type="date" name="start_date" class="form-control" value="{{ old('start_date', $event->start_date ? $event->start_date->format('Y-m-d') : '') }}" required>
                                </div>
                                <div class="date-input-group">
                                    <span class="date-label">To</span>
                                    <input type="date" name="end_date" class="form-control" value="{{ old('end_date', $event->end_date ? $event->end_date->format('Y-m-d') : '') }}" required>
                                </div>
                            </div>
                            <small style="color: var(--gray-500); font-size: 0.8125rem; display: block; margin-top: 0.375rem;">Choose the start and end dates for the event schedule.</small>
                        </div>

                        <!-- Venue Section (On-Site / Hybrid) -->
                        <div class="section-card" id="venueSection">
                            <div class="section-card-header">
                                <div>
                                    <h3 class="section-card-title"><i class="fa-solid fa-location-dot"></i> On-Site Venue</h3>
                                    <p class="section-card-subtitle">Use this for face-to-face or hybrid events. The map and address stay synced.</p>
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="venueAddress" class="form-label">Venue Address</label>
                                <input
                                    type="text"
                                    name="venue_address"
                                    id="venueAddress"
                                    class="form-control"
                                    placeholder="Start typing a location or exact address"
                                    value="{{ old('venue_address', optional($event->venue)->address) }}"
                                >
                                <small style="color: var(--gray-500); font-size: 0.8125rem; display: block; margin-top: 0.375rem;">Type a place name or exact address to search nearby results.</small>
                                <div id="venueSuggestions" class="venue-suggestions hidden-section"></div>
                            </div>

                            <div class="form-group">
                                <label for="venueName" class="form-label">Venue Name</label>
                                <input
                                    type="text"
                                    name="venue_name"
                                    id="venueName"
                                    class="form-control"
                                    placeholder="Optional venue name"
                                    value="{{ old('venue_name', optional($event->venue)->name) }}"
                                >
                                <small style="color: var(--gray-500); font-size: 0.8125rem; display: block; margin-top: 0.375rem;">Please set the actual name of the event venue. This will not auto-fill.</small>
                            </div>

                            <input type="hidden" name="venue_latitude" id="venueLatitude" value="{{ old('venue_latitude', optional($event->venue)->latitude) }}">
                            <input type="hidden" name="venue_longitude" id="venueLongitude" value="{{ old('venue_longitude', optional($event->venue)->longitude) }}">

                            <div id="venueMap" class="venue-map"></div>
                            <div class="venue-status" id="venueStatus">Search an address or click the map to pin the venue.</div>
                        </div>

                        <!-- Online Section (Online / Hybrid) -->
                        <div class="section-card hidden-section" id="onlineSection">
                            <div class="section-card-header">
                                <div>
                                    <h3 class="section-card-title"><i class="fa-solid fa-globe"></i> Online Details</h3>
                                    <p class="section-card-subtitle">Use this for virtual events. Hidden when the event is on-site only.</p>
                                </div>
                            </div>

                            <div class="form-row-2col">
                                <div class="form-group">
                                    <label for="platformField" class="form-label">Platform</label>
                                    <input type="text" name="platform" id="platformField" class="form-control" placeholder="Zoom, Google Meet, Facebook Live, etc." value="{{ old('platform', $event->platform) }}">
                                    <small style="color: var(--gray-500); font-size: 0.8125rem; display: block; margin-top: 0.375rem;">Select the online service or channel where the event will happen.</small>
                                </div>

                                <div class="form-group">
                                    <label for="platformUrlField" class="form-label">Platform URL</label>
                                    <input type="url" name="platform_url" id="platformUrlField" class="form-control" placeholder="https://..." value="{{ old('platform_url', $event->platform_url) }}">
                                    <small style="color: var(--gray-500); font-size: 0.8125rem; display: block; margin-top: 0.375rem;">Add the meeting link, livestream URL, or event page if available.</small>
                                </div>
                            </div>
                        </div>

                        <!-- Description -->
                        <div class="form-group">
                            <label for="description" class="form-label">Content / Description</label>
                            <textarea id="description" name="description" class="form-control textarea-description" required>{{ old('description', $event->description) }}</textarea>
                            <small style="color: var(--gray-500); font-size: 0.8125rem; display: block; margin-top: 0.375rem;">Provide the event description, agenda, or important instructions for attendees.</small>
                        </div>
                    </div>

                    <!-- Right Panel - Attachments + Actions -->
                    <div class="form-right-panel">
                        <div class="attachments-card">
                            <h3 class="attachments-title">
                                <i class="fa-solid fa-paperclip"></i> Attachments
                                <span class="attachments-count">(Max 5)</span>
                            </h3>
                            
                            <!-- Existing Attachments -->
                            @if($event->exists && $event->images->count() > 0)
                                <div style="margin-bottom: 1rem;">
                                    <h4 style="font-size: 0.8125rem; font-weight: 600; color: var(--gray-600); margin-bottom: 0.625rem; text-align: left;">
                                        Current Attachments 
                                        <span style="font-weight: 400; color: var(--gray-400);">(Click ✕ to mark for removal)</span>
                                    </h4>
                                </div>
                            @endif

                            <div id="attachment-preview-container" class="attachment-grid">
                                @if($event->exists && $event->images)
                                    @foreach($event->images as $image)
                                        <div class="attachment-item existing-image" id="existing-attachment-{{ $image->id }}">
                                            <img src="{{ $image->image_url }}" class="attachment-img">
                                            <button type="button" class="remove-attachment-btn" onclick="window.removeExistingImage({{ $image->id }})" title="Mark for removal">
                                                <i class="fa-solid fa-xmark"></i>
                                            </button>
                                        </div>
                                    @endforeach
                                @endif
                            </div>
                            
                            <input type="file" name="images[]" id="attachmentInput" accept="image/jpeg,image/png,image/webp" multiple style="display: none;">

                            <button type="button" class="add-attachment-btn" id="triggerFileInput">
                                <i class="fa-solid fa-cloud-arrow-up"></i>
                                Add More Attachments
                            </button>
                            <p class="file-limit-info">Max 5 files total • 5MB each • JPG, PNG, WEBP</p>
                        </div>

                        <!-- Form Actions -->
                        <div class="form-actions">
                            <a href="{{ route('events.index') }}" class="btn btn-secondary">Cancel</a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fa-solid fa-save"></i>
                                <span>Update Event</span>
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </main>
    </div>

    <!-- Warning Modal -->
    <div id="warningModal" class="warning-modal-overlay">
        <div class="warning-modal">
            <div class="warning-modal-icon" id="warningModalIcon">
                <i class="fa-solid fa-triangle-exclamation"></i>
            </div>
            <div class="warning-modal-content">
                <h3 class="warning-modal-title" id="warningModalTitle">Confirm Action</h3>
                <p class="warning-modal-message" id="warningModalMessage">Are you sure you want to proceed?</p>
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

    <!-- Leaflet JS -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
    
    <script>
    // ========================================
    // Mobile Menu Toggle
    // ========================================
    function toggleMobileMenu() {
        const sidebar = document.getElementById('adminSidebar');
        const overlay = document.getElementById('mobileOverlay');
        sidebar.classList.toggle('mobile-open');
        overlay.classList.toggle('active');
        document.body.style.overflow = sidebar.classList.contains('mobile-open') ? 'hidden' : '';
    }

    document.querySelectorAll('.nav-item').forEach(item => {
        item.addEventListener('click', function() {
            if (window.innerWidth <= 1024) toggleMobileMenu();
        });
    });

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

    // ========================================
    // Attachment Handling
    // ========================================
    let activeExistingCount = {{ $event->exists ? $event->images->count() : 0 }};

    window.removeExistingImage = function(imageId) {
        window.showWarningModal({
            title: 'Remove Attachment',
            message: 'Are you sure you want to <strong>remove</strong> this attachment?<br><small>It will be permanently deleted when you save the changes.</small>',
            iconType: 'warning',
            confirmText: 'Remove',
            confirmClass: 'btn-warning',
            onConfirm: function() {
                const item = document.getElementById(`existing-attachment-${imageId}`);
                if (item) {
                    item.style.opacity = '0.3';
                    item.style.pointerEvents = 'none';
                }
                const deletedContainer = document.getElementById('deleted-media-container');
                const hiddenInput = document.createElement('input');
                hiddenInput.type = 'hidden';
                hiddenInput.name = 'deleted_media[]';
                hiddenInput.value = imageId;
                deletedContainer.appendChild(hiddenInput);
                activeExistingCount--;
            }
        });
    };

    document.addEventListener('DOMContentLoaded', function() {
        const fileInput = document.getElementById('attachmentInput');
        const triggerBtn = document.getElementById('triggerFileInput');
        const previewContainer = document.getElementById('attachment-preview-container');
        const MAX_FILES = 5;
        const MAX_SIZE = 5 * 1024 * 1024;

        const eventTypeField = document.getElementById('event_type');
        const venueAddressField = document.getElementById('venueAddress');
        const venueNameField = document.getElementById('venueName');
        const venueLatitudeField = document.getElementById('venueLatitude');
        const venueLongitudeField = document.getElementById('venueLongitude');
        const venueSuggestions = document.getElementById('venueSuggestions');
        const venueSection = document.getElementById('venueSection');
        const onlineSection = document.getElementById('onlineSection');
        const platformField = document.getElementById('platformField');
        const platformUrlField = document.getElementById('platformUrlField');
        const venueStatus = document.getElementById('venueStatus');
        const venueMapElement = document.getElementById('venueMap');
        const defaultCenter = [14.5995, 120.9842];
        const existingLatitude = parseFloat(venueLatitudeField?.value || '');
        const existingLongitude = parseFloat(venueLongitudeField?.value || '');
        const existingAddress = (venueAddressField?.value || '').trim();
        let venueMap = null;
        let venueMarker = null;
        let addressDebounce = null;
        let suggestionDebounce = null;
        let lastSuggestionQuery = '';

        // ========================================
        // WARNING MODAL SYSTEM
        // ========================================
        const warningOverlay = document.getElementById('warningModal');
        const warningTitle = document.getElementById('warningModalTitle');
        const warningMessage = document.getElementById('warningModalMessage');
        const warningIcon = document.getElementById('warningModalIcon');
        const confirmBtn = document.getElementById('warningModalConfirm');
        const modalCancelBtn = document.getElementById('warningModalCancel');

        let pendingCallback = null;

        function closeWarningModal() {
            warningOverlay.classList.remove('active');
            document.body.style.overflow = '';
            pendingCallback = null;
        }

        modalCancelBtn.addEventListener('click', closeWarningModal);
        warningOverlay.addEventListener('click', function(e) { if (e.target === warningOverlay) closeWarningModal(); });
        document.addEventListener('keydown', function(e) { if (e.key === 'Escape' && warningOverlay.classList.contains('active')) closeWarningModal(); });
        confirmBtn.addEventListener('click', function() { if (pendingCallback) pendingCallback(); closeWarningModal(); });

        window.showWarningModal = function(config) {
            const {
                title = 'Confirm Action',
                message = 'Are you sure?',
                iconType = 'warning',
                confirmText = 'Confirm',
                confirmClass = 'btn-danger',
                onConfirm = null,
                hideCancel = false
            } = config;

            warningTitle.textContent = title;
            warningMessage.innerHTML = message;
            warningIcon.className = 'warning-modal-icon ' + iconType;
            const iconElement = warningIcon.querySelector('i');
            if (iconType === 'danger') iconElement.className = 'fa-solid fa-triangle-exclamation';
            else if (iconType === 'success') iconElement.className = 'fa-solid fa-circle-question';
            else iconElement.className = 'fa-solid fa-triangle-exclamation';

            confirmBtn.className = 'btn ' + confirmClass;
            confirmBtn.innerHTML = '<i class="fa-solid fa-check"></i> ' + confirmText;

            modalCancelBtn.style.display = hideCancel ? 'none' : 'inline-flex';

            pendingCallback = onConfirm;
            warningOverlay.classList.add('active');
            document.body.style.overflow = 'hidden';
            confirmBtn.focus();
        };

        // Cancel button confirmation
        const cancelLink = document.querySelector('.form-actions .btn-secondary');
        if (cancelLink) {
            cancelLink.addEventListener('click', function(e) {
                e.preventDefault();
                const href = this.getAttribute('href');
                window.showWarningModal({
                    title: 'Discard Changes',
                    message: 'Are you sure you want to <strong>cancel</strong>?<br><small>All unsaved changes will be lost.</small>',
                    iconType: 'warning',
                    confirmText: 'Discard',
                    confirmClass: 'btn-warning',
                    onConfirm: function() { window.location.href = href; }
                });
            });
        }

        const setSectionVisibility = () => {
            const mode = eventTypeField?.value;
            const showVenue = ['In-Person', 'Hybrid'].includes(mode);
            const showOnline = ['Online', 'Hybrid'].includes(mode);
            venueSection?.classList.toggle('hidden-section', !showVenue);
            onlineSection?.classList.toggle('hidden-section', !showOnline);
            if (venueAddressField) { venueAddressField.required = showVenue; venueAddressField.disabled = !showVenue; }
            if (venueNameField) venueNameField.disabled = !showVenue;
            if (platformField) { platformField.required = showOnline; platformField.disabled = !showOnline; }
            if (platformUrlField) platformUrlField.disabled = !showOnline;
            if (venueMapElement) venueMapElement.style.display = showVenue ? 'block' : 'none';
            if (!showVenue) { venueSuggestions?.classList.add('hidden-section'); venueSuggestions.innerHTML = ''; }
            if (venueStatus) {
                venueStatus.textContent = showVenue ? 'Search an address or click the map to pin the venue.' : 'Venue details are hidden for this event mode.';
            }
        };

        if (triggerBtn && fileInput) {
            triggerBtn.addEventListener('click', function(e) { e.preventDefault(); fileInput.click(); });
        }

        fileInput.addEventListener('change', function(event) {
            const files = Array.from(event.target.files);
            const totalCount = activeExistingCount + files.length;

            if (totalCount > MAX_FILES) {
                window.showWarningModal({
                    title: 'Too Many Files',
                    message: `You can only have up to <strong>${MAX_FILES}</strong> images total.<br><small>You currently have ${activeExistingCount} active existing image(s).</small>`,
                    iconType: 'warning',
                    confirmText: 'OK',
                    confirmClass: 'btn-warning',
                    hideCancel: true
                });
                this.value = "";
                return;
            }

            document.querySelectorAll('.new-image-preview').forEach(el => el.remove());

            let hasError = false;
            files.forEach((file) => {
                if (hasError) return;
                const ext = file.name.split('.').pop().toLowerCase();
                if (!['jpg', 'jpeg', 'png', 'webp'].includes(ext)) {
                    window.showWarningModal({
                        title: 'Invalid File Type',
                        message: `<strong>${file.name}</strong> is not a supported format.<br><small>Please use JPG, PNG, or WEBP only.</small>`,
                        iconType: 'warning',
                        confirmText: 'OK',
                        confirmClass: 'btn-warning',
                        hideCancel: true
                    });
                    hasError = true;
                    return;
                }
                if (file.size > MAX_SIZE) {
                    window.showWarningModal({
                        title: 'File Too Large',
                        message: `<strong>${file.name}</strong> exceeds the 5MB limit.`,
                        iconType: 'warning',
                        confirmText: 'OK',
                        confirmClass: 'btn-warning',
                        hideCancel: true
                    });
                    hasError = true;
                    return;
                }
                const reader = new FileReader();
                reader.onload = function(e) {
                    const wrapper = document.createElement('div');
                    wrapper.className = 'attachment-item new-image-preview';
                    wrapper.innerHTML = `<img src="${e.target.result}" class="attachment-img" style="border: 2px dashed var(--nu-gold);"><button type="button" class="remove-attachment-btn" onclick="this.parentElement.remove()" title="Remove new image"><i class="fa-solid fa-xmark"></i></button>`;
                    previewContainer.appendChild(wrapper);
                };
                reader.readAsDataURL(file);
            });

            if (hasError) this.value = '';
        });

        const renderVenueSuggestions = (items) => {
            if (!venueSuggestions) return;
            venueSuggestions.innerHTML = '';
            if (!items.length) { venueSuggestions.classList.add('hidden-section'); return; }
            items.forEach((item) => {
                const row = document.createElement('div');
                row.className = 'venue-suggestion-item';
                row.textContent = item.display_name;
                row.dataset.lat = item.lat; row.dataset.lon = item.lon; row.dataset.name = item.display_name;
                row.addEventListener('mousedown', (event) => {
                    event.preventDefault();
                    venueAddressField.value = item.display_name;
                    venueNameField.value = venueNameField.value.trim() || item.display_name;
                    venueSuggestions.classList.add('hidden-section'); venueSuggestions.innerHTML = '';
                    setVenuePoint(parseFloat(item.lat), parseFloat(item.lon), false);
                    venueStatus.textContent = 'Venue selected from suggestions.';
                });
                venueSuggestions.appendChild(row);
            });
            venueSuggestions.classList.remove('hidden-section');
        };

        const searchVenueSuggestions = (query) => {
            const trimmedQuery = query.trim();
            if (!trimmedQuery || trimmedQuery.length < 2 || trimmedQuery === lastSuggestionQuery) {
                if (!trimmedQuery) { venueSuggestions?.classList.add('hidden-section'); venueSuggestions.innerHTML = ''; }
                return;
            }
            lastSuggestionQuery = trimmedQuery;
            fetch(`https://nominatim.openstreetmap.org/search?format=jsonv2&limit=6&q=${encodeURIComponent(trimmedQuery)}`)
                .then(r => r.json()).then(results => renderVenueSuggestions(results))
                .catch(() => { venueSuggestions?.classList.add('hidden-section'); });
        };

        if (venueMapElement && window.L) {
            const mapCenter = Number.isFinite(existingLatitude) && Number.isFinite(existingLongitude) ? [existingLatitude, existingLongitude] : defaultCenter;
            venueMap = L.map('venueMap').setView(mapCenter, Number.isFinite(existingLatitude) ? 16 : 11);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { maxZoom: 19, attribution: '&copy; OpenStreetMap contributors' }).addTo(venueMap);
            venueMarker = L.marker(mapCenter, { draggable: true }).addTo(venueMap);

            const setVenuePoint = (lat, lng, updateAddress = true) => {
                venueLatitudeField.value = lat.toFixed(6);
                venueLongitudeField.value = lng.toFixed(6);
                venueMarker.setLatLng([lat, lng]);
                venueMap.setView([lat, lng], 16);
                if (updateAddress) {
                    venueStatus.textContent = 'Resolving address from map pin...';
                    fetch(`https://nominatim.openstreetmap.org/reverse?format=jsonv2&lat=${lat}&lon=${lng}`)
                        .then(r => r.json()).then(data => {
                            const address = data.display_name || '';
                            if (address) { venueAddressField.value = address; venueStatus.textContent = 'Address updated from map pin.'; }
                            else venueStatus.textContent = 'Unable to resolve an address for that pin.';
                        }).catch(() => { venueStatus.textContent = 'Address lookup failed.'; });
                }
            };

            const geocodeAddress = (address) => {
                const query = address.trim();
                if (!query) return;
                venueStatus.textContent = 'Looking up the typed address...';
                fetch(`https://nominatim.openstreetmap.org/search?format=jsonv2&limit=1&q=${encodeURIComponent(query)}`)
                    .then(r => r.json()).then(results => {
                        if (!results.length) { venueStatus.textContent = 'No map result found.'; return; }
                        const place = results[0];
                        venueAddressField.value = place.display_name;
                        venueStatus.textContent = 'Map updated from typed address.';
                        setVenuePoint(parseFloat(place.lat), parseFloat(place.lon), false);
                    }).catch(() => { venueStatus.textContent = 'Address lookup failed.'; });
            };

            venueMarker.on('dragend', () => { const { lat, lng } = venueMarker.getLatLng(); setVenuePoint(lat, lng, true); });
            venueMap.on('click', (event) => { setVenuePoint(event.latlng.lat, event.latlng.lng, true); });
            venueAddressField?.addEventListener('blur', () => {
                if (['In-Person', 'Hybrid'].includes(eventTypeField?.value)) {
                    clearTimeout(addressDebounce);
                    addressDebounce = setTimeout(() => geocodeAddress(venueAddressField.value), 300);
                }
            });
            venueAddressField?.addEventListener('input', () => {
                clearTimeout(suggestionDebounce);
                if (!['In-Person', 'Hybrid'].includes(eventTypeField?.value)) { venueSuggestions?.classList.add('hidden-section'); venueSuggestions.innerHTML = ''; return; }
                suggestionDebounce = setTimeout(() => searchVenueSuggestions(venueAddressField.value), 300);
            });
            venueAddressField?.addEventListener('keydown', (event) => { if (event.key === 'Enter') { event.preventDefault(); geocodeAddress(venueAddressField.value); } });
            if (Number.isFinite(existingLatitude) && Number.isFinite(existingLongitude)) {
                venueMarker.setLatLng([existingLatitude, existingLongitude]);
                venueMap.setView([existingLatitude, existingLongitude], 16);
            } else if (existingAddress) { geocodeAddress(existingAddress); }
        }

        eventTypeField?.addEventListener('change', setSectionVisibility);
        setSectionVisibility();
    });
</script>

</body>
</html>