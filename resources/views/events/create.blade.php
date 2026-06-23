@extends('layouts.admin')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/events.css') }}">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>
@endpush

@section('content')
<div class="layout-wrapper">
    <div class="admin-menu">
        <div>
            <p class="text-titles">Admin Menu</p>
            <a href="/admin/dashboard" class="admin-menu-buttons">Admin Dashboard</a>
            <a href="/admin/directory" class="admin-menu-buttons">Alumni Directory</a>
            <a href="/admin/announcements" class="admin-menu-buttons">Announcement Editor</a>
            <a href="/admin/events" class="admin-menu-current">Event Organizer</a>
            <a href="/admin/perks" class="admin-menu-buttons">Perks and Discounts</a>
            <a href="/admin/alumni_tracer" class="admin-menu-buttons">NU Alumni Tracer</a>
            <a href="/admin/messages" class="admin-menu-buttons">Messages</a>
            <a href="/admin/settings" class="admin-menu-buttons">Settings</a>
        </div>
        <a href="{{ route('admin.logout') }}" class="admin-menu-signout">Sign Out</a>
    </div>

    <div class="div-dashboard-container">
        <div class="events-create-container">
            <h2>{{ $event->exists ? 'Edit Event' : 'Add New Event' }}</h2>

            @if ($errors->any())
                <div style="color: red; margin-bottom: 15px; background: #ffeeee; padding: 10px; border-radius: 8px;">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ $event->exists ? route('events.update', $event) : route('events.store') }}" 
                  method="POST" 
                  enctype="multipart/form-data" 
                  id="eventForm">
                @csrf
                @if($event->exists) @method('PUT') @endif
                
                <div id="deleted-media-container"></div>

                <div class="form-notes">
                    <div><strong>Required:</strong> event title, event mode, event dates, max capacity, and description.</div>
                    <div><strong>On-site / hybrid:</strong> fill in the venue address and pin it on the map. Suggestions will appear as you type.</div>
                    <div><strong>Online / hybrid:</strong> choose the platform and add the platform URL if needed.</div>
                </div>
                
                <div class="events-details-layout">
                    <div class="form-left-panel">
                        <label>Event Title</label>
                        <input type="text" name="title" class="textarea-style" value="{{ old('title', $event->title) }}" required>
                        <div class="field-note">Use the official event title shown to attendees.</div>

                        <div class="location-date-row equal-columns-row">
                            <div class="location-col">
                                <label>Event Mode</label>
                                @php
                                    $selectedEventType = old('event_type', $event->event_type);
                                @endphp
                                <select name="event_type" class="textarea-style" required>
                                    <option value="" disabled {{ $selectedEventType ? '' : 'selected' }}>Choose event mode</option>
                                    @foreach (['In-Person' => 'On-Site / Face to Face', 'Online' => 'Online / Virtual', 'Hybrid' => 'Hybrid'] as $eventValue => $eventLabel)
                                        <option value="{{ $eventValue }}" {{ $selectedEventType === $eventValue ? 'selected' : '' }}>{{ $eventLabel }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="capacity-col">
                                <label>Max Capacity</label>
                                <input type="number" name="max_capacity" class="textarea-style" placeholder="e.g. 100" min="1" value="{{ old('max_capacity', $event->max_capacity) }}" required style="padding: 10px 15px !important; box-sizing: border-box !important;">
                            </div>
                        </div>
                        
                        <div class="location-date-row">
                            <div class="date-col">
                                <label>Event Date</label>
                                <div class="date-inputs-row">
                                    <p>From</p>
                                    <div class="date-input-wrapper">
                                        <input type="date" name="start_date" value="{{ old('start_date', $event->start_date ? $event->start_date->format('Y-m-d') : '') }}" required>
                                    </div>
                                    <p>To</p>
                                    <div class="date-input-wrapper">
                                        <input type="date" name="end_date" value="{{ old('end_date', $event->end_date ? $event->end_date->format('Y-m-d') : '') }}" required>
                                    </div>
                                </div>
                                <div class="field-note">Choose the start and end dates for the event schedule.</div>
                            </div>
                        </div>

                        <div class="section-card form-section venue-card" id="venueSection">
                            <div class="venue-card-header">
                                <div>
                                    <h3 class="section-card-title">On-Site Venue</h3>
                                    <p class="section-card-subtitle">Use this for face-to-face or hybrid events. The map and address stay synced.</p>
                                </div>
                            </div>

                            <div class="venue-input-wrap">
                                <label for="venueAddress">Venue Address</label>
                                <input
                                    type="text"
                                    name="venue_address"
                                    id="venueAddress"
                                    class="textarea-style"
                                    placeholder="Start typing a location or exact address"
                                    value="{{ old('venue_address', optional($event->venue)->address) }}"
                                >
                                <div class="field-note">Type a place name or exact address to search nearby results.</div>
                                <div id="venueSuggestions" class="venue-suggestions hidden-section"></div>
                            </div>

                            <label for="venueName">Venue Name</label>
                            <input
                                type="text"
                                name="venue_name"
                                id="venueName"
                                class="textarea-style"
                                placeholder="Optional venue name"
                                value="{{ old('venue_name', optional($event->venue)->name) }}"
                            >
                            <div class="field-note">Please set the actual name of the event venue. This will not auto-fill.</div>

                            <input type="hidden" name="venue_latitude" id="venueLatitude" value="{{ old('venue_latitude', optional($event->venue)->latitude) }}">
                            <input type="hidden" name="venue_longitude" id="venueLongitude" value="{{ old('venue_longitude', optional($event->venue)->longitude) }}">

                            <div id="venueMap" class="venue-map"></div>
                            <div class="venue-status" id="venueStatus">Search an address or click the map to pin the venue.</div>
                        </div>

                        <div class="section-card form-section hidden-section" id="onlineSection">
                            <div class="section-card-header">
                                <div>
                                    <h3 class="section-card-title">Online Details</h3>
                                    <p class="section-card-subtitle">Use this for virtual events. Hidden when the event is on-site only.</p>
                                </div>
                            </div>

                            <div class="location-date-row equal-columns-row">
                                <div class="location-col">
                                    <label>Platform</label>
                                    <input type="text" name="platform" id="platformField" class="textarea-style" placeholder="Zoom, Google Meet, Facebook Live, and so on" value="{{ old('platform', $event->platform) }}">
                                    <div class="field-note">Select the online service or channel where the event will happen.</div>
                                </div>

                                <div class="capacity-col">
                                    <label>Platform URL</label>
                                    <input type="url" name="platform_url" id="platformUrlField" class="textarea-style" placeholder="https://..." value="{{ old('platform_url', $event->platform_url) }}">
                                    <div class="field-note">Add the meeting link, livestream URL, or event page if available.</div>
                                </div>
                            </div>
                        </div>

                        <label>Content</label>
                        <textarea name="description" class="textarea-style textarea-description event-content" required>{{ old('description', $event->description) }}</textarea>
                        <div class="field-note">Provide the event description, agenda, or important instructions for attendees.</div>
                    </div>

                    <div class="form-right-panel">
                        <div class="attachments-card">
                            <h3 class="attachments-title">Attachments (Max 5)</h3>
                            
                            <div id="attachment-preview-container">
                                @if($event->exists && $event->images)
                                    @foreach($event->images as $image)
                                        <div class="attachment-item existing-image">
                                            <img src="{{ $image->image_url }}" class="attachment-img">
                                            <button type="button" class="remove-attachment-btn" onclick="removeExistingImage(this, {{ $image->id }})">&times;</button>
                                        </div>
                                    @endforeach
                                @endif
                            </div>
                            
                            <input type="file" name="images[]" id="attachmentInput" accept="image/*" multiple style="display: none;">

                            <button type="button" class="add-attachment-btn" id="triggerFileInput">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-paperclip"><path d="M21.44 11.05l-9.19 9.19a6 6 0 0 1-8.49-8.49l9.19-9.19a4 4 0 0 1 5.66 5.66l-9.2 9.19a2 2 0 0 1-2.83-2.83l8.49-8.48"></path></svg>
                                Add Attachments
                            </button>
                            <p class="file-limit-info">Max size: 2MB per photo</p>
                        </div>

                        <div class="form-actions events-buttons">
                            <a href="{{ route('events.index') }}" class="btn-discard">Discard</a>
                            <button type="submit" class="btn-save">Save</button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
<script>
    // 1. Handles removing images already in the database
    function removeExistingImage(buttonElement, imageId) {
        const deletedContainer = document.getElementById('deleted-media-container');
        const hiddenInput = document.createElement('input');
        hiddenInput.type = 'hidden';
        hiddenInput.name = 'deleted_media[]';
        hiddenInput.value = imageId;
        deletedContainer.appendChild(hiddenInput);
        buttonElement.closest('.attachment-item').remove();
    }

    document.addEventListener('DOMContentLoaded', function() {
        const fileInput = document.getElementById('attachmentInput');
        const triggerBtn = document.getElementById('triggerFileInput');
        const previewContainer = document.getElementById('attachment-preview-container');
        const MAX_FILES = 5;
        const MAX_SIZE = 2 * 1024 * 1024; // 2MB
        const eventTypeField = document.querySelector('[name="event_type"]');
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

        const setSectionVisibility = () => {
            const mode = eventTypeField?.value;
            const showVenue = ['In-Person', 'Hybrid'].includes(mode);
            const showOnline = ['Online', 'Hybrid'].includes(mode);

            venueSection?.classList.toggle('hidden-section', !showVenue);
            onlineSection?.classList.toggle('hidden-section', !showOnline);

            if (venueAddressField) {
                venueAddressField.required = showVenue;
                venueAddressField.disabled = !showVenue;
            }

            if (venueNameField) {
                venueNameField.disabled = !showVenue;
            }

            if (platformField) {
                platformField.required = showOnline;
                platformField.disabled = !showOnline;
            }

            if (platformUrlField) {
                platformUrlField.disabled = !showOnline;
            }

            if (venueMapElement) {
                venueMapElement.style.display = showVenue ? 'block' : 'none';
            }

            if (!showVenue) {
                venueSuggestions?.classList.add('hidden-section');
                venueSuggestions.innerHTML = '';
            }

            venueStatus.textContent = showVenue
                ? 'Search an address or click the map to pin the venue.'
                : 'Venue details are hidden for this event mode.';
        };

        const renderVenueSuggestions = (items) => {
            if (!venueSuggestions) {
                return;
            }

            venueSuggestions.innerHTML = '';

            if (!items.length) {
                venueSuggestions.classList.add('hidden-section');
                return;
            }

            items.forEach((item) => {
                const row = document.createElement('div');
                row.className = 'venue-suggestion-item';
                row.textContent = item.display_name;
                row.dataset.lat = item.lat;
                row.dataset.lon = item.lon;
                row.dataset.name = item.display_name;

                row.addEventListener('mousedown', (event) => {
                    event.preventDefault();
                    venueAddressField.value = item.display_name;
                    venueNameField.value = venueNameField.value.trim() || item.display_name;
                    venueSuggestions.classList.add('hidden-section');
                    venueSuggestions.innerHTML = '';
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
                if (!trimmedQuery) {
                    venueSuggestions?.classList.add('hidden-section');
                    venueSuggestions.innerHTML = '';
                }

                return;
            }

            lastSuggestionQuery = trimmedQuery;

            fetch(`https://nominatim.openstreetmap.org/search?format=jsonv2&limit=6&q=${encodeURIComponent(trimmedQuery)}`)
                .then((response) => response.json())
                .then((results) => renderVenueSuggestions(results))
                .catch(() => {
                    venueSuggestions?.classList.add('hidden-section');
                });
        };

        if (triggerBtn && fileInput) {
            triggerBtn.addEventListener('click', function(e) {
                e.preventDefault();
                fileInput.click();
            });
        }

        fileInput.addEventListener('change', function(event) {
            const files = Array.from(event.target.files);
            const existingCount = document.querySelectorAll('.attachment-item').length;

            // Check file limit
            if (files.length + existingCount > MAX_FILES) {
                alert(`Baby, you can only upload up to ${MAX_FILES} photos.`);
                this.value = ""; 
                return;
            }

            // Clear previous NEW previews (to keep it organized)
            document.querySelectorAll('.new-image-preview').forEach(el => el.remove());

            files.forEach((file) => {
                // Check individual file size
                if (file.size > MAX_SIZE) {
                    alert(`${file.name} is too big! (Max 2MB)`);
                    return;
                }

                const reader = new FileReader();
                reader.onload = function(e) {
                    const wrapper = document.createElement('div');
                    wrapper.className = 'attachment-item new-image-preview';
                    
                    wrapper.innerHTML = `
                        <img src="${e.target.result}" class="attachment-img" style="border: 2px dashed #fbd117;">
                        <button type="button" class="remove-attachment-btn" onclick="this.parentElement.remove()">&times;</button>
                    `;
                    
                    previewContainer.appendChild(wrapper);
                }
                reader.readAsDataURL(file);
            });
        });

        if (venueMapElement && window.L) {
            const mapCenter = Number.isFinite(existingLatitude) && Number.isFinite(existingLongitude)
                ? [existingLatitude, existingLongitude]
                : defaultCenter;

            venueMap = L.map('venueMap').setView(mapCenter, Number.isFinite(existingLatitude) ? 16 : 11);

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 19,
                attribution: '&copy; OpenStreetMap contributors',
            }).addTo(venueMap);

            venueMarker = L.marker(mapCenter, { draggable: true }).addTo(venueMap);

            const setVenuePoint = (lat, lng, updateAddress = true) => {
                venueLatitudeField.value = lat.toFixed(6);
                venueLongitudeField.value = lng.toFixed(6);
                venueMarker.setLatLng([lat, lng]);
                venueMap.setView([lat, lng], 16);

                if (updateAddress) {
                    venueStatus.textContent = 'Resolving address from map pin...';
                    fetch(`https://nominatim.openstreetmap.org/reverse?format=jsonv2&lat=${encodeURIComponent(lat)}&lon=${encodeURIComponent(lng)}`)
                        .then((response) => response.json())
                        .then((data) => {
                            const address = data.display_name || '';
                            if (address) {
                                venueAddressField.value = address;
                                venueStatus.textContent = 'Address updated from map pin.';
                            } else {
                                venueStatus.textContent = 'Unable to resolve an address for that pin.';
                            }
                        })
                        .catch(() => {
                            venueStatus.textContent = 'Address lookup failed. You can still type the exact address manually.';
                        });
                }
            };

            const geocodeAddress = (address) => {
                const query = address.trim();

                if (!query) {
                    return;
                }

                venueStatus.textContent = 'Looking up the typed address...';

                fetch(`https://nominatim.openstreetmap.org/search?format=jsonv2&limit=1&q=${encodeURIComponent(query)}`)
                    .then((response) => response.json())
                    .then((results) => {
                        if (!results.length) {
                            venueStatus.textContent = 'No map result found for that address. Try a more exact address.';
                            return;
                        }

                        const place = results[0];
                        const lat = parseFloat(place.lat);
                        const lng = parseFloat(place.lon);
                        venueAddressField.value = place.display_name;
                        venueStatus.textContent = 'Map updated from typed address.';
                        setVenuePoint(lat, lng, false);
                    })
                    .catch(() => {
                        venueStatus.textContent = 'Address lookup failed. The venue can still be submitted with the typed address.';
                    });
            };

            venueMarker.on('dragend', () => {
                const { lat, lng } = venueMarker.getLatLng();
                setVenuePoint(lat, lng, true);
            });

            venueMap.on('click', (event) => {
                setVenuePoint(event.latlng.lat, event.latlng.lng, true);
            });

            venueAddressField?.addEventListener('blur', () => {
                const currentType = eventTypeField?.value;

                if (['In-Person', 'Hybrid'].includes(currentType)) {
                    clearTimeout(addressDebounce);
                    addressDebounce = setTimeout(() => geocodeAddress(venueAddressField.value), 300);
                }
            });

            venueAddressField?.addEventListener('input', () => {
                clearTimeout(suggestionDebounce);
                const currentType = eventTypeField?.value;

                if (!['In-Person', 'Hybrid'].includes(currentType)) {
                    venueSuggestions?.classList.add('hidden-section');
                    venueSuggestions.innerHTML = '';
                    return;
                }

                suggestionDebounce = setTimeout(() => searchVenueSuggestions(venueAddressField.value), 300);
            });

            venueAddressField?.addEventListener('keydown', (event) => {
                if (event.key === 'Enter') {
                    event.preventDefault();
                    geocodeAddress(venueAddressField.value);
                }
            });

            if (Number.isFinite(existingLatitude) && Number.isFinite(existingLongitude)) {
                venueMarker.setLatLng([existingLatitude, existingLongitude]);
                venueMap.setView([existingLatitude, existingLongitude], 16);
            } else if (existingAddress) {
                geocodeAddress(existingAddress);
            }
        }

        const syncVenueRequirement = () => {
            setSectionVisibility();
        };

        eventTypeField?.addEventListener('change', syncVenueRequirement);
        syncVenueRequirement();
    });
</script>
@endpush