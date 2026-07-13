<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Announcement | LumiNUs Admin</title>
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

        <main class="admin-main">
            <button class="mobile-menu-toggle" id="mobileMenuToggle" onclick="toggleMobileMenu()">
                <i class="fa-solid fa-bars"></i>
            </button>

            <header class="page-header">
                <div class="header-content">
                    <div class="header-title-section">
                        <h1 class="page-title"><i class="fa-solid fa-plus-circle"></i> Create Announcement</h1>
                        <p class="page-subtitle">Share a new update with NU Lipa alumni</p>
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

            <form action="{{ route('announcements.store') }}" method="POST" enctype="multipart/form-data" id="announcementForm" class="form-card">
                @csrf

                <div class="form-group">
                    <label for="title" class="form-label">Announcement Title</label>
                    <input type="text" id="title" name="title" class="form-control" value="{{ old('title') }}" required>
                </div>

                <div class="form-group">
                    <label for="announcement_description" class="form-label">Description</label>
                    <textarea id="announcement_description" name="announcement_description" class="form-control" required>{{ old('announcement_description') }}</textarea>
                </div>

                <div class="form-group">
                    <label for="scheduled_post_at" class="form-label">Schedule Post (Optional)</label>
                    <input type="datetime-local" id="scheduled_post_at" name="scheduled_post_at" class="form-control" value="{{ old('scheduled_post_at') }}">
                    <small style="color: var(--gray-500); font-size: 0.8125rem;">Leave blank to post immediately.</small>
                </div>

                <!-- Attachments Section -->
                <div class="form-group">
                    <label class="form-label">Attachments</label>
                    <div class="rule-alert">
                        <i class="fa-solid fa-circle-info"></i>
                        <span>You can only upload either <strong>images</strong> or a <strong>video</strong> per announcement, not both.</span>
                    </div>

                    <div class="upload-grid">
                        <div class="upload-zone" id="imageZone" onclick="handleZoneClick('image')">
                            <input type="file" id="imageInput" name="images[]" multiple accept="image/jpeg,image/png,image/webp" hidden>
                            <div class="upload-icon"><i class="fa-regular fa-images"></i></div>
                            <div class="upload-title">Upload Images</div>
                            <div class="upload-desc">Max 5 files • 5MB each • JPG, PNG, WEBP</div>
                        </div>

                        <div class="upload-zone" id="videoZone" onclick="handleZoneClick('video')">
                            <input type="file" id="videoInput" name="video" accept="video/mp4" hidden>
                            <div class="upload-icon"><i class="fa-solid fa-video"></i></div>
                            <div class="upload-title">Upload Video</div>
                            <div class="upload-desc">Max 1 file • 30MB limit • MP4 only</div>
                        </div>
                    </div>
                    <div id="uploadError" class="error-message"></div>
                    
                    <div id="clearImagesBtn" style="display: none; margin-bottom: 0.75rem; text-align: right;">
                        <button type="button" class="clearimg btn btn-sm btn-secondary" onclick="window.removeAllImages()" style="font-size: 0.8125rem;">
                            <i class="fa-solid fa-xmark"></i> Clear All Images
                        </button>
                    </div>

                    <div id="previewContainer" class="preview-container"></div>
                </div>

                <div class="form-actions">
                    <a href="{{ route('announcements.index') }}" class="btn btn-secondary">Cancel</a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fa-solid fa-save"></i>
                        <span>Create Announcement</span>
                    </button>
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

    <script>
        // Mobile menu toggle
        function toggleMobileMenu() {
            const sidebar = document.getElementById('adminSidebar');
            const overlay = document.getElementById('mobileOverlay');
            sidebar.classList.toggle('mobile-open');
            overlay.classList.toggle('active');
            document.body.style.overflow = sidebar.classList.contains('mobile-open') ? 'hidden' : '';
        }

        // ========================================
        // WARNING MODAL SYSTEM
        // ========================================
        
        document.addEventListener('DOMContentLoaded', function() {
            // Get modal elements
            const warningOverlay = document.getElementById('warningModal');
            const warningTitle = document.getElementById('warningModalTitle');
            const warningMessage = document.getElementById('warningModalMessage');
            const warningIcon = document.getElementById('warningModalIcon');
            const confirmBtn = document.getElementById('warningModalConfirm');
            const cancelBtn = document.getElementById('warningModalCancel');
            
            let pendingCallback = null;
            
            // Close modal function
            function closeWarningModal() {
                warningOverlay.classList.remove('active');
                document.body.style.overflow = '';
                pendingCallback = null;
            }
            
            // Cancel button
            cancelBtn.addEventListener('click', closeWarningModal);
            
            // Close on overlay click
            warningOverlay.addEventListener('click', function(e) {
                if (e.target === warningOverlay) {
                    closeWarningModal();
                }
            });
            
            // Close on Escape key
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape' && warningOverlay.classList.contains('active')) {
                    closeWarningModal();
                }
            });
            
            // Confirm button - execute the pending callback
            confirmBtn.addEventListener('click', function() {
                if (pendingCallback) {
                    pendingCallback();
                }
                closeWarningModal();
            });
            
            // Show modal function
            window.showWarningModal = function(config) {
                const {
                    title = 'Confirm Action',
                    message = 'Are you sure?',
                    iconType = 'warning',
                    confirmText = 'Confirm',
                    confirmClass = 'btn-danger',
                    onConfirm = null
                } = config;
                
                // Set title and message
                warningTitle.textContent = title;
                warningMessage.innerHTML = message;
                
                // Set icon
                warningIcon.className = 'warning-modal-icon ' + iconType;
                const iconElement = warningIcon.querySelector('i');
                if (iconType === 'danger') {
                    iconElement.className = 'fa-solid fa-triangle-exclamation';
                } else if (iconType === 'success') {
                    iconElement.className = 'fa-solid fa-circle-question';
                } else {
                    iconElement.className = 'fa-solid fa-triangle-exclamation';
                }
                
                // Set confirm button
                confirmBtn.className = 'btn ' + confirmClass;
                confirmBtn.innerHTML = '<i class="fa-solid fa-check"></i> ' + confirmText;
                
                // Store callback
                pendingCallback = onConfirm;
                
                // Show modal
                warningOverlay.classList.add('active');
                document.body.style.overflow = 'hidden';
                confirmBtn.focus();
            };

            const formCancelBtn = document.querySelector('.form-actions .btn-secondary');
            if (formCancelBtn) {
                formCancelBtn.addEventListener('click', function(e) {
                    e.preventDefault();
                    const href = this.getAttribute('href');
                    window.showWarningModal({
                        title: 'Discard Changes',
                        message: 'Are you sure you want to <strong>cancel</strong>?<br><small>All unsaved changes will be lost.</small>',
                        iconType: 'warning',
                        confirmText: 'Discard',
                        confirmClass: 'btn-warning',
                        onConfirm: function() {
                            window.location.href = href;
                        }
                    });
                });
            }
            
            // Close sidebar when clicking on a nav item (mobile)
            document.querySelectorAll('.nav-item').forEach(item => {
                item.addEventListener('click', function() {
                    if (window.innerWidth <= 1024) {
                        toggleMobileMenu();
                    }
                });
            });

            // Form-specific JS for file uploads
            const imageInput = document.getElementById('imageInput');
            const videoInput = document.getElementById('videoInput');
            const imageZone = document.getElementById('imageZone');
            const videoZone = document.getElementById('videoZone');
            const uploadError = document.getElementById('uploadError');
            const previewContainer = document.getElementById('previewContainer');

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
                if (files.length > 5) errorMsg = 'Maximum of 5 images allowed.';

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
                files.forEach((file, index) => {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        const div = document.createElement('div');
                        div.className = 'preview-item';
                        div.style.position = 'relative';
                        div.innerHTML = `
                            <img src="${e.target.result}" alt="Preview">
                            <button type="button" class="preview-remove" onclick="window.removeSingleImage(${index})" title="Remove image">
                                <i class="fa-solid fa-xmark"></i>
                            </button>
                        `;
                        previewContainer.appendChild(div);
                    };
                    reader.readAsDataURL(file);
                });
                
                document.getElementById('clearImagesBtn').style.display = 'block';
            }

            window.removeSingleImage = function(index) {
                const dt = new DataTransfer();
                const files = Array.from(imageInput.files);
                
                files.splice(index, 1);
                files.forEach(file => dt.items.add(file));
                imageInput.files = dt.files;
                
                if (files.length === 0) {
                    removeAllImages();
                } else {
                    updateImagePreviews(files);
                }
            };

            window.removeAllImages = function() {
                previewContainer.innerHTML = '';
                imageInput.value = '';
                document.getElementById('clearImagesBtn').style.display = 'none';
                imageZone.classList.remove('active');
                videoZone.classList.remove('disabled');
                videoInput.disabled = false;
                imageInput.disabled = false;
                clearError();
            };

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

            window.removeVideoPreview = function() {
                previewContainer.innerHTML = '';
                videoInput.value = '';
                videoZone.classList.remove('active', 'disabled');
                imageZone.classList.remove('disabled', 'active');
                videoInput.disabled = false;
                imageInput.disabled = false;
                clearError();
            };
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