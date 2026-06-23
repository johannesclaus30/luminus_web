@extends('layouts.admin')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/announcements.css') }}">
@endpush

@section('content')
<div class="layout-wrapper">
    <div class="admin-menu">
        <div>
            <p class="text-titles">Admin Menu</p>
            <a href="/admin/dashboard" class="admin-menu-buttons">Admin Dashboard</a>
            <a href="/admin/announcements" class="admin-menu-current">Announcement Editor</a>
            <a href="/admin/events" class="admin-menu-buttons">Event Organizer</a>
            <a href="/admin/perks" class="admin-menu-buttons">Perks and Discounts</a>
            <a href="/admin/alumni_tracer" class="admin-menu-buttons">NU Alumni Tracer</a>
            <a href="/admin/messages" class="admin-menu-buttons">Messages</a>
            <a href="/admin/settings" class="admin-menu-buttons">Settings</a>
        </div>
        <a href="{{ route('admin.logout') }}" class="admin-menu-signout">Sign Out</a>
    </div>

    <div class="div-dashboard-container">
        <div class="announcements-create-container">
            <h2>Edit Announcement</h2>

            @if ($errors->any())
                <div class="upload-status status-error" style="margin-bottom: 16px;">
                    @foreach ($errors->all() as $error)
                        <div>{{ $error }}</div>
                    @endforeach
                </div>
            @endif

            <form action="{{ route('announcements.update', $announcement->id) }}" method="POST" enctype="multipart/form-data" id="announcementForm">
                @csrf
                @method('PUT')

                <div id="deleted-media-container"></div>

                <div class="announcements-details">
                    <div class="new-announcements-title-desc">
                        <label>Announcement Title</label>
                        <input type="text" name="title" class="textarea-style" value="{{ old('title', $announcement->title) }}" required>
                        
                        <label>Announcement Description</label>
                        <textarea name="announcement_description" class="textarea-style textarea-description" required>{{ old('announcement_description', $announcement->announcement_description) }}</textarea>

                        <label>Schedule Post (optional)</label>
                        <input type="datetime-local" name="scheduled_post_at" class="textarea-style" value="{{ old('scheduled_post_at', optional($announcement->scheduled_post_at)->format('Y-m-d\TH:i')) }}">
                        <small style="display:block; margin-top:6px; color:#666;">Leave blank to keep this announcement unscheduled.</small>
                        
                        <button type="submit" class="announcements-submit-btn">Update Announcement</button>
                        <a href="{{ route('announcements.index') }}" style="display:block; text-align:center; margin-top:10px; color:#666; text-decoration:none;">Cancel</a>
                    </div>

                    <div class="new-announcements-image">
                        
                        <label>Attach New Images Only (up to 5 total, max 5MB each)</label>
                        <label class="image-upload-box" id="image-box">
                            @php
                                $existingImages = $announcement->images->filter(fn($i) => in_array(strtolower(pathinfo($i->image_path, PATHINFO_EXTENSION)), ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg']));
                            @endphp
                            
                            @if($existingImages->count() > 0)
                                <span class="current-label">Currently Uploaded (Click 'x' to remove):</span>
                                <div class="preview-container" style="margin-bottom: 10px; pointer-events: auto;">
                                    @foreach($existingImages as $img)
                                        <div class="existing-item-container" id="media-{{ $img->id }}">
                                            <img src="{{ $img->image_url }}" class="preview-item" style="width:60px; height:60px; object-fit:cover;">
                                            <button type="button" class="remove-media-btn" onclick="removeExistingMedia(event, {{ $img->id }})">&times;</button>
                                        </div>
                                    @endforeach
                                </div>
                                <hr style="width:100%; border:0; border-top:1px solid #eee;">
                            @endif

                            <div class="image-upload-box-content">
                                <span class="upload-prompt-text">Click to add images</span>
                                <input type="file" id="image-input" name="images[]" multiple accept="image/*" hidden>
                                <div id="image-status" class="upload-status status-default">0 new items selected</div>
                            </div>
                            <div id="image-preview-container" class="preview-container"></div>
                        </label>

                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    // Utility for New File Previews
    function handleFilePreview(config) {
        const input = document.getElementById(config.inputId);
        const container = document.getElementById(config.containerId);
        const status = document.getElementById(config.statusId);
        const parentBox = input.closest('.image-upload-box');

        input.addEventListener('change', function() {
            container.innerHTML = ''; 
            status.classList.remove('status-error', 'status-success');
            status.classList.add('status-default');

            const files = Array.from(this.files);
            if (files.length === 0) {
                status.innerText = config.isVideo ? "No new video selected" : "0 new items selected";
                parentBox.classList.remove('has-new-files');
                return;
            }

            if ((config.existingCount + files.length) > config.maxCount) {
                status.innerText = `⚠️ Limit: ${config.maxCount}. Can add ${config.maxCount - config.existingCount} more.`;
                status.classList.add('status-error');
                this.value = ""; 
                return;
            }

            parentBox.classList.add('has-new-files');
            status.classList.add('status-success');
            status.innerText = config.isVideo ? "✅ New video ready" : `✅ ${files.length} new image(s) selected`;

            files.forEach(file => {
                const reader = new FileReader();
                reader.onload = function(e) {
                    let element = document.createElement(config.isVideo ? 'video' : 'img');
                    element.src = e.target.result;
                    element.classList.add(config.isVideo ? 'video-preview' : 'preview-item');
                    if(config.isVideo) {
                        element.style.maxWidth = "120px";
                        element.style.maxHeight = "80px";
                        element.muted = true;
                    } else {
                        element.style.width = "60px";
                        element.style.height = "60px";
                        element.style.objectFit = "cover";
                    }
                    container.appendChild(element);
                }
                reader.readAsDataURL(file);
            });
        });
    }

    // Initialize listeners
    handleFilePreview({
        inputId: 'image-input',
        containerId: 'image-preview-container',
        statusId: 'image-status',
        maxCount: 5,
        existingCount: {{ $existingImages->count() }},
        maxSizeMB: 5,
        isVideo: false
    });

    // Mark Existing Media for Deletion
    function removeExistingMedia(event, id) {
        event.preventDefault(); 
        event.stopPropagation();

        if (confirm('Are you sure you want to remove this item? It will be deleted when you update.')) {
            const element = document.getElementById(`media-${id}`);
            if (element) {
                element.style.display = 'none';
            }

            const container = document.getElementById('deleted-media-container');
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'deleted_media[]';
            input.value = id;
            container.appendChild(input);
        }
    }
</script>
@endsection