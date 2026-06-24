<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Announcement | LumiNUs Admin</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="/css/admin.css">
    <link rel="stylesheet" href="/css/announcements_modern.css">
    <link rel="icon" type="image/png" href="/assets/logos/LumiNUs_Icon.png">
</head>
<body>
    @include('partials.admin-navbar')
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

        <main class="admin-main">
            <button class="mobile-menu-toggle" id="mobileMenuToggle" onclick="toggleMobileMenu()">
                <i class="fa-solid fa-bars"></i>
            </button>

            <header class="page-header">
                <div class="header-content">
                    <div class="header-title-section">
                        <h1 class="page-title"><i class="fa-solid fa-pen-to-square"></i> Edit Announcement</h1>
                        <p class="page-subtitle">Update the details of this announcement</p>
                    </div>
                    <div class="header-actions">
                        <a href="{{ route('announcements.index') }}" class="btn btn-secondary">
                            <i class="fa-solid fa-arrow-left"></i> <span>Back</span>
                        </a>
                    </div>
                </div>
            </header>

            @if ($errors->any())
                <div class="upload-status status-error" style="margin-bottom: 1.5rem; padding: 1rem; border-radius: var(--radius-lg); background: #fee2e2; color: #ef4444; border: 1px solid #ef4444;">
                    <ul style="margin:0; padding-left:1.25rem;">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('announcements.update', $announcement->id) }}" method="POST" enctype="multipart/form-data" id="announcementForm" class="form-card">
                @csrf
                @method('PUT')
                
                <!-- Hidden container to store IDs of media marked for deletion -->
                <div id="deletedMediaContainer"></div>

                <div class="form-group">
                    <label for="title" class="form-label">Announcement Title</label>
                    <input type="text" id="title" name="title" class="form-control" value="{{ old('title', $announcement->title) }}" required>
                </div>

                <div class="form-group">
                    <label for="announcement_description" class="form-label">Description</label>
                    <textarea id="announcement_description" name="announcement_description" class="form-control" required>{{ old('announcement_description', $announcement->announcement_description) }}</textarea>
                </div>

                <div class="form-group">
                    <label for="scheduled_post_at" class="form-label">Schedule Post (Optional)</label>
                    <input type="datetime-local" id="scheduled_post_at" name="scheduled_post_at" class="form-control" value="{{ old('scheduled_post_at', optional($announcement->scheduled_post_at)->format('Y-m-d\TH:i')) }}">
                    <small style="color: var(--gray-500); font-size: 0.8125rem;">Leave blank to keep this announcement unscheduled.</small>
                </div>

                <!-- Attachments Section -->
                <div class="form-group">
                    <label class="form-label">Attachments</label>
                    <div class="rule-alert">
                        <i class="fa-solid fa-circle-info"></i>
                        <span>You can only upload either <strong>images</strong> or a <strong>video</strong> per announcement, not both.</span>
                    </div>

                    @php
                        $existingImages = $announcement->images->filter(fn($i) => !in_array(strtolower(pathinfo($i->image_path, PATHINFO_EXTENSION)), ['mp4', 'mov', 'avi']));
                        $existingVideos = $announcement->images->filter(fn($i) => in_array(strtolower(pathinfo($i->image_path, PATHINFO_EXTENSION)), ['mp4', 'mov', 'avi']));
                        $existingImageCount = $existingImages->count();
                        $existingVideoCount = $existingVideos->count();
                    @endphp

                    <!-- Existing Media Display -->
                    @if($announcement->images->count() > 0)
                        <div style="margin-bottom: 1.5rem;">
                            <h4 style="font-size: 0.9rem; font-weight: 600; color: var(--gray-700); margin-bottom: 0.75rem;">Current Attachments <span style="font-weight: 400; color: var(--gray-500); font-size: 0.8rem;">(Click 'X' to remove)</span></h4>
                            <div class="preview-container" id="existingMediaContainer">
                                @foreach($announcement->images as $media)
                                    @php
                                        $ext = strtolower(pathinfo($media->image_path, PATHINFO_EXTENSION));
                                        $isVideo = in_array($ext, ['mp4', 'mov', 'avi']);
                                    @endphp
                                    <div class="preview-item existing-media-item" id="existing-media-{{ $media->id }}" data-type="{{ $isVideo ? 'video' : 'image' }}">
                                        @if($isVideo)
                                            <video src="{{ $media->image_url }}" muted></video>
                                        @else
                                            <img src="{{ $media->image_url }}" alt="Attachment">
                                        @endif
                                        <button type="button" class="preview-remove" onclick="markForDeletion({{ $media->id }}, '{{ $isVideo ? 'video' : 'image' }}')">
                                            <i class="fa-solid fa-xmark"></i>
                                        </button>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    <!-- Upload Zones -->
                    <div class="upload-grid">
                        <div class="upload-zone" id="imageZone" onclick="handleZoneClick('image')">
                            <input type="file" id="imageInput" name="images[]" multiple accept="image/jpeg,image/png,image/webp" hidden>
                            <div class="upload-icon"><i class="fa-regular fa-images"></i></div>
                            <div class="upload-title">Upload Images</div>
                            <div class="upload-desc">Max 5 files total • 3MB each • JPG, PNG, WEBP</div>
                        </div>

                        <div class="upload-zone" id="videoZone" onclick="handleZoneClick('video')">
                            <input type="file" id="videoInput" name="video" accept="video/mp4" hidden>
                            <div class="upload-icon"><i class="fa-solid fa-video"></i></div>
                            <div class="upload-title">Upload Video</div>
                            <div class="upload-desc">Max 1 file • 30MB limit • MP4 only</div>
                        </div>
                    </div>
                    
                    <div id="uploadError" class="error-message"></div>
                    <div id="previewContainer" class="preview-container"></div>
                </div>

                <div class="form-actions">
                    <a href="{{ route('announcements.index') }}" class="btn btn-secondary">Cancel</a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fa-solid fa-save"></i>
                        <span>Update Announcement</span>
                    </button>
                </div>
            </form>
        </main>
    </div>

    <!-- Include your shared JS from index.blade.php here -->
    <script src="/js/admin-shared.js"></script>
    
    <!-- Form-specific JS -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const imageInput = document.getElementById('imageInput');
            const videoInput = document.getElementById('videoInput');
            const imageZone = document.getElementById('imageZone');
            const videoZone = document.getElementById('videoZone');
            const uploadError = document.getElementById('uploadError');
            const previewContainer = document.getElementById('previewContainer');
            const deletedMediaContainer = document.getElementById('deletedMediaContainer');

            // Track existing counts from PHP
            let existingImageCount = {{ $existingImageCount }};
            let existingVideoCount = {{ $existingVideoCount }};

            // Initial state: Disable zones based on existing media
            if (existingImageCount > 0) {
                videoZone.classList.add('disabled');
                videoInput.disabled = true;
            } else if (existingVideoCount > 0) {
                imageZone.classList.add('disabled');
                imageInput.disabled = true;
            }

            window.handleZoneClick = function(type) {
                if (type === 'video' && videoZone.classList.contains('disabled')) return;
                if (type === 'image' && imageZone.classList.contains('disabled')) return;
                
                if (type === 'video') videoInput.click();
                else imageInput.click();
            };

            // Handle Image Upload
            imageInput.addEventListener('change', function(e) {
                const files = Array.from(e.target.files);
                if (files.length === 0) return;

                let errorMsg = '';
                // Check total limit (existing + new)
                if ((existingImageCount + files.length) > 5) {
                    errorMsg = `You can only have 5 images total. You already have ${existingImageCount}.`;
                }

                for (let file of files) {
                    if (file.size > 3 * 1024 * 1024) {
                        errorMsg = `Image "${file.name}" exceeds the 3MB limit.`;
                        break;
                    }
                    const ext = file.name.split('.').pop().toLowerCase();
                    if (!['jpg', 'jpeg', 'png', 'webp'].includes(ext)) {
                        errorMsg = `Invalid file type "${ext}". Only JPG, PNG, and WEBP are allowed.`;
                        break;
                    }
                }

                if (errorMsg) {
                    showError(errorMsg);
                    this.value = ''; 
                    return;
                }

                clearError();
                videoZone.classList.add('disabled');
                videoInput.disabled = true;
                videoInput.value = ''; 
                imageZone.classList.add('active');
                
                updateImagePreviews(files);
            });

            // Handle Video Upload
            videoInput.addEventListener('change', function(e) {
                const file = e.target.files[0];
                if (!file) return;

                let errorMsg = '';
                if (existingVideoCount > 0) errorMsg = 'You can only have 1 video per announcement.';
                if (file.size > 30 * 1024 * 1024) errorMsg = 'Video exceeds the 30MB limit.';
                
                const ext = file.name.split('.').pop().toLowerCase();
                if (ext !== 'mp4') errorMsg = 'Invalid file type. Only MP4 videos are allowed.';

                if (errorMsg) {
                    showError(errorMsg);
                    this.value = '';
                    return;
                }

                clearError();
                imageZone.classList.add('disabled');
                imageInput.disabled = true;
                imageInput.value = ''; 
                videoZone.classList.add('active');

                updateVideoPreview(file);
            });

            function showError(msg) {
                uploadError.textContent = msg;
                uploadError.style.display = 'block';
            }

            function clearError() {
                uploadError.style.display = 'none';
            }

            function updateImagePreviews(files) {
                previewContainer.innerHTML = '';
                files.forEach(file => {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        const div = document.createElement('div');
                        div.className = 'preview-item';
                        div.innerHTML = `<img src="${e.target.result}" alt="Preview">`;
                        previewContainer.appendChild(div);
                    };
                    reader.readAsDataURL(file);
                });
            }

            function updateVideoPreview(file) {
                previewContainer.innerHTML = '';
                const reader = new FileReader();
                reader.onload = function(e) {
                    const div = document.createElement('div');
                    div.className = 'preview-item';
                    div.style.width = '200px';
                    div.style.height = '150px';
                    div.innerHTML = `<video src="${e.target.result}" controls></video>`;
                    previewContainer.appendChild(div);
                };
                reader.readAsDataURL(file);
            }

            // Handle marking existing attachments for deletion
            window.markForDeletion = function(id, type) {
                if(!confirm('Are you sure you want to remove this attachment? It will be deleted when you save.')) return;

                const item = document.getElementById(`existing-media-${id}`);
                if (item) {
                    item.style.display = 'none';
                }

                // Add hidden input for the controller
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'deleted_media[]';
                input.value = id;
                deletedMediaContainer.appendChild(input);

                // Update counts and re-enable zones if necessary
                if (type === 'image') {
                    existingImageCount--;
                    if (existingImageCount === 0 && existingVideoCount === 0) {
                        enableAllZones();
                    } else if (existingImageCount === 0) {
                        enableZone('image');
                    }
                } else {
                    existingVideoCount--;
                    if (existingImageCount === 0 && existingVideoCount === 0) {
                        enableAllZones();
                    } else if (existingVideoCount === 0) {
                        enableZone('video');
                    }
                }
            };

            function enableZone(type) {
                if (type === 'image') {
                    imageZone.classList.remove('disabled', 'active');
                    imageInput.disabled = false;
                } else {
                    videoZone.classList.remove('disabled', 'active');
                    videoInput.disabled = false;
                }
            }

            function enableAllZones() {
                enableZone('image');
                enableZone('video');
            }
        });
    </script>
</body>
</html>