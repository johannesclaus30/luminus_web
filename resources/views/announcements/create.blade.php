@extends('layouts.admin')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/announcements.css') }}">
@endpush

@section('content')
<div class="layout-wrapper">
    {{-- ... Admin Menu ... --}}
    <div class="admin-menu">
        <div>
            <p class="text-titles">Admin Menu</p>
            <a href="/admin/dashboard" class="admin-menu-buttons">Admin Dashboard</a>
            <a href="/admin/directory" class="admin-menu-buttons">Alumni Directory</a>
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
            <h2>Add New Announcement</h2>

            @if ($errors->any())
                <div class="upload-status status-error" style="margin-bottom: 16px;">
                    @foreach ($errors->all() as $error)
                        <div>{{ $error }}</div>
                    @endforeach
                </div>
            @endif

            <form action="{{ route('announcements.store') }}" method="POST" enctype="multipart/form-data" id="announcementForm">
                @csrf
                <div class="announcements-details">
                    <div class="new-announcements-title-desc">
                        <label>Announcement Title</label>
                        <input type="text" name="title" class="textarea-style" value="{{ old('title') }}" required>
                        
                        <label>Announcement Description</label>
                        <textarea name="announcement_description" class="textarea-style textarea-description" required>{{ old('announcement_description') }}</textarea>

                        <label>Schedule Post (optional)</label>
                        <input type="datetime-local" name="scheduled_post_at" class="textarea-style" value="{{ old('scheduled_post_at') }}">
                        <small style="display:block; margin-top:6px; color:#666;">Leave blank to publish immediately.</small>
                        
                        <button type="submit" class="announcements-submit-btn">Publish Announcement</button>
                    </div>

                    <div class="new-announcements-image">
                        <label>Attach Images Only (up to 5, max 5MB each)</label>
                        <label class="image-upload-box" id="image-box">
                            <span class="upload-prompt-text">Click or drag images here</span>
                            <input type="file" id="image-input" name="images[]" multiple accept="image/*" hidden>
                            <div id="image-preview-container" class="preview-container"></div>
                            <div id="image-status" class="upload-status status-default">0 items attached</div>
                        </label>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
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
                status.innerText = config.isVideo ? "No video attached" : "0 items attached";
                parentBox.classList.remove('has-files');
                return;
            }

            // 1. Validate Amount
            if (files.length > config.maxCount) {
                status.innerText = `⚠️ Exceeded limit! Max ${config.maxCount} allowed.`;
                status.classList.add('status-error');
                this.value = ""; // Reset input
                return;
            }

            // 2. Validate Size (Individual files)
            const oversized = files.filter(f => f.size > (config.maxSizeMB * 1024 * 1024));
            if (oversized.length > 0) {
                status.innerText = `⚠️ File too large! Max ${config.maxSizeMB}MB allowed.`;
                status.classList.add('status-error');
                this.value = ""; 
                return;
            }

            // If valid, show previews
            parentBox.classList.add('has-files');
            status.classList.add('status-success');
            status.innerText = config.isVideo ? "✅ Video ready" : `✅ ${files.length} image(s) attached`;

            files.forEach(file => {
                const reader = new FileReader();
                reader.onload = function(e) {
                    let element = document.createElement(config.isVideo ? 'video' : 'img');
                    element.src = e.target.result;
                    element.classList.add(config.isVideo ? 'video-preview' : 'preview-item');
                    if(config.isVideo) element.muted = true; // Prevents audio issues on auto-load
                    container.appendChild(element);
                }
                reader.readAsDataURL(file);
            });
        });
    }

    // Initialize with limits
    handleFilePreview({
        inputId: 'image-input',
        containerId: 'image-preview-container',
        statusId: 'image-status',
        maxCount: 5,
        maxSizeMB: 5,
        isVideo: false
    });
</script>
@endsection