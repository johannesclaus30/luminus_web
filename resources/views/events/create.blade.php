@extends('layouts.admin')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/events.css') }}">
    <style>
        .capacity-col { flex: 0.8; display: flex; flex-direction: column; min-width: 0; }
        
        /* Grid layout for neat, organized previews */
        #attachment-preview-container { 
            display: grid; 
            grid-template-columns: repeat(auto-fill, minmax(80px, 1fr)); 
            gap: 10px; 
            margin-bottom: 15px; 
            width: 100%;
        }

        .attachment-item { 
            position: relative; 
            width: 80px; 
            height: 80px; 
            border-radius: 8px; 
            overflow: hidden; 
            border: 1px solid #ddd;
            background-color: #f9f9f9;
        }

        .attachment-img { 
            width: 100%; 
            height: 100%; 
            object-fit: cover; /* Prevents distortion */
        }

        .remove-attachment-btn {
            position: absolute; top: 4px; right: 4px; background: rgba(255,0,0,0.9); color: white;
            border: none; border-radius: 50%; width: 20px; height: 20px; cursor: pointer;
            display: flex; align-items: center; justify-content: center; font-size: 12px; font-weight: bold;
            z-index: 10;
        }
        
        .remove-attachment-btn:hover { background: red; }
        .file-limit-info { font-size: 12px; color: #666; margin-top: 5px; }
    </style>
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

            <form action="{{ $event->exists ? route('events.update', $event->Events_ID) : route('events.store') }}" 
                  method="POST" 
                  enctype="multipart/form-data" 
                  id="eventForm">
                @csrf
                @if($event->exists) @method('PUT') @endif
                
                <div id="deleted-media-container"></div>
                
                <div class="events-details-layout">
                    <div class="form-left-panel">
                        <label>Event Title</label>
                        <input type="text" name="Title" class="textarea-style" value="{{ old('Title', $event->Title) }}" required>
                        
                        <div class="location-date-row">
                            <div class="location-col">
                                <label>Event Location</label>
                                <div class="input-with-icon">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-map-pin"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path><circle cx="12" cy="10" r="3"></circle></svg>
                                    <input type="text" name="Location" class="textarea-style pl-icon" placeholder="Select Location" value="{{ old('Location', $event->Location) }}" required>
                                </div>
                            </div>

                            <div class="capacity-col">
                                <label>Max Capacity</label>
                                <input type="number" name="MaxCapacity" class="textarea-style" placeholder="e.g. 100" min="1" value="{{ old('MaxCapacity', $event->MaxCapacity) }}" required style="padding: 10px 15px !important; box-sizing: border-box !important;">
                            </div>
                        </div>

                        <div class="location-date-row">
                            <div class="date-col">
                                <label>Event Date</label>
                                <div class="date-inputs-row">
                                    <p>From</p>
                                    <div class="date-input-wrapper">
                                        <input type="date" name="StartDate" value="{{ old('StartDate', $event->StartDate ? \Carbon\Carbon::parse($event->StartDate)->format('Y-m-d') : '') }}" required>
                                    </div>
                                    <p>To</p>
                                    <div class="date-input-wrapper">
                                        <input type="date" name="EndDate" value="{{ old('EndDate', $event->EndDate ? \Carbon\Carbon::parse($event->EndDate)->format('Y-m-d') : '') }}" required>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <label>Content</label>
                        <textarea name="Description" class="textarea-style textarea-description event-content" required>{{ old('Description', $event->Description) }}</textarea>
                    </div>

                    <div class="form-right-panel">
                        <div class="attachments-card">
                            <h3 class="attachments-title">Attachments (Max 5)</h3>
                            
                            <div id="attachment-preview-container">
                                @if($event->images)
                                    @foreach($event->images as $image)
                                        <div class="attachment-item existing-image">
                                            <img src="{{ asset('storage/' . $image->ImagePath) }}" class="attachment-img">
                                            <button type="button" class="remove-attachment-btn" onclick="removeExistingImage(this, {{ $image->ImgEvent_ID }})">&times;</button>
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
    });
</script>
@endpush