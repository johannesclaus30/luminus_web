<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Perk | LumiNUs Admin</title>

    <!-- Fonts & Icons -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Stylesheets -->
    <link rel="stylesheet" href="/css/admin.css">
    <link rel="stylesheet" href="/css/admin-fixed-navbar.css">
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
                <a href="{{ route('events.index') }}" class="nav-item">
                    <i class="fa-solid fa-calendar-check"></i>
                    <span>Events</span>
                </a>
                <a href="{{ route('perks.index') }}" class="nav-item active">
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
                            Edit Perk
                        </h1>
                        <p class="page-subtitle">Update perk details for NU Lipa alumni</p>
                    </div>
                    
                    <div class="header-actions">
                        <a href="{{ route('perks.index') }}" class="btn btn-secondary">
                            <i class="fa-solid fa-arrow-left"></i> 
                            <span>Back to Perks</span>
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

            <form action="{{ route('perks.update', $perk->id) }}" 
                  method="POST" 
                  enctype="multipart/form-data" 
                  id="perkForm" 
                  class="form-card">
                @csrf
                @method('PUT')
                
                <div class="perks-details-layout">
                    <!-- Left Panel - Main Form Fields -->
                    <div class="form-left-panel">
                        <!-- Perk Title -->
                        <div class="form-group">
                            <label for="title" class="form-label">Perk Title</label>
                            <input type="text" id="title" name="title" class="form-control" value="{{ old('title', $perk->title) }}" required>
                            <small style="color: var(--gray-500); font-size: 0.8125rem; display: block; margin-top: 0.375rem;">Give your perk a clear, descriptive title.</small>
                        </div>

                        <!-- Description -->
                        <div class="form-group">
                            <label for="description" class="form-label">Description</label>
                            <textarea id="description" name="description" class="form-control textarea-description" required>{{ old('description', $perk->description) }}</textarea>
                            <small style="color: var(--gray-500); font-size: 0.8125rem; display: block; margin-top: 0.375rem;">Describe the perk details, terms, or instructions for alumni.</small>
                        </div>

                        <!-- Status + Validity Date Row -->
                        <div class="form-row-2col">
                            <div class="form-group">
                                <label for="status" class="form-label">Status</label>
                                <select id="status" name="status" class="form-control">
                                    <option value="1" {{ old('status', (string) $perk->status) == '1' ? 'selected' : '' }}>Active</option>
                                    <option value="0" {{ old('status', (string) $perk->status) == '0' ? 'selected' : '' }}>Inactive</option>
                                </select>
                            </div>

                            <div class="form-group">
                                <label for="valid_until" class="form-label">Validity Date</label>
                                <input type="date" id="valid_until" name="valid_until" class="form-control" 
                                       min="{{ date('Y-m-d') }}" 
                                       value="{{ old('valid_until', $perk->valid_until ? $perk->valid_until->format('Y-m-d') : '') }}" required>
                                <small style="color: var(--gray-500); font-size: 0.8125rem; display: block; margin-top: 0.375rem;">Set when this perk expires.</small>
                            </div>
                        </div>
                    </div>

                    <!-- Right Panel - Attachments + Actions -->
                    <div class="form-right-panel">
                        <div class="attachments-card">
                            <h3 class="attachments-title">
                                <i class="fa-solid fa-paperclip"></i> Attachments
                                <span class="attachments-count">(Max 5)</span>
                            </h3>
                            
                            <div id="attachment-preview-container" class="attachment-grid">
                                <!-- Existing Images -->
                                @if($perk->images && $perk->images->isNotEmpty())
                                    @foreach($perk->images as $image)
                                        <div class="attachment-item existing-image" data-image-id="{{ $image->id }}">
                                            <img src="{{ $image->image_url }}" alt="Perk Image" class="attachment-img">
                                            <button type="button" class="remove-attachment-btn" onclick="markImageForRemoval(this)" title="Remove image">
                                                <i class="fa-solid fa-xmark"></i>
                                            </button>
                                        </div>
                                    @endforeach
                                @endif
                            </div>
                            
                            <input type="file" name="images[]" id="attachmentInput" accept="image/jpeg,image/png,image/webp" multiple style="display: none;">

                            <button type="button" class="add-attachment-btn" id="triggerFileInput">
                                <i class="fa-solid fa-cloud-arrow-up"></i>
                                Add Attachments
                            </button>
                            <p class="file-limit-info">Max 5 files • 5MB each • JPG, PNG, WEBP</p>
                        </div>

                        <!-- Form Actions -->
                        <div class="form-actions">
                            <a href="{{ route('perks.index') }}" class="btn btn-secondary">Cancel</a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fa-solid fa-check"></i>
                                <span>Update Perk</span>
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
    // Attachment Handling (global scope)
    // ========================================
    window.removedImageIds = [];

    window.markImageForRemoval = function(button) {
        window.showWarningModal({
            title: 'Remove Attachment',
            message: 'Are you sure you want to <strong>remove</strong> this attachment?<br><small>It will be permanently deleted when you save the changes.</small>',
            iconType: 'warning',
            confirmText: 'Remove',
            confirmClass: 'btn-warning',
            onConfirm: function() {
                const wrapper = button.closest('.attachment-item');
                const imageId = wrapper.dataset.imageId;

                if (wrapper.classList.contains('marked-for-removal')) {
                    // Undo removal
                    wrapper.classList.remove('marked-for-removal');
                    wrapper.style.opacity = '1';
                    const index = window.removedImageIds.indexOf(imageId);
                    if (index > -1) window.removedImageIds.splice(index, 1);
                    const hiddenInput = document.querySelector(`input[name="remove_existing[]"][value="${imageId}"]`);
                    if (hiddenInput) hiddenInput.remove();
                } else {
                    // Mark for removal
                    wrapper.classList.add('marked-for-removal');
                    wrapper.style.opacity = '0.4';
                    if (!window.removedImageIds.includes(imageId)) window.removedImageIds.push(imageId);
                    const form = document.getElementById('perkForm');
                    const hiddenInput = document.createElement('input');
                    hiddenInput.type = 'hidden';
                    hiddenInput.name = 'remove_existing[]';
                    hiddenInput.value = imageId;
                    form.appendChild(hiddenInput);
                }
            }
        });
    };

    // ========================================
    // INITIALIZE EVERYTHING ON DOM LOAD
    // ========================================
    document.addEventListener('DOMContentLoaded', function() {
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

        // ========================================
        // Attachment Handling
        // ========================================
        const fileInput = document.getElementById('attachmentInput');
        const triggerBtn = document.getElementById('triggerFileInput');
        const previewContainer = document.getElementById('attachment-preview-container');
        const MAX_FILES = 5;
        const MAX_SIZE = 5 * 1024 * 1024;

        if (triggerBtn && fileInput) {
            triggerBtn.addEventListener('click', function(e) {
                e.preventDefault();
                const existingCount = document.querySelectorAll('.attachment-item:not(.marked-for-removal)').length;
                if (existingCount >= MAX_FILES) {
                    window.showWarningModal({
                        title: 'Too Many Files',
                        message: `You can only have up to <strong>${MAX_FILES}</strong> images. Remove some existing images first.`,
                        iconType: 'warning',
                        confirmText: 'OK',
                        confirmClass: 'btn-warning',
                        hideCancel: true
                    });
                    return;
                }
                fileInput.click();
            });
        }

        fileInput.addEventListener('change', function(event) {
            const files = Array.from(event.target.files);
            const existingCount = document.querySelectorAll('.attachment-item:not(.marked-for-removal)').length;

            if (files.length + existingCount > MAX_FILES) {
                window.showWarningModal({
                    title: 'Too Many Files',
                    message: `You can only upload up to <strong>${MAX_FILES}</strong> images total.<br><small>You currently have ${existingCount} image(s).</small>`,
                    iconType: 'warning',
                    confirmText: 'OK',
                    confirmClass: 'btn-warning',
                    hideCancel: true
                });
                this.value = "";
                return;
            }

            document.querySelectorAll('.new-image-preview').forEach(el => el.remove());

            files.forEach((file) => {
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
                    return;
                }
                const reader = new FileReader();
                reader.onload = function(e) {
                    const wrapper = document.createElement('div');
                    wrapper.className = 'attachment-item new-image-preview';
                    wrapper.innerHTML = `<img src="${e.target.result}" class="attachment-img" style="border: 2px dashed var(--nu-gold);"><button type="button" class="remove-attachment-btn" onclick="this.parentElement.remove()" title="Remove"><i class="fa-solid fa-xmark"></i></button>`;
                    previewContainer.appendChild(wrapper);
                };
                reader.readAsDataURL(file);
            });
        });
    });
</script>

</body>
</html>