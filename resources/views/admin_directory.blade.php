<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Alumni Directory | LumiNUs Admin</title>

    <!-- Fonts & Icons -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    
    <!-- SheetJS for Excel/CSV parsing -->
    <script src="https://cdn.sheetjs.com/xlsx-latest/package/dist/xlsx.full.min.js"></script>

    <!-- Stylesheets -->
    <link rel="stylesheet" href="/css/admin.css">
    <link rel="stylesheet" href="/css/directory_modern.css">
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
                <a href="{{ route('admin.directory') }}" class="nav-item active">
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
                            <i class="fa-solid fa-users"></i>
                            Alumni Directory
                        </h1>
                        <p class="page-subtitle">Manage and connect with NU Lipa alumni members</p>
                    </div>
                    <div class="header-actions">
                        <button class="btn btn-primary" onclick="showModal()">
                            <i class="fa-solid fa-user-plus"></i> 
                            <span>Create Account</span>
                        </button>
                        <button class="btn btn-secondary" onclick="exportAlumni()">
                            <i class="fa-solid fa-download"></i> 
                            <span>Export</span>
                        </button>
                    </div>
                </div>
            </header>

            <!-- Stats Overview -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon-wrapper">
                        <div class="stat-icon">
                            <i class="fa-solid fa-users"></i>
                        </div>
                    </div>
                    <div class="stat-info">
                        <span class="stat-value">{{ $totalAlumni ?? 0 }}</span>
                        <span class="stat-label">Total Alumni</span>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon-wrapper">
                        <div class="stat-icon active">
                            <i class="fa-solid fa-graduation-cap"></i>
                        </div>
                    </div>
                    <div class="stat-info">
                        <span class="stat-value">{{ $recentGraduates ?? 0 }}</span>
                        <span class="stat-label">Recent Graduates</span>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon-wrapper">
                        <div class="stat-icon">
                            <i class="fa-solid fa-building-columns"></i>
                        </div>
                    </div>
                    <div class="stat-info">
                        <span class="stat-value">{{ $uniquePrograms ?? 0 }}</span>
                        <span class="stat-label">Programs</span>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon-wrapper">
                        <div class="stat-icon views">
                            <i class="fa-regular fa-envelope"></i>
                        </div>
                    </div>
                    <div class="stat-info">
                        <span class="stat-value">{{ $withEmails ?? 0 }}</span>
                        <span class="stat-label">With Email</span>
                    </div>
                </div>
            </div>

            <!-- Search & Actions Bar -->
            <div class="directory-toolbar">
                <div class="search-wrapper">
                    <i class="fa-solid fa-magnifying-glass search-icon"></i>
                    <input id="searchInput" type="text" 
                           placeholder="Search alumni by name, email, or program..." 
                           class="search-bar" 
                           oninput="filterAlumni()">
                    <button id="clearSearch" class="clear-search" onclick="clearSearch()" title="Clear search">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>
                <div class="toolbar-actions">
                    <span class="results-count" id="resultsCount">
                        {{ $alumni->count() }} result{{ $alumni->count() != 1 ? 's' : '' }}
                    </span>
                </div>
            </div>

            <!-- Alumni Card Grid -->
            <div class="alumni-grid" id="alumniGrid">
                @forelse ($alumni as $alumnus)
                    @php
                        $firstName = $alumnus->first_name ?? '';
                        $middleName = $alumnus->middle_name ?? '';
                        $lastName = $alumnus->last_name ?? '';
                        $email = $alumnus->email ?? '';
                        $program = $alumnus->program ?? '';
                        $graduationYear = optional($alumnus->year_graduated)->format('Y') ?: 'N/A';
                        $middleInitial = $middleName !== '' ? strtoupper(mb_substr(trim($middleName), 0, 1)) . '.' : '';
                        
                        // Photo URL logic
                        $photoPath = trim((string) ($alumnus->alumni_photo ?: $alumnus->card_photo));
                        if ($photoPath === '') {
                            $photoUrl = '/assets/FINAL-NULIPA.jpg';
                        } elseif (preg_match('/^https?:\/\//i', $photoPath)) {
                            $photoUrl = $photoPath;
                        } elseif (str_starts_with($photoPath, '/storage/')) {
                            $photoUrl = $photoPath;
                        } elseif (str_starts_with($photoPath, 'storage/')) {
                            $photoUrl = '/' . $photoPath;
                        } elseif (str_starts_with($photoPath, '/')) {
                            $photoUrl = $photoPath;
                        } elseif (trim((string) config('filesystems.disks.s3.url')) !== '') {
                            $photoUrl = rtrim((string) config('filesystems.disks.s3.url'), '/') . '/' . ltrim($photoPath, '/');
                        } else {
                            $photoUrl = asset('storage/' . ltrim($photoPath, '/'));
                        }

                        $displayName = trim($firstName . ' ' . ($middleInitial ? $middleInitial . ' ' : '') . $lastName);
                        $initials = strtoupper(mb_substr($firstName, 0, 1) . mb_substr($lastName, 0, 1));
                    @endphp
                    <article class="alumni-card" 
                             data-name="{{ strtolower($displayName) }}" 
                             data-email="{{ strtolower($email) }}"
                             data-program="{{ strtolower($program) }}">
                        <div class="alumni-card-wrapper">
                            <div class="alumni-card-header">
                                <div class="alumni-photo-wrapper">
                                    <img src="{{ $photoUrl }}" 
                                         alt="{{ $displayName ?: 'Alumni photo' }}" 
                                         class="alumni-photo"
                                         onerror="this.src='/assets/FINAL-NULIPA.jpg'">
                                    @if(empty($photoPath))
                                        <span class="photo-initials">{{ $initials }}</span>
                                    @endif
                                </div>
                                <div class="alumni-quick-actions">
                                    <a href="{{ route('admin.alumni.edit', $alumnus->id) }}" 
                                       class="quick-action-btn" 
                                       title="View & Edit Profile">
                                        <i class="fa-solid fa-pen-to-square"></i>
                                    </a>
                                </div>
                            </div>
                            
                            <div class="alumni-card-body">
                                <div class="alumni-identity">
                                    <h3 class="alumni-name">{{ $displayName ?: 'Unnamed Alumni' }}</h3>
                                    <p class="alumni-program">
                                        <i class="fa-solid fa-graduation-cap"></i>
                                        {{ $program ?: 'Program not specified' }}
                                    </p>
                                </div>
                                
                                <div class="alumni-meta">
                                    <div class="meta-item">
                                        <i class="fa-regular fa-calendar"></i>
                                        <span>Graduated: {{ $graduationYear }}</span>
                                    </div>
                                    @if($alumnus->student_id_number)
                                    <div class="meta-item">
                                        <i class="fa-solid fa-id-card"></i>
                                        <span>ID: {{ $alumnus->student_id_number }}</span>
                                    </div>
                                    @endif
                                </div>
                                
                                <div class="alumni-contact">
                                    <div class="contact-item">
                                        <i class="fa-regular fa-envelope"></i>
                                        <span class="contact-value">{{ $email ?: 'No email' }}</span>
                                    </div>
                                    @if($alumnus->phone_number)
                                    <div class="contact-item">
                                        <i class="fa-solid fa-phone"></i>
                                        <span class="contact-value">{{ $alumnus->phone_number }}</span>
                                    </div>
                                    @endif
                                </div>
                            </div>
                            
                            <div class="alumni-card-footer">
                                <div class="alumni-status">
                                    <span class="status-dot active"></span>
                                    <span>Active Member</span>
                                </div>
                                
                                <!-- Find this block in your directory page -->
                                <div class="alumni-actions">
                                    <button type="button" class="btn-action btn-reserved" title="Coming Soon" disabled>
                                        <i class="fa-solid fa-comment-dots"></i>
                                    </button>
                                    
                                    <!-- CHANGE THE ROUTE AND TITLE HERE -->
                                    <a href="{{ route('admin.alumni.show', $alumnus->id) }}" 
                                    class="btn-action btn-edit" 
                                    title="View Profile">
                                        <i class="fa-solid fa-eye"></i>
                                    </a>
                                    
                                    <!-- Replace your current 'i' button with this -->
                                    <button type="button" class="btn-action btn-info-action manage-btn" 
                                            data-id="{{ $alumnus->id }}" 
                                            data-name="{{ $displayName }}" 
                                            title="Manage Account">
                                        <i class="fa-solid fa-circle-info"></i>
                                    </button>
                                </div>

                            </div>
                        </div>
                    </article>
                @empty
                    <div class="empty-state full-width">
                        <div class="empty-icon-wrapper">
                            <div class="empty-icon">
                                <i class="fa-solid fa-users"></i>
                            </div>
                        </div>
                        <h3 class="empty-title">No alumni records found</h3>
                        <p class="empty-description">
                            Start building your alumni network by creating the first alumni account.
                        </p>
                        <button onclick="showModal()" class="btn btn-primary btn-lg">
                            <i class="fa-solid fa-user-plus"></i> 
                            <span>Create First Alumni</span>
                        </button>
                    </div>
                @endforelse
            </div>

            <!-- Pagination -->
            @if (isset($alumni) && method_exists($alumni, 'links'))
            <div class="pagination-wrapper">
                {{ $alumni->links() }}
            </div>
            @endif
        </main>
    </div>

    <!-- Create Alumni Modal -->
    <div id="createModal" class="modal-overlay" aria-hidden="true">
        <div class="modal-content-wrapper">
            <div class="modal-card">
                <div class="modal-header">
                    <div>
                        <h2 class="modal-title">
                            <i class="fa-solid fa-user-plus"></i>
                            Create Alumni Account
                        </h2>
                        <p class="modal-subtitle">Add an alumni record. Initial password: <strong>password123</strong></p>
                    </div>
                    <button class="modal-close" onclick="hideModal()" title="Close">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>

                <div class="modal-tabbar" role="tablist" aria-label="Alumni creation options">
                    <button type="button" class="modal-tab active" data-modal-tab="single" onclick="switchModalMode('single')">
                        <i class="fa-solid fa-user"></i>
                        <span>Individual</span>
                    </button>
                    <button type="button" class="modal-tab" data-modal-tab="bulk" onclick="switchModalMode('bulk')">
                        <i class="fa-solid fa-file-csv"></i>
                        <span>Bulk Import</span>
                    </button>
                </div>

                <div class="modal-body">
                    <!-- Single Creation Form -->
                    <section class="modal-section modal-section-active" data-modal-section="single">
                        <form id="singleCreateForm" method="POST" action="{{ route('admin.alumni.store') }}" enctype="multipart/form-data">
                            @csrf

                            {{-- ADD THIS BLOCK TO SHOW VALIDATION ERRORS --}}
                            @if ($errors->any())
                                <div style="background-color: #fee2e2; color: #991b1b; padding: 12px; border-radius: 6px; margin-bottom: 15px; border: 1px solid #fca5a5;">
                                    <strong> Please fix the following errors:</strong>
                                    <ul style="margin: 5px 0 0 20px; padding: 0;">
                                        @foreach ($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif
                            {{-- END ERROR BLOCK --}}

                            <div class="form-grid">
                                <div class="form-group">
                                    <label for="first_name">First Name *</label>
                                    <input id="first_name" name="first_name" value="{{ old('first_name') }}" required placeholder="Jane">
                                </div>
                                <div class="form-group">
                                    <label for="middle_name">Middle Name</label>
                                    <input id="middle_name" name="middle_name" value="{{ old('middle_name') }}" placeholder="Dela">
                                </div>
                            </div>

                            <div class="form-grid">
                                <div class="form-group">
                                    <label for="last_name">Last Name *</label>
                                    <input id="last_name" name="last_name" value="{{ old('last_name') }}" required placeholder="Cruz">
                                </div>
                                <div class="form-group">
                                    <label for="student_id_number">Student ID *</label>
                                    <input id="student_id_number" name="student_id_number" value="{{ old('student_id_number') }}" required placeholder="2020-00001">
                                </div>
                            </div>

                            <div class="form-grid">
                                <div class="form-group">
                                    <label for="date_of_birth">Date of Birth</label>
                                    <input id="date_of_birth" name="date_of_birth" value="{{ old('date_of_birth') }}" type="date">
                                </div>
                                <div class="form-group">
                                    <label for="year_graduated">Year Graduated</label>
                                    <input id="year_graduated" name="year_graduated" value="{{ old('year_graduated') }}" type="date">
                                </div>
                            </div>

                            <div class="form-grid">
                                <div class="form-group">
                                    <label for="sex">Sex</label>
                                    <select id="sex" name="sex">
                                        <option value="">Select sex</option>
                                        <option value="Male" @selected(old('sex') === 'Male')>Male</option>
                                        <option value="Female" @selected(old('sex') === 'Female')>Female</option>
                                        <option value="Prefer not to say" @selected(old('sex') === 'Prefer not to say')>Prefer not to say</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label for="program">Program *</label>
                                    <input id="program" name="program" value="{{ old('program') }}" required placeholder="BS Computer Science">
                                </div>
                            </div>

                            <div class="form-grid">
                                <div class="form-group">
                                    <label for="email">Email *</label>
                                    <input id="email" name="email" value="{{ old('email') }}" type="email" required placeholder="email@example.com">
                                </div>
                                <div class="form-group">
                                    <label for="phone_number">Phone Number *</label>
                                    <input id="phone_number" name="phone_number" value="{{ old('phone_number') }}" required placeholder="09xx xxx xxxx">
                                </div>
                            </div>

                            <div class="form-group full-width">
                                <label for="card_photo">Card Photo</label>
                                <div class="file-upload-wrapper">
                                    <input id="card_photo" name="card_photo" type="file" accept="image/*">
                                    <span class="file-upload-label">Choose file...</span>
                                </div>
                                <p class="form-hint">Upload the alumni card photo. Stored in <code>luminus_assets/card_photo/</code></p>
                            </div>

                            <div class="form-note">
                                <i class="fa-solid fa-circle-info"></i>
                                <strong>Testing password:</strong> All new accounts use <span class="password-hint">password123</span>
                            </div>

                            <div class="modal-actions">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fa-solid fa-user-plus"></i>
                                    <span>Create Account</span>
                                </button>
                                <button type="button" class="btn btn-secondary" onclick="hideModal()">Cancel</button>
                            </div>
                        </form>
                    </section>

                    <!-- Bulk Import Section -->
                    <section class="modal-section" data-modal-section="bulk">
                        <div class="bulk-import-panel">
                            <div class="bulk-import-header">
                                <div>
                                    <h3><i class="fa-solid fa-file-import"></i> Bulk Import</h3>
                                    <p>Upload a CSV or Excel file to create multiple alumni accounts at once.</p>
                                </div>
                                <span class="bulk-badge">CSV / XLSX</span>
                            </div>

                            <div class="bulk-import-body">
                                <div class="file-drop-zone" id="fileDropZone">
                                    <i class="fa-solid fa-cloud-arrow-up"></i>
                                    <p>Drag & drop your file here</p>
                                    <span class="file-types">.csv, .xls, .xlsx</span>
                                    <input id="bulkImportFile" type="file" accept=".csv,.xls,.xlsx" class="file-input" />
                                </div>
                                <button type="button" class="btn btn-primary" onclick="handleBulkImport()">
                                    <i class="fa-solid fa-upload"></i>
                                    <span>Import File</span>
                                </button>
                            </div>

                            <p id="bulkImportStatus" class="bulk-import-status">No file selected yet.</p>
                            
                            <div class="bulk-import-help">
                                <h4><i class="fa-solid fa-lightbulb"></i> Required Columns</h4>
                                <ul>
                                    <li><strong>Student ID</strong> (unique identifier)</li>
                                    <li><strong>First Name</strong> & <strong>Last Name</strong></li>
                                    <li><strong>Email</strong> (for login)</li>
                                    <li><strong>Program</strong> (e.g., BS Computer Science)</li>
                                </ul>
                                <p class="help-note">Optional: Middle Name, Phone, Graduation Year, Date of Birth, Sex</p>
                            </div>
                        </div>
                    </section>
                </div>
            </div>
        </div>
    </div>

    <!-- Manage Alumni Modal -->
    <div id="manageModal" class="modal-overlay" aria-hidden="true">
        <div class="modal-content-wrapper">
            <div class="modal-card" style="max-width: 550px;">
                <div class="modal-header">
                    <div>
                        <h2 class="modal-title">
                            <i class="fa-solid fa-user-gear"></i>
                            Manage Account
                        </h2>
                        <p class="modal-subtitle">Account actions for <strong id="manageAlumniName"></strong></p>
                    </div>
                    <button class="modal-close" onclick="hideManageModal()" title="Close">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>
                <div class="modal-body" style="padding: 1.5rem;">
                    <input type="hidden" id="manageAlumniId">
                    
                    <div class="manage-action-group">
                        <!-- Reset Password (Reserved) -->
                        <div class="manage-action-item">
                            <div class="manage-action-icon icon-info">
                                <i class="fa-solid fa-key"></i>
                            </div>
                            <div class="manage-action-content">
                                <h4>Reset Password</h4>
                                <p>Reset the account password to the default or a custom one.</p>
                            </div>
                            <button type="button" class="btn btn-secondary" disabled title="Coming Soon">
                                Reserved
                            </button>
                        </div>

                        <!-- Restrict Account (Reserved) -->
                        <div class="manage-action-item">
                            <div class="manage-action-icon icon-warning">
                                <i class="fa-solid fa-user-slash"></i>
                            </div>
                            <div class="manage-action-content">
                                <h4>Restrict Account</h4>
                                <p>Temporarily suspend or restrict access for this alumnus.</p>
                            </div>
                            <button type="button" class="btn btn-secondary" disabled title="Coming Soon">
                                Reserved
                            </button>
                        </div>

                        <!-- Delete Account (Active) -->
                        <div class="manage-action-item danger-zone">
                            <div class="manage-action-icon icon-danger">
                                <i class="fa-solid fa-trash-can"></i>
                            </div>
                            <div class="manage-action-content danger-text">
                                <h4>Delete Account</h4>
                                <p>Permanently remove this alumni record from the system.</p>
                            </div>
                            <button type="button" class="btn btn-danger" onclick="prepareDelete()">
                                <i class="fa-solid fa-trash"></i> Delete
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div id="deleteConfirmModal" class="modal-overlay" aria-hidden="true">
        <div class="modal-content-wrapper" style="max-width: 450px;">
            <div class="confirm-modal-card">
                <div class="confirm-icon-wrapper">
                    <i class="fa-solid fa-triangle-exclamation"></i>
                </div>
                <h3 class="confirm-title">Delete Account</h3>
                <p class="confirm-message">
                    Are you sure you want to delete <strong id="confirmAlumniName"></strong>? 
                    <br>This action cannot be undone.
                </p>
                <div class="confirm-actions">
                    <button type="button" class="btn btn-secondary" onclick="hideDeleteConfirm()">
                        Cancel
                    </button>
                    <button type="button" class="btn btn-danger" onclick="executeDelete()">
                        <i class="fa-solid fa-trash"></i> Delete
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Alert Toast -->
    <div id="alertToast" class="alert-toast">
        <i class="alert-icon fa-solid fa-circle-check"></i>
        <span class="alert-message"></span>
        <button class="alert-close" onclick="hideAlert()"><i class="fa-solid fa-xmark"></i></button>
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

        // Search filter function
        function filterAlumni() {
            const q = document.getElementById('searchInput').value.toLowerCase().trim();
            const cards = document.querySelectorAll('.alumni-card');
            let visibleCount = 0;
            
            cards.forEach(card => {
                const name = (card.dataset.name || '').toLowerCase();
                const email = (card.dataset.email || '').toLowerCase();
                const program = (card.dataset.program || '').toLowerCase();
                const visible = !q || name.includes(q) || email.includes(q) || program.includes(q);
                
                card.style.display = visible ? 'flex' : 'none';
                if (visible) visibleCount++;
            });
            
            // Update results count
            document.getElementById('resultsCount').textContent = 
                `${visibleCount} result${visibleCount != 1 ? 's' : ''}`;
            
            // Show/hide clear button
            document.getElementById('clearSearch').style.display = q ? 'flex' : 'none';
        }

        function clearSearch() {
            document.getElementById('searchInput').value = '';
            filterAlumni();
            document.getElementById('searchInput').focus();
        }

        // Modal functions
        function showModal() {
            document.getElementById('createModal').classList.add('active');
            document.body.style.overflow = 'hidden';
        }

        function hideModal() {
            document.getElementById('createModal').classList.remove('active');
            document.body.style.overflow = '';
        }


        function switchModalMode(mode) {
            // Update tabs
            document.querySelectorAll('[data-modal-tab]').forEach((tab) => {
                tab.classList.toggle('active', tab.dataset.modalTab === mode);
            });

            // Update sections
            document.querySelectorAll('[data-modal-section]').forEach((section) => {
                section.classList.toggle('modal-section-active', section.dataset.modalSection === mode);
            });
        }

        // File upload preview
        document.getElementById('card_photo')?.addEventListener('change', function(e) {
            const fileName = e.target.files[0]?.name;
            const label = this.closest('.file-upload-wrapper')?.querySelector('.file-upload-label');
            if (label && fileName) {
                label.textContent = fileName;
            }
        });

        // Bulk import file drop zone
        const dropZone = document.getElementById('fileDropZone');
        const fileInput = document.getElementById('bulkImportFile');
        
        if (dropZone && fileInput) {
            ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
                dropZone.addEventListener(eventName, preventDefaults, false);
            });

            function preventDefaults(e) {
                e.preventDefault();
                e.stopPropagation();
            }

            ['dragenter', 'dragover'].forEach(eventName => {
                dropZone.addEventListener(eventName, () => dropZone.classList.add('drag-over'), false);
            });

            ['dragleave', 'drop'].forEach(eventName => {
                dropZone.addEventListener(eventName, () => dropZone.classList.remove('drag-over'), false);
            });

            dropZone.addEventListener('drop', handleDrop, false);

            function handleDrop(e) {
                const dt = e.dataTransfer;
                const files = dt.files;
                if (files[0]) {
                    fileInput.files = files;
                    updateFileName(files[0].name);
                }
            }

            fileInput.addEventListener('change', function(e) {
                if (e.target.files[0]) {
                    updateFileName(e.target.files[0].name);
                }
            });

            function updateFileName(name) {
                const status = document.getElementById('bulkImportStatus');
                if (status) {
                    status.textContent = `Selected: ${name}`;
                    status.style.color = 'var(--success)';
                }
            }
        }

        // Bulk import logic (same as original, adapted)
        function normalizeBulkRow(row) {
            const findKey = (obj, possibleKeys) => {
                for (const key of possibleKeys) {
                    if (obj[key] !== undefined) return obj[key];
                    const found = Object.keys(obj).find(k => k.toLowerCase() === key.toLowerCase());
                    if (found) return obj[found];
                }
                return '';
            };

            return {
                first_name: findKey(row, ['First Name', 'first_name', 'FirstName']).trim(),
                middle_name: findKey(row, ['Middle Name', 'middle_name', 'MiddleName']).trim(),
                last_name: findKey(row, ['Last Name', 'last_name', 'LastName']).trim(),
                student_id_number: findKey(row, ['Student ID', 'student_id_number', 'StudentID', 'Student ID Number']).trim(),
                program: findKey(row, ['Strand', 'program', 'Program', 'Department']).trim(),
                email: findKey(row, ['Personal Email', 'Official Email', 'email', 'Email', 'E-mail']).trim(),
                phone_number: findKey(row, ['Mobile No', 'phone_number', 'MobileNo', 'Mobile Number', 'Phone']).trim(),
                year_graduated: findKey(row, ['Graduation Period', 'year_graduated', 'GraduationPeriod', 'Year Graduated']).trim(),
                date_of_birth: '',
                sex: findKey(row, ['Sex', 'sex', 'Gender']).trim(),
            };
        }

        async function createAlumniRecord(record) {
            const formData = new FormData();
            formData.append('_token', '{{ csrf_token() }}');
            
            Object.keys(record).forEach(key => {
                formData.append(key, record[key]);
            });

            const response = await fetch('{{ route('admin.alumni.store') }}', {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                },
                body: formData,
            });

            if (!response.ok) {
                const payload = await response.json().catch(() => null);
                const message = payload?.message || 'Unable to import one or more alumni records.';
                throw new Error(message);
            }
        }

        async function handleBulkImport() {
            const fileInput = document.getElementById('bulkImportFile');
            const status = document.getElementById('bulkImportStatus');
            const file = fileInput?.files[0];

            if (!file) {
                showAlert('Please choose a CSV or Excel file first.', 'error');
                return;
            }

            status.textContent = 'Reading file...';
            status.style.color = 'var(--warning)';

            if (!window.XLSX) {
                status.textContent = 'Excel parsing library is unavailable.';
                return;
            }

            try {
                const buffer = await file.arrayBuffer();
                const workbook = XLSX.read(buffer, { type: 'array', cellDates: true });
                const sheet = workbook.Sheets[workbook.SheetNames[0]];
                
                const rawData = XLSX.utils.sheet_to_json(sheet, { header: 1, raw: false });
                
                let headerRowIndex = -1;
                for (let i = 0; i < rawData.length; i++) {
                    const row = rawData[i] || [];
                    const rowString = row.join(' ').toLowerCase();
                    if (rowString.includes('student id') && rowString.includes('last name')) {
                        headerRowIndex = i;
                        break;
                    }
                }

                if (headerRowIndex === -1) {
                    status.textContent = 'Error: Could not find headers. Ensure file has "Student ID" and "Last Name" columns.';
                    status.style.color = 'var(--danger)';
                    return;
                }

                const rows = XLSX.utils.sheet_to_json(sheet, { 
                    range: headerRowIndex, 
                    defval: '', 
                    raw: false,
                    dateNF: 'yyyy-mm-dd'
                });

                const records = rows
                    .map(normalizeBulkRow)
                    .filter((record) => record.first_name && record.last_name && record.student_id_number);

                if (!records.length) {
                    status.textContent = 'No valid alumni records found.';
                    status.style.color = 'var(--danger)';
                    return;
                }

                let created = 0;
                let failed = 0;

                status.textContent = `Importing ${records.length} record(s)...`;

                for (const record of records) {
                    try {
                        await createAlumniRecord(record);
                        created += 1;
                    } catch (error) {
                        failed += 1;
                        console.error("Failed to import:", record.student_id_number, error.message);
                    }
                }

                status.textContent = `✓ Import complete: ${created} created${failed ? `, ${failed} failed` : ''}.`;
                status.style.color = created > 0 ? 'var(--success)' : 'var(--danger)';

                if (created > 0) {
                    setTimeout(() => window.location.reload(), 1500);
                }
            } catch (error) {
                console.error('Import error:', error);
                status.textContent = 'Error: ' + error.message;
                status.style.color = 'var(--danger)';
            }
        }

        // Export function (placeholder)
        function exportAlumni() {
            showAlert('Export feature coming soon!', 'info');
        }

        // Alert toast system
        function showAlert(message, type = 'success') {
            const toast = document.getElementById('alertToast');
            const icon = toast.querySelector('.alert-icon');
            const msg = toast.querySelector('.alert-message');
            
            // Set icon and color based on type
            const icons = {
                success: 'fa-circle-check',
                error: 'fa-circle-exclamation',
                info: 'fa-circle-info',
                warning: 'fa-circle-exclamation'
            };
            const colors = {
                success: 'var(--success)',
                error: 'var(--danger)',
                info: 'var(--info)',
                warning: 'var(--warning)'
            };
            
            icon.className = `alert-icon fa-solid ${icons[type] || icons.success}`;
            toast.style.borderColor = colors[type] || colors.success;
            msg.textContent = message;
            
            toast.classList.add('show');
            
            // Auto hide after 4 seconds
            setTimeout(() => hideAlert(), 4000);
        }

        function hideAlert() {
            document.getElementById('alertToast').classList.remove('show');
        }

        // Close modal when clicking outside
        document.getElementById('createModal')?.addEventListener('click', function(e) {
            if (e.target === this) hideModal();
        });

        // Close delete confirm modal when clicking outside
        document.getElementById('deleteConfirmModal')?.addEventListener('click', function(e) {
            if (e.target === this) hideDeleteConfirm();
        });

        // Close sidebar on nav item click (mobile)
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
                    document.getElementById('adminSidebar')?.classList.remove('mobile-open');
                    document.getElementById('mobileOverlay')?.classList.remove('active');
                    document.body.style.overflow = '';
                }
            }, 250);
        });

        // Initialize search clear button visibility
        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.getElementById('searchInput');
            const clearBtn = document.getElementById('clearSearch');
            if (searchInput && clearBtn) {
                clearBtn.style.display = searchInput.value ? 'flex' : 'none';
            }
        });

        // --- Manage Modal Functions ---
        function openManageModal(id, name) {
            document.getElementById('manageAlumniId').value = id;
            document.getElementById('manageAlumniName').textContent = name;
            document.getElementById('manageModal').classList.add('active');
            document.body.style.overflow = 'hidden';
        }

        function hideManageModal() {
            document.getElementById('manageModal').classList.remove('active');
            document.body.style.overflow = '';
        }

        // Close manage modal when clicking outside
    document.getElementById('manageModal')?.addEventListener('click', function(e) {
        if (e.target === this) hideManageModal();
    });

        
        // Change to:
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                hideModal();
                hideManageModal(); // Added this
                hideAlert();
            }
            if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
                e.preventDefault();
                document.getElementById('searchInput')?.focus();
            }
        });

        // Safely handle clicks on the 'i' button without breaking HTML quotes
        document.addEventListener('click', function(e) {
            // Check if the clicked element (or its parent) is our manage button
            const btn = e.target.closest('.manage-btn');
            
            if (btn) {
                const id = btn.dataset.id;
                const name = btn.dataset.name;
                openManageModal(id, name);
            }
        });

                // --- Delete Alumni Functions ---
        
        // Variable to hold the ID temporarily
        let pendingDeleteId = null;

        // --- Delete Confirmation Functions ---
        function prepareDelete() {
            const id = document.getElementById('manageAlumniId').value;
            const name = document.getElementById('manageAlumniName').textContent;
            
            pendingDeleteId = id;
            document.getElementById('confirmAlumniName').textContent = name;
            
            hideManageModal();
            document.getElementById('deleteConfirmModal').classList.add('active');
        }

        function hideDeleteConfirm() {
            document.getElementById('deleteConfirmModal').classList.remove('active');
            document.body.style.overflow = '';
            pendingDeleteId = null;
        }

        // 3. This is the actual deletion logic (called by the "Delete" button in the confirm modal)
        async function executeDelete() {
            if (!pendingDeleteId) return;

            const formData = new FormData();
            formData.append('_method', 'DELETE');
            formData.append('_token', '{{ csrf_token() }}');

            try {
                const response = await fetch(`/admin/alumni/${pendingDeleteId}`, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                });

                if (response.ok) {
                    hideDeleteConfirm();
                    showAlert('Alumni account deleted successfully.', 'success');
                    setTimeout(() => window.location.reload(), 1500);
                } else {
                    const data = await response.json().catch(() => null);
                    showAlert(data?.message || 'Failed to delete account.', 'error');
                    hideDeleteConfirm();
                }
            } catch (error) {
                console.error('Delete error:', error);
                showAlert('An error occurred.', 'error');
                hideDeleteConfirm();
            }
        }

    </script>
</body>
</html>