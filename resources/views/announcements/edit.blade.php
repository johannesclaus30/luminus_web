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
                        $hasExistingMedia = $announcement->images->count() > 0;
                    @endphp

                    <!-- Existing Media Display -->
                    @if($hasExistingMedia)
                        <div style="margin-bottom: 1.5rem;" id="existingMediaSection">
                            <h4 style="font-size: 0.9rem; font-weight: 600; color: var(--gray-700); margin-bottom: 0.75rem;">
                                Current Attachments 
                                <span style="font-weight: 400; color: var(--gray-500); font-size: 0.8rem;">(Click 'X' to mark for removal — changes save when you click Update)</span>
                            </h4>
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
                                        <button type="button" class="preview-remove" onclick="window.markForDeletion({{ $media->id }}, '{{ $isVideo ? 'video' : 'image' }}')" title="Mark for removal">
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
                            <div class="upload-title">Upload New Images</div>
                            <div class="upload-desc">Max 5 files total • 5MB each • JPG, PNG, WEBP</div>
                        </div>

                        <div class="upload-zone" id="videoZone" onclick="handleZoneClick('video')">
                            <input type="file" id="videoInput" name="video" accept="video/mp4" hidden>
                            <div class="upload-icon"><i class="fa-solid fa-video"></i></div>
                            <div class="upload-title">Upload New Video</div>
                            <div class="upload-desc">Max 1 file • 30MB limit • MP4 only</div>
                        </div>
                    </div>
                    
                    <div id="uploadError" class="error-message"></div>
                    
                    <!-- Clear new images button -->
                    <div id="clearImagesBtn" style="display: none; margin: 0.75rem 0; text-align: right;">
                        <button type="button" class="btn btn-sm btn-secondary" onclick="window.removeAllNewImages()" style="font-size: 0.8125rem;">
                            <i class="fa-solid fa-trash-can"></i> Clear All New Images
                        </button>
                    </div>
                    
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

    <script>
        // Mobile menu toggle
        function toggleMobileMenu() {
            const sidebar = document.getElementById('adminSidebar');
            const overlay = document.getElementById('mobileOverlay');
            sidebar.classList.toggle('mobile-open');
            overlay.classList.toggle('active');
            document.body.style.overflow = sidebar.classList.contains('mobile-open') ? 'hidden' : '';
        }

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

        // Form-specific JS
        document.addEventListener('DOMContentLoaded', function() {
            const imageInput = document.getElementById('imageInput');
            const videoInput = document.getElementById('videoInput');
            const imageZone = document.getElementById('imageZone');
            const videoZone = document.getElementById('videoZone');
            const uploadError = document.getElementById('uploadError');
            const previewContainer = document.getElementById('previewContainer');
            const deletedMediaContainer = document.getElementById('deletedMediaContainer');
            const existingMediaSection = document.getElementById('existingMediaSection');
            const clearImagesBtn = document.getElementById('clearImagesBtn');

            // Track counts — these update dynamically as things are removed
            let existingImageCount = {{ $existingImageCount }};
            let existingVideoCount = {{ $existingVideoCount }};
            
            // Track if a NEW video has been uploaded (separate from existing)
            let newVideoUploaded = false;
            let newImagesUploaded = false;

            // Initial state: Disable zones based on existing media
            updateZoneStates();

            function updateZoneStates() {
                // Determine total active images (existing not marked for deletion + new)
                const totalActiveImages = existingImageCount + (newImagesUploaded ? imageInput.files.length : 0);
                const totalActiveVideos = existingVideoCount + (newVideoUploaded ? 1 : 0);
                
                if (totalActiveImages > 0) {
                    // Has images — disable video zone
                    videoZone.classList.add('disabled');
                    videoInput.disabled = true;
                } else {
                    videoZone.classList.remove('disabled');
                    videoInput.disabled = false;
                }
                
                if (totalActiveVideos > 0) {
                    // Has videos — disable image zone
                    imageZone.classList.add('disabled');
                    imageInput.disabled = true;
                } else {
                    imageZone.classList.remove('disabled');
                    imageInput.disabled = false;
                }
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
                // Check total limit (existing not-deleted + new)
                if ((existingImageCount + files.length) > 5) {
                    errorMsg = `You can only have 5 images total. You currently have ${existingImageCount} existing image(s).`;
                }

                for (let file of files) {
                    if (file.size > 5 * 1024 * 1024) {
                        errorMsg = `Image "${file.name}" exceeds the 5MB limit.`;
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
                newImagesUploaded = true;
                videoZone.classList.add('disabled');
                videoInput.disabled = true;
                videoInput.value = ''; 
                newVideoUploaded = false;
                imageZone.classList.add('active');
                
                updateImagePreviews(files);
            });

            // Handle Video Upload
            videoInput.addEventListener('change', function(e) {
                const file = e.target.files[0];
                if (!file) return;

                let errorMsg = '';
                if (existingVideoCount > 0) errorMsg = 'This announcement already has a video. Remove the existing video first.';
                if (newVideoUploaded) errorMsg = 'You already uploaded a new video. Clear it first.';
                if (file.size > 30 * 1024 * 1024) errorMsg = 'Video exceeds the 30MB limit.';
                
                const ext = file.name.split('.').pop().toLowerCase();
                if (ext !== 'mp4') errorMsg = 'Invalid file type. Only MP4 videos are allowed.';

                if (errorMsg) {
                    showError(errorMsg);
                    this.value = '';
                    return;
                }

                clearError();
                newVideoUploaded = true;
                imageZone.classList.add('disabled');
                imageInput.disabled = true;
                imageInput.value = ''; 
                newImagesUploaded = false;
                videoZone.classList.add('active');
                clearImagesBtn.style.display = 'none';
                previewContainer.innerHTML = '';

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
                files.forEach((file, index) => {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        const div = document.createElement('div');
                        div.className = 'preview-item';
                        div.style.position = 'relative';
                        div.innerHTML = `
                            <img src="${e.target.result}" alt="Preview">
                            <button type="button" class="preview-remove" onclick="window.removeSingleNewImage(${index})" title="Remove image">
                                <i class="fa-solid fa-xmark"></i>
                            </button>
                        `;
                        previewContainer.appendChild(div);
                    };
                    reader.readAsDataURL(file);
                });
                
                clearImagesBtn.style.display = 'block';
            }

            function updateVideoPreview(file) {
                previewContainer.innerHTML = '';
                const reader = new FileReader();
                reader.onload = function(e) {
                    const div = document.createElement('div');
                    div.className = 'preview-item';
                    div.style.width = '200px';
                    div.style.height = '150px';
                    div.style.position = 'relative';
                    div.innerHTML = `
                        <video src="${e.target.result}" controls style="width:100%; height:100%; object-fit:cover;"></video>
                        <button type="button" class="preview-remove" onclick="window.removeVideoPreview()" title="Remove video">
                            <i class="fa-solid fa-xmark"></i>
                        </button>
                    `;
                    previewContainer.appendChild(div);
                };
                reader.readAsDataURL(file);
            }

            // Remove single new image
            window.removeSingleNewImage = function(index) {
                const dt = new DataTransfer();
                const files = Array.from(imageInput.files);
                
                files.splice(index, 1);
                files.forEach(file => dt.items.add(file));
                imageInput.files = dt.files;
                
                if (files.length === 0) {
                    removeAllNewImages();
                } else {
                    updateImagePreviews(files);
                }
            };

            // Remove all new images
            window.removeAllNewImages = function() {
                previewContainer.innerHTML = '';
                imageInput.value = '';
                newImagesUploaded = false;
                clearImagesBtn.style.display = 'none';
                imageZone.classList.remove('active');
                updateZoneStates();
                clearError();
            };

            // Remove new video preview
            window.removeVideoPreview = function() {
                previewContainer.innerHTML = '';
                videoInput.value = '';
                newVideoUploaded = false;
                videoZone.classList.remove('active');
                updateZoneStates();
                clearError();
            };

            // Mark existing attachment for deletion
            window.markForDeletion = function(id, type) {
                if(!confirm('Are you sure you want to remove this attachment? It will be deleted when you save.')) return;

                const item = document.getElementById(`existing-media-${id}`);
                if (item) {
                    item.style.opacity = '0.3';
                    item.style.pointerEvents = 'none';
                }

                // Add hidden input for the controller
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'deleted_media[]';
                input.value = id;
                deletedMediaContainer.appendChild(input);

                // Update counts
                if (type === 'image') {
                    existingImageCount--;
                } else {
                    existingVideoCount--;
                }

                // Check if all existing media is now marked for deletion
                const remainingVisible = document.querySelectorAll('.existing-media-item:not([style*="opacity: 0.3"])');
                if (remainingVisible.length === 0 && existingMediaSection) {
                    existingMediaSection.style.opacity = '0.5';
                }

                // Update zone states
                updateZoneStates();
                
                // If removing a video, clear any new video upload too
                if (type === 'video' && newVideoUploaded) {
                    window.removeVideoPreview();
                }
                // If removing all images, clear any new image uploads too
                if (type === 'image' && existingImageCount === 0 && newImagesUploaded) {
                    window.removeAllNewImages();
                }

                clearError();
            };
        });
    </script>
</body>
</html>