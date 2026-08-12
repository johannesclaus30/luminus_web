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
    <link rel="stylesheet" href="/css/admin-fixed-navbar.css">
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
            
            @php
                $currentAdmin = null;
                $adminId = session('admin_id');
                if ($adminId) {
                    $currentAdmin = \App\Models\Admin::find($adminId);
                }
                $accessibleModules = $currentAdmin ? $currentAdmin->getAccessibleModules() : [];
            @endphp

            <nav class="sidebar-nav">
                <p class="nav-section-title">Admin Menu</p>
                
                @if(isset($accessibleModules['dashboard']))
                <a href="/admin/dashboard" class="nav-item {{ request()->is('admin/dashboard') ? 'active' : '' }}">
                    <i class="fa-solid fa-chart-line"></i><span>Dashboard</span>
                </a>
                @endif
                
                @if(isset($accessibleModules['directory']))
                <a href="/admin/directory" class="nav-item {{ request()->is('admin/directory*') ? 'active' : '' }}">
                    <i class="fa-solid fa-users"></i><span>Alumni Directory</span>
                </a>
                @endif
                
                @if(isset($accessibleModules['announcements']))
                <a href="{{ route('announcements.index') }}" class="nav-item {{ request()->is('admin/announcements*') ? 'active' : '' }}">
                    <i class="fa-solid fa-bullhorn"></i><span>Announcements</span>
                </a>
                @endif
                
                @if(isset($accessibleModules['events']))
                <a href="{{ route('events.index') }}" class="nav-item {{ request()->is('admin/events*') ? 'active' : '' }}">
                    <i class="fa-solid fa-calendar-check"></i><span>Events</span>
                </a>
                @endif
                
                @if(isset($accessibleModules['perks']))
                <a href="{{ route('perks.index') }}" class="nav-item {{ request()->is('admin/perks*') ? 'active' : '' }}">
                    <i class="fa-solid fa-gift"></i><span>Perks & Discounts</span>
                </a>
                @endif
                
                @if(isset($accessibleModules['tracer']))
                <a href="/admin/alumni_tracer" class="nav-item {{ request()->is('admin/alumni_tracer*') ? 'active' : '' }}">
                    <i class="fa-solid fa-location-dot"></i><span>Alumni Tracer</span>
                </a>
                @endif
                
                @if(isset($accessibleModules['messages']))
                <a href="/admin/messages" class="nav-item {{ request()->is('admin/messages*') ? 'active' : '' }}">
                    <i class="fa-solid fa-envelope"></i><span>Messages</span>
                </a>
                @endif
                
                @if(isset($accessibleModules['settings']))
                <a href="{{ route('admin.settings') }}" class="nav-item {{ request()->is('admin/settings*') ? 'active' : '' }}">
                    <i class="fa-solid fa-gear"></i><span>Settings</span>
                </a>
                @endif
            </nav>
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
                                
                                    <div class="alumni-actions">
                                        <!-- Message Button - Direct link with alumni ID -->
                                        <a href="/admin/messages?chat={{ $alumnus->id }}" 
                                        class="btn-action btn-message" 
                                        title="Message {{ $displayName }}">
                                            <i class="fa-solid fa-comment-dots"></i>
                                        </a>
                                        
                                        <!-- View Profile Button -->
                                        <a href="{{ route('admin.alumni.show', $alumnus->id) }}" 
                                        class="btn-action btn-edit" 
                                        title="View Profile">
                                            <i class="fa-solid fa-eye"></i>
                                        </a>
                                        
                                        <!-- Manage Account Button -->
                                        <button type="button" 
                                                class="btn-action btn-info-action manage-btn" 
                                                data-id="{{ $alumnus->id }}" 
                                                data-name="{{ addslashes($displayName) }}"
                                                data-status="{{ $alumnus->account_status ?? 1 }}" 
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
                        <p class="modal-subtitle">Add an alumni record. A random temporary password will be emailed to the alumni.</p>
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
                                    <label for="year_graduated">Year Graduated *</label>
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
                                <strong>Note:</strong> A random temporary password will be generated and sent to the alumni's email address.
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

                    <!-- Bulk Import Section - Enhanced with Preview -->
                    <section class="modal-section" data-modal-section="bulk">
                        <div class="bulk-import-panel">
                            <!-- Step 1: Upload -->
                            <div id="uploadStep" class="bulk-step">
                                <div class="bulk-import-header">
                                    <div>
                                        <h3><i class="fa-solid fa-file-import"></i> Bulk Import</h3>
                                        <p>Upload a CSV file to create multiple alumni accounts at once.</p>
                                    </div>
                                    <span class="bulk-badge">CSV Only</span>
                                </div>

                                <div class="bulk-import-body">
                                    <div class="file-drop-zone" id="fileDropZone">
                                        <i class="fa-solid fa-cloud-arrow-up"></i>
                                        <p>Drag & drop your CSV file here</p>
                                        <span class="file-types">.csv only</span>
                                        <input id="bulkImportFile" type="file" accept=".csv" class="file-input" />
                                    </div>
                                    
                                    <!-- Template & Preview Buttons -->
                                    <div style="display: flex; gap: 1rem; justify-content: center; margin-top: 0.5rem; flex-wrap: wrap;">
                                        <button type="button" class="btn btn-secondary" onclick="downloadTemplate()">
                                            <i class="fa-solid fa-download"></i> Download Template
                                        </button>
                                        <button type="button" class="btn btn-primary" onclick="previewFile()" id="previewBtn" disabled>
                                            <i class="fa-solid fa-eye"></i> Preview Data
                                        </button>
                                    </div>
                                </div>
                                
                                <p id="bulkImportStatus" class="bulk-import-status">No file selected yet.</p>
                            </div>

                            <!-- Step 2: Preview & Edit -->
                            <div id="previewStep" class="bulk-step" style="display: none;">
                                <div class="preview-header">
                                    <div style="display: flex; justify-content: space-between; align-items: center; width: 100%; flex-wrap: wrap; gap: 0.75rem;">
                                        <div>
                                            <h4><i class="fa-solid fa-table"></i> Data Preview</h4>
                                            <p id="recordCount" style="font-size: 0.875rem; color: var(--gray-500); margin: 0.25rem 0 0 0;"></p>
                                        </div>
                                        <div style="display: flex; gap: 0.75rem; flex-wrap: wrap;">
                                            <button type="button" class="btn btn-secondary" onclick="backToUpload()">
                                                <i class="fa-solid fa-arrow-left"></i> Back
                                            </button>
                                            <button type="button" class="btn btn-primary" onclick="validateAndImport()" id="confirmImportBtn" disabled>
                                                <i class="fa-solid fa-check"></i> Import All
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                <!-- Column Mapping -->
                                <div id="columnMapping" style="margin-bottom: 1rem; padding: 1rem; background: var(--gray-50); border-radius: var(--radius-lg); border: 2px solid var(--gray-200);">
                                    <h5 style="margin: 0 0 0.5rem 0; color: var(--nu-blue); font-size: 0.9375rem;">
                                        <i class="fa-solid fa-code-branch"></i> Column Mapping
                                    </h5>
                                    <p style="font-size: 0.8125rem; color: var(--gray-500); margin: 0 0 0.75rem 0;">
                                        Map your CSV columns to database fields. Required fields are marked with <span style="color: var(--danger);">*</span>
                                    </p>
                                    <div id="mappingControls" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 0.75rem;">
                                        <!-- Dynamic mapping controls will be inserted here -->
                                    </div>
                                </div>
                                <!-- Editable Table -->
                                <div class="preview-table-wrapper">
                                    <div class="table-controls" style="display: flex; gap: 0.75rem; margin-bottom: 0.75rem; flex-wrap: wrap; align-items: center;">
                                        <div style="display: flex; gap: 0.5rem; align-items: center;">
                                            <span style="font-size: 0.8125rem; font-weight: 500;">Show:</span>
                                            <select id="pageSizeSelect" onchange="changePageSize()" style="padding: 0.375rem 0.75rem; border: 2px solid var(--gray-200); border-radius: var(--radius); font-size: 0.8125rem; font-family: inherit; background: var(--white);">
                                                <option value="10">10</option>
                                                <option value="25" selected>25</option>
                                                <option value="50">50</option>
                                                <option value="100">100</option>
                                                <option value="0">All Records</option>
                                            </select>
                                        </div>
                                        <div style="display: flex; gap: 0.5rem; margin-left: auto; flex-wrap: wrap;">
                                            <span id="paginationInfo" style="font-size: 0.8125rem; color: var(--gray-500);"></span>
                                            <button type="button" class="btn btn-sm btn-secondary" onclick="validateAllRows()">
                                                <i class="fa-solid fa-check-double"></i> Re-validate
                                            </button>
                                            <button type="button" class="btn btn-sm btn-secondary" onclick="clearAllErrors()">
                                                <i class="fa-solid fa-eraser"></i> Clear Errors
                                            </button>
                                        </div>
                                    </div>
                                    
                                    <div class="table-scroll-container">
                                        <table id="previewTable" class="editable-table" style="width: 100%; min-width: 800px; border-collapse: collapse; font-size: 0.875rem;">
                                            <thead style="position: sticky; top: 0; background: var(--nu-blue); color: white; z-index: 10;">
                                                <!-- Dynamic headers will be inserted here -->
                                            </thead>
                                            <tbody>
                                                <!-- Dynamic rows will be inserted here -->
                                            </tbody>
                                        </table>
                                    </div>
                                    
                                    <!-- Pagination Controls -->
                                    <div id="paginationControls" style="display: flex; justify-content: center; gap: 0.5rem; margin-top: 0.75rem; flex-wrap: wrap;">
                                        <!-- Dynamic pagination controls will be inserted here -->
                                    </div>
                                </div>

                                <!-- Validation Summary -->
                                <div id="validationSummary" style="margin-top: 1rem; padding: 1rem; border-radius: var(--radius-lg); background: var(--gray-50); border: 2px solid var(--gray-200); display: none;">
                                    <!-- Dynamic validation summary will be inserted here -->
                                </div>
                            </div>

                            <!-- Help Section -->
                            <div class="bulk-import-help" style="margin-top: 1.5rem; padding-top: 1.5rem; border-top: 2px solid var(--gray-200);">
                                <h4 style="font-size: 1rem; font-weight: 600; color: var(--nu-blue); margin: 0 0 0.75rem 0; display: flex; align-items: center; gap: 0.5rem;">
                                    <i class="fa-solid fa-lightbulb" style="color: var(--nu-gold);"></i> Required Columns
                                </h4>
                                <ul style="list-style: none; padding: 0; margin: 0 0 0.75rem 0;">
                                    <li style="font-size: 0.875rem; color: var(--gray-600); padding: 0.375rem 0 0.375rem 1.75rem; position: relative; line-height: 1.5;">
                                        <span style="position: absolute; left: 0; color: var(--danger); font-weight: 700;">*</span>
                                        <strong>Student ID</strong> (unique identifier)
                                    </li>
                                    <li style="font-size: 0.875rem; color: var(--gray-600); padding: 0.375rem 0 0.375rem 1.75rem; position: relative; line-height: 1.5;">
                                        <span style="position: absolute; left: 0; color: var(--danger); font-weight: 700;">*</span>
                                        <strong>First Name</strong>
                                    </li>
                                    <li style="font-size: 0.875rem; color: var(--gray-600); padding: 0.375rem 0 0.375rem 1.75rem; position: relative; line-height: 1.5;">
                                        <span style="position: absolute; left: 0; color: var(--danger); font-weight: 700;">*</span>
                                        <strong>Last Name</strong>
                                    </li>
                                    <li style="font-size: 0.875rem; color: var(--gray-600); padding: 0.375rem 0 0.375rem 1.75rem; position: relative; line-height: 1.5;">
                                        <span style="position: absolute; left: 0; color: var(--danger); font-weight: 700;">*</span>
                                        <strong>Email</strong> (for login)
                                    </li>
                                    <li style="font-size: 0.875rem; color: var(--gray-600); padding: 0.375rem 0 0.375rem 1.75rem; position: relative; line-height: 1.5;">
                                        <span style="position: absolute; left: 0; color: var(--danger); font-weight: 700;">*</span>
                                        <strong>Program</strong> (e.g., BS Computer Science)
                                    </li>
                                </ul>
                                <p class="help-note" style="font-size: 0.875rem; color: var(--gray-500); margin: 0; font-style: italic; padding: 0.75rem 1rem; background: var(--white); border-radius: var(--radius); border: 1px solid var(--gray-200);">
                                    <i class="fa-solid fa-circle-info" style="color: var(--info);"></i>
                                    Optional: Middle Name, Phone Number, Graduation Year, Date of Birth, Sex
                                </p>
                                <p class="help-note" style="font-size: 0.875rem; color: var(--gray-500); margin: 0.5rem 0 0 0; padding: 0.75rem 1rem; background: var(--info-light); border-radius: var(--radius); border: 1px solid var(--info);">
                                    <i class="fa-solid fa-pen-to-square" style="color: var(--info);"></i>
                                    You can edit any cell in the preview table before importing.
                                </p>
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
            <div class="modal-card" style="max-width: 550px; margin: 0 auto;">
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
                    <input type="hidden" id="manageAccountStatus" value="1">
                    
                    <div class="manage-action-group">
                        <!-- Reset Password - NOW ACTIVE -->
                        <div class="manage-action-item">
                            <div class="manage-action-icon icon-info">
                                <i class="fa-solid fa-key"></i>
                            </div>
                            <div class="manage-action-content">
                                <h4>Reset Password</h4>
                                <p>Generate a new temporary password. Alumni must change it on next login.</p>
                            </div>
                            <button type="button" class="btn btn-primary" onclick="prepareResetPassword()">
                                <i class="fa-solid fa-rotate"></i> Reset
                            </button>
                        </div>

                        <!-- Restrict Account - NOW ACTIVE -->
                        <div class="manage-action-item" id="restrictActionItem">
                            <div class="manage-action-icon icon-warning" id="restrictActionIcon">
                                <i class="fa-solid fa-user-slash"></i>
                            </div>
                            <div class="manage-action-content">
                                <h4 id="restrictTitle">Restrict Account</h4>
                                <p id="restrictDesc">Temporarily suspend access for this alumnus.</p>
                            </div>
                            <button type="button" class="btn btn-warning" id="manageRestrictBtn" onclick="prepareToggleRestrict()">
                                <i class="fa-solid fa-lock"></i> Restrict
                            </button>
                        </div>

                        <!-- Delete Account (Already Functional) -->
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

    <!-- Reset Password Confirmation Modal -->
    <div id="resetPasswordConfirmModal" class="modal-overlay" aria-hidden="true">
        <div class="modal-content-wrapper" style="max-width: 450px;">
            <div class="confirm-modal-card">
                <div class="confirm-icon-wrapper" style="background: #eff6ff; color: #3b82f6;">
                    <i class="fa-solid fa-key"></i>
                </div>
                <h3 class="confirm-title">Reset Password</h3>
                <p class="confirm-message">
                    Are you sure you want to reset the password for <strong id="resetConfirmName"></strong>?
                    <br><small>A new temporary password will be generated and emailed. The alumnus will be required to change it upon next login.</small>
                </p>
                <div class="confirm-actions">
                    <button type="button" class="btn btn-secondary" onclick="hideResetPasswordConfirm()">
                        Cancel
                    </button>
                    <button type="button" class="btn btn-primary" onclick="executeResetPassword()">
                        <i class="fa-solid fa-rotate"></i> Reset Password
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Restrict Account Confirmation Modal -->
    <div id="restrictConfirmModal" class="modal-overlay" aria-hidden="true">
        <div class="modal-content-wrapper" style="max-width: 450px;">
            <div class="confirm-modal-card">
                <div class="confirm-icon-wrapper" id="restrictConfirmIcon" style="background: #fef3c7; color: #d97706;">
                    <i class="fa-solid fa-user-slash"></i>
                </div>
                <h3 class="confirm-title" id="restrictConfirmTitle">Restrict Account</h3>
                <p class="confirm-message" id="restrictConfirmMessage">
                    Are you sure you want to restrict <strong id="restrictConfirmName"></strong>'s account?
                    <br><small>They will not be able to log in until unrestricted.</small>
                </p>
                <div class="confirm-actions">
                    <button type="button" class="btn btn-secondary" onclick="hideRestrictConfirm()">
                        Cancel
                    </button>
                    <button type="button" class="btn btn-warning" id="restrictConfirmBtn" onclick="executeToggleRestrict()">
                        <i class="fa-solid fa-lock"></i> Restrict
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
                if (record[key] !== undefined && record[key] !== null && record[key] !== '') {
                    formData.append(key, record[key]);
                }
            });

            const response = await fetch('{{ route('admin.alumni.store') }}', {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                },
                body: formData,
            });

            const payload = await response.json().catch(() => null);

            if (!response.ok) {
                // Extract validation errors
                let errorMessage = 'Unable to import record.';
                if (payload?.errors) {
                    const errorList = [];
                    Object.entries(payload.errors).forEach(([field, messages]) => {
                        errorList.push(`${field}: ${messages.join(', ')}`);
                    });
                    errorMessage = errorList.join(' | ');
                } else if (payload?.message) {
                    errorMessage = payload.message;
                }
                throw new Error(errorMessage);
            }

            return payload;
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
                const errors = [];

                status.textContent = `Importing ${records.length} record(s)...`;

                for (const record of records) {
                    try {
                        await createAlumniRecord(record);
                        created += 1;
                    } catch (error) {
                        failed += 1;
                        errors.push(`${record.first_name} ${record.last_name} (${record.student_id_number}): ${error.message}`);
                        console.error("Failed to import:", record.student_id_number, error.message);
                    }
                }

                // Show detailed results
                if (errors.length > 0) {
                    status.innerHTML = `✓ Import complete: ${created} created, ${failed} failed.<br><small style="color: var(--danger); font-size: 0.8rem;">${errors.slice(0, 3).join('<br>')}${errors.length > 3 ? '<br>...and ' + (errors.length - 3) + ' more errors' : ''}</small>`;
                    status.style.color = failed > created ? 'var(--danger)' : 'var(--warning)';
                } else {
                    status.textContent = `✓ Import complete: ${created} created successfully.`;
                    status.style.color = 'var(--success)';
                }

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
                const status = btn.dataset.status || 1; // Get status from data attribute
                openManageModal(id, name, status);
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

        // Add this function to your existing script section
        function redirectToMessages(alumniId, alumniName) {
            // Store the alumni info in sessionStorage so the messages page can pick it up
            sessionStorage.setItem('openChat', JSON.stringify({
                id: alumniId,
                name: alumniName,
                timestamp: Date.now()
            }));
            
            // Redirect to messages page
            window.location.href = '/admin/messages';
        }

        // ========================================
        // MANAGE MODAL - ENHANCED LOGIC
        // ========================================
        let currentManageAlumniId = null;
        let currentManageAlumniName = '';
        let currentManageAccountStatus = 1;

        // --- Manage Modal Functions ---
        function openManageModal(id, name, status) {
            // If status is not provided, default to 1 (active)
            const accountStatus = status !== undefined ? parseInt(status) : 1;
            
            currentManageAlumniId = id;
            currentManageAlumniName = name;
            currentManageAccountStatus = accountStatus;
            
            document.getElementById('manageAlumniId').value = id;
            document.getElementById('manageAlumniName').textContent = name;
            document.getElementById('manageAccountStatus').value = accountStatus;
            
            // Update restrict button based on current status
            updateRestrictButton(accountStatus);
            
            document.getElementById('manageModal').classList.add('active');
            document.body.style.overflow = 'hidden';
        }

        function hideManageModal() {
            document.getElementById('manageModal').classList.remove('active');
            document.body.style.overflow = '';
            currentManageAlumniId = null;
        }

        function updateRestrictButton(status) {
            const isRestricted = status == 0;
            const restrictTitle = document.getElementById('restrictTitle');
            const restrictDesc = document.getElementById('restrictDesc');
            const restrictBtn = document.getElementById('manageRestrictBtn');
            const restrictIcon = document.getElementById('restrictActionIcon');
            
            if (isRestricted) {
                restrictTitle.textContent = 'Unrestrict Account';
                restrictDesc.textContent = 'Restore access for this alumnus.';
                restrictBtn.innerHTML = '<i class="fa-solid fa-unlock"></i> Unrestrict';
                restrictBtn.className = 'btn btn-success';
                restrictIcon.innerHTML = '<i class="fa-solid fa-user-check"></i>';
            } else {
                restrictTitle.textContent = 'Restrict Account';
                restrictDesc.textContent = 'Temporarily suspend access for this alumnus.';
                restrictBtn.innerHTML = '<i class="fa-solid fa-lock"></i> Restrict';
                restrictBtn.className = 'btn btn-warning';
                restrictIcon.innerHTML = '<i class="fa-solid fa-user-slash"></i>';
            }
        }

        // ========================================
        // RESET PASSWORD
        // ========================================
        function prepareResetPassword() {
            const id = document.getElementById('manageAlumniId').value;
            const name = document.getElementById('manageAlumniName').textContent;
            
            document.getElementById('resetConfirmName').textContent = name;
            hideManageModal();
            document.getElementById('resetPasswordConfirmModal').classList.add('active');
            document.body.style.overflow = 'hidden';
        }

        function hideResetPasswordConfirm() {
            document.getElementById('resetPasswordConfirmModal').classList.remove('active');
            document.body.style.overflow = '';
        }

        async function executeResetPassword() {
            const id = document.getElementById('manageAlumniId').value;
            
            try {
                const response = await fetch(`/admin/alumni/${id}/reset-password`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    }
                });
                
                const data = await response.json();
                
                hideResetPasswordConfirm();
                
                if (data.success) {
                    showAlert(data.message, 'success');
                    setTimeout(() => window.location.reload(), 2000);
                } else {
                    showAlert(data.message || 'Failed to reset password.', 'error');
                }
            } catch (error) {
                console.error('Reset password error:', error);
                hideResetPasswordConfirm();
                showAlert('An error occurred while resetting the password.', 'error');
            }
        }

        // ========================================
        // RESTRICT / UNRESTRICT
        // ========================================
        function prepareToggleRestrict() {
            const id = document.getElementById('manageAlumniId').value;
            const name = document.getElementById('manageAlumniName').textContent;
            const isRestricted = currentManageAccountStatus == 0;
            
            document.getElementById('restrictConfirmName').textContent = name;
            
            if (isRestricted) {
                document.getElementById('restrictConfirmTitle').textContent = 'Unrestrict Account';
                document.getElementById('restrictConfirmMessage').innerHTML = 
                    'Are you sure you want to <strong>unrestrict</strong> ' + name + '\'s account?<br><small>They will be able to log in again.</small>';
                document.getElementById('restrictConfirmBtn').innerHTML = '<i class="fa-solid fa-unlock"></i> Unrestrict';
                document.getElementById('restrictConfirmBtn').className = 'btn btn-success';
                document.getElementById('restrictConfirmIcon').style.background = '#d1fae5';
                document.getElementById('restrictConfirmIcon').style.color = '#065f46';
                document.getElementById('restrictConfirmIcon').innerHTML = '<i class="fa-solid fa-user-check"></i>';
            } else {
                document.getElementById('restrictConfirmTitle').textContent = 'Restrict Account';
                document.getElementById('restrictConfirmMessage').innerHTML = 
                    'Are you sure you want to <strong>restrict</strong> ' + name + '\'s account?<br><small>They will not be able to log in until unrestricted.</small>';
                document.getElementById('restrictConfirmBtn').innerHTML = '<i class="fa-solid fa-lock"></i> Restrict';
                document.getElementById('restrictConfirmBtn').className = 'btn btn-warning';
                document.getElementById('restrictConfirmIcon').style.background = '#fef3c7';
                document.getElementById('restrictConfirmIcon').style.color = '#d97706';
                document.getElementById('restrictConfirmIcon').innerHTML = '<i class="fa-solid fa-user-slash"></i>';
            }
            
            hideManageModal();
            document.getElementById('restrictConfirmModal').classList.add('active');
            document.body.style.overflow = 'hidden';
        }

        function hideRestrictConfirm() {
            document.getElementById('restrictConfirmModal').classList.remove('active');
            document.body.style.overflow = '';
        }

        async function executeToggleRestrict() {
            const id = document.getElementById('manageAlumniId').value;
            const isCurrentlyRestricted = currentManageAccountStatus == 0;
            
            try {
                const response = await fetch(`/admin/alumni/${id}/toggle-restrict`, {
                    method: 'PATCH',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    }
                });
                
                const data = await response.json();
                
                hideRestrictConfirm();
                
                if (data.success) {
                    showAlert(data.message, data.warning ? 'warning' : 'success');
                    setTimeout(() => window.location.reload(), 2000);
                } else {
                    showAlert(data.message || 'Failed to update account status.', 'error');
                }
            } catch (error) {
                console.error('Toggle restrict error:', error);
                hideRestrictConfirm();
                showAlert('An error occurred while updating account status.', 'error');
            }
        }

        // ========================================
        // EXPORT FUNCTIONALITY
        // ========================================
        function exportAlumni() {
            // Show loading state
            const exportBtn = document.querySelector('.btn-secondary .fa-download')?.closest('button');
            if (exportBtn) {
                exportBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Exporting...';
                exportBtn.disabled = true;
            }
            
            // Create a hidden link and trigger download
            const link = document.createElement('a');
            link.href = '{{ route('admin.alumni.export') }}';
            link.download = 'alumni_export_' + new Date().toISOString().split('T')[0] + '.csv';
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
            
            // Reset button after delay
            setTimeout(() => {
                if (exportBtn) {
                    exportBtn.innerHTML = '<i class="fa-solid fa-download"></i> Export';
                    exportBtn.disabled = false;
                }
            }, 2000);
            
            showAlert('Exporting alumni data...', 'info');
        }

        // ========================================
        // KEYBOARD SHORTCUTS - UPDATE
        // ========================================
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                hideModal();
                hideManageModal();
                hideResetPasswordConfirm();
                hideRestrictConfirm();
                hideDeleteConfirm();
                hideAlert();
            }
        });

        // ========================================
        // CLOSE MODALS ON OVERLAY CLICK
        // ========================================
        document.getElementById('resetPasswordConfirmModal')?.addEventListener('click', function(e) {
            if (e.target === this) hideResetPasswordConfirm();
        });

        document.getElementById('restrictConfirmModal')?.addEventListener('click', function(e) {
            if (e.target === this) hideRestrictConfirm();
        });

        document.getElementById('manageModal')?.addEventListener('click', function(e) {
            if (e.target === this) hideManageModal();
        });

        // ========================================
        // HANDLE SUCCESS MESSAGES FROM SERVER
        // ========================================
        document.addEventListener('DOMContentLoaded', function() {
            // Check for session flash messages
            @if(session('success'))
                showAlert('{{ session('success') }}', 'success');
            @endif
            
            @if(session('error'))
                showAlert('{{ session('error') }}', 'error');
            @endif
        });

        // ========================================
// ENHANCED BULK IMPORT WITH PREVIEW
// ========================================

let importedData = [];
let currentPage = 1;
let pageSize = 25;
let columnMapping = {};
let validationResults = {};
let fileHeaders = [];

// --- Download Template ---
function downloadTemplate() {
    const headers = [
        'Student ID', 'First Name', 'Middle Name', 'Last Name',
        'Email', 'Phone Number', 'Program', 'Graduation Year',
        'Date of Birth', 'Sex'
    ];
    
    const sample = [
        '2020-00001', 'Jane', 'Dela', 'Cruz',
        'jane.cruz@email.com', '09123456789', 'BS Computer Science',
        '2024-06-01', '2000-01-15', 'Female'
    ];
    
    // Create CSV content with BOM for Excel compatibility
    const BOM = '\uFEFF';
    const csvContent = BOM + headers.join(',') + '\n' + sample.join(',') + '\n';
    
    const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8' });
    const link = document.createElement('a');
    link.href = URL.createObjectURL(blob);
    link.download = 'alumni_import_template.csv';
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
    URL.revokeObjectURL(link.href);
    
    showAlert('Template downloaded successfully!', 'success');
}

// --- Preview File Function ---
async function previewFile() {
    const fileInput = document.getElementById('bulkImportFile');
    const file = fileInput?.files[0];
    const status = document.getElementById('bulkImportStatus');
    
    if (!file) {
        showAlert('Please select a file first.', 'error');
        return;
    }

    status.textContent = 'Reading file...';
    status.style.color = 'var(--warning)';

    try {
        const buffer = await file.arrayBuffer();
        const workbook = XLSX.read(buffer, { type: 'array', cellDates: true });
        const sheet = workbook.Sheets[workbook.SheetNames[0]];
        
        // Get all data as array of arrays with proper headers
        const rawData = XLSX.utils.sheet_to_json(sheet, { header: 1, raw: false, defval: '' });
        
        if (!rawData || rawData.length < 2) {
            status.textContent = 'Error: File is empty or contains only headers.';
            status.style.color = 'var(--danger)';
            return;
        }

        // Find header row
        let headerRowIndex = -1;
        for (let i = 0; i < Math.min(rawData.length, 10); i++) {
            const row = rawData[i] || [];
            const rowString = row.join(' ').toLowerCase();
            // Check for common header keywords
            const hasStudentId = rowString.includes('student id') || rowString.includes('studentid');
            const hasLastName = rowString.includes('last name') || rowString.includes('lastname');
            const hasFirstName = rowString.includes('first name') || rowString.includes('firstname');
            const hasEmail = rowString.includes('email') || rowString.includes('e-mail');
            
            if (hasStudentId && hasLastName && hasFirstName && hasEmail) {
                headerRowIndex = i;
                break;
            }
        }

        if (headerRowIndex === -1) {
            status.textContent = 'Error: Could not find required headers. Ensure file has "Student ID", "Last Name", "First Name", and "Email" columns.';
            status.style.color = 'var(--danger)';
            return;
        }

        // Extract headers and data
        const headers = rawData[headerRowIndex].map(h => String(h || '').trim());
        const dataRows = [];
        
        for (let i = headerRowIndex + 1; i < rawData.length; i++) {
            const row = rawData[i] || [];
            // Skip empty rows
            if (row.every(cell => !cell || String(cell).trim() === '')) continue;
            
            const rowData = {};
            headers.forEach((header, index) => {
                const value = row[index] || '';
                rowData[header] = typeof value === 'string' ? value.trim() : String(value).trim();
            });
            dataRows.push(rowData);
        }

        if (dataRows.length === 0) {
            status.textContent = 'Error: No data rows found after headers.';
            status.style.color = 'var(--danger)';
            return;
        }

        // Store data for preview
        importedData = dataRows;
        fileHeaders = headers;
        
        // Show preview step
        document.getElementById('uploadStep').style.display = 'none';
        document.getElementById('previewStep').style.display = 'block';
        document.getElementById('bulkImportStatus').textContent = `Loaded ${dataRows.length} records for preview.`;
        document.getElementById('bulkImportStatus').style.color = 'var(--success)';
        
        // Render preview table
        renderPreviewTable(dataRows);
        renderColumnMapping(headers);
        validateAllRows();
        
    } catch (error) {
        console.error('Preview error:', error);
        status.textContent = 'Error reading file: ' + error.message;
        status.style.color = 'var(--danger)';
    }
}

// --- Render Preview Table ---
function renderPreviewTable(data) {
    const table = document.getElementById('previewTable');
    const thead = table.querySelector('thead');
    const tbody = table.querySelector('tbody');
    
    // Clear existing content
    thead.innerHTML = '';
    tbody.innerHTML = '';
    
    // Determine which columns to show (based on mapping or all)
    const columnsToShow = getVisibleColumns();
    
    // If no columns to show, use all file headers
    if (columnsToShow.length === 0 && fileHeaders.length > 0) {
        columnsToShow = fileHeaders;
    }
    
    // Build header
    const headerRow = document.createElement('tr');
    
    // Add row number column
    const numTh = document.createElement('th');
    numTh.textContent = '#';
    numTh.style.width = '50px';
    numTh.style.minWidth = '50px';
    numTh.style.maxWidth = '50px';
    numTh.style.textAlign = 'center';
    numTh.style.position = 'sticky';
    numTh.style.left = '0';
    numTh.style.zIndex = '11';
    numTh.style.background = 'var(--nu-blue)';
    headerRow.appendChild(numTh);
    
    // Add status column
    const statusTh = document.createElement('th');
    statusTh.textContent = 'Status';
    statusTh.style.width = '70px';
    statusTh.style.minWidth = '70px';
    statusTh.style.maxWidth = '70px';
    statusTh.style.textAlign = 'center';
    statusTh.style.position = 'sticky';
    statusTh.style.left = '50px';
    statusTh.style.zIndex = '11';
    statusTh.style.background = 'var(--nu-blue)';
    headerRow.appendChild(statusTh);
    
    // Data columns - make them scrollable
    columnsToShow.forEach((col, index) => {
        const th = document.createElement('th');
        th.textContent = col;
        th.dataset.column = col;
        th.style.minWidth = '150px';
        th.style.maxWidth = '300px';
        th.style.position = 'sticky';
        th.style.top = '0';
        th.style.background = 'var(--nu-blue)';
        th.style.zIndex = '10';
        headerRow.appendChild(th);
    });
    
    thead.appendChild(headerRow);
    
    // Calculate pagination
    const total = data.length;
    const totalPages = Math.ceil(total / pageSize);
    
    // Ensure current page is valid
    if (currentPage > totalPages) currentPage = totalPages;
    if (currentPage < 1) currentPage = 1;
    
    const start = (currentPage - 1) * pageSize;
    const end = Math.min(start + pageSize, total);
    const pageData = data.slice(start, end);
    
    // Build rows
    pageData.forEach((row, index) => {
        const actualIndex = start + index;
        const tr = document.createElement('tr');
        tr.dataset.rowIndex = actualIndex;
        tr.id = `row-${actualIndex}`;
        
        // Row number - sticky
        const numTd = document.createElement('td');
        numTd.className = 'row-number';
        numTd.textContent = actualIndex + 1;
        numTd.style.position = 'sticky';
        numTd.style.left = '0';
        numTd.style.zIndex = '5';
        numTd.style.background = 'var(--white)';
        tr.appendChild(numTd);
        
        // Status - sticky
        const statusTd = document.createElement('td');
        statusTd.className = 'row-status';
        statusTd.id = `status-${actualIndex}`;
        statusTd.style.position = 'sticky';
        statusTd.style.left = '50px';
        statusTd.style.zIndex = '5';
        statusTd.style.background = 'var(--white)';
        statusTd.innerHTML = '<i class="fa-regular fa-hourglass-half" style="color: var(--gray-400);"></i>';
        tr.appendChild(statusTd);
        
        // Data cells
        columnsToShow.forEach(col => {
            const td = document.createElement('td');
            const input = document.createElement('input');
            input.type = 'text';
            input.value = row[col] || '';
            input.dataset.column = col;
            input.dataset.rowIndex = actualIndex;
            input.className = 'editable-cell';
            input.placeholder = 'Enter value...';
            input.id = `cell-${actualIndex}-${col}`;
            input.style.width = '100%';
            input.style.minWidth = '120px';
            
            input.addEventListener('input', function() {
                handleCellEdit(this, actualIndex, col);
            });
            
            input.addEventListener('blur', function() {
                validateSingleRow(actualIndex);
            });
            
            td.appendChild(input);
            tr.appendChild(td);
        });
        
        tbody.appendChild(tr);
    });
    
    // Update record count with pagination info
    let countText = `Showing ${start + 1}-${end} of ${total} records`;
    if (totalPages > 1) {
        countText += ` (Page ${currentPage} of ${totalPages})`;
    }
    document.getElementById('recordCount').textContent = countText;
    
    // Render pagination controls
    renderPaginationControls();
}

// --- Get Visible Columns ---
function getVisibleColumns() {
  
    return fileHeaders; // <-- This saves the day!
}

// --- Render Column Mapping ---
function renderColumnMapping(headers) {
    const container = document.getElementById('mappingControls');
    container.innerHTML = '';
    
    const requiredFields = {
        'student_id_number': 'Student ID *',
        'first_name': 'First Name *',
        'last_name': 'Last Name *',
        'email': 'Email *',
        'program': 'Program *'
    };
    
    const optionalFields = {
        'middle_name': 'Middle Name',
        'phone_number': 'Phone Number',
        'year_graduated': 'Graduation Year',
        'date_of_birth': 'Date of Birth',
        'sex': 'Sex'
    };
    
    const allFields = { ...requiredFields, ...optionalFields };
    
    Object.entries(allFields).forEach(([field, label]) => {
        const div = document.createElement('div');
        div.className = 'mapping-item';
        
        const labelEl = document.createElement('label');
        labelEl.className = 'mapping-label';
        labelEl.textContent = label;
        div.appendChild(labelEl);
        
        const select = document.createElement('select');
        select.id = `map-${field}`;
        select.dataset.field = field;
        
        const defaultOption = document.createElement('option');
        defaultOption.value = '';
        defaultOption.textContent = '-- Select Column --';
        select.appendChild(defaultOption);
        
        headers.forEach(header => {
            const option = document.createElement('option');
            option.value = header;
            option.textContent = header;
            
            // Auto-detect matching columns
            const headerLower = header.toLowerCase();
            const fieldLower = field.toLowerCase();
            if (headerLower.includes(fieldLower.replace('_', ' ')) || 
                headerLower === fieldLower ||
                headerLower.replace(' ', '_') === fieldLower) {
                option.selected = true;
            }
            
            select.appendChild(option);
        });
        
        select.addEventListener('change', function() {
            columnMapping[this.dataset.field] = this.value;
            renderPreviewTable(importedData);
            validateAllRows();
        });
        
        div.appendChild(select);
        container.appendChild(div);
    });
}

// --- Cell Edit Handler ---
function handleCellEdit(input, rowIndex, column) {
    const value = input.value;
    importedData[rowIndex][column] = value;
    
    // Remove any existing error state
    input.classList.remove('error', 'success');
    const errorMsg = input.parentElement.querySelector('.error-message');
    if (errorMsg) errorMsg.remove();
}

// --- Validate Single Row ---
function validateSingleRow(rowIndex) {
    const row = importedData[rowIndex];
    if (!row) return;
    
    const errors = [];
    const mappedFields = getFieldMapping();
    
    // Check required fields
    const required = ['student_id_number', 'first_name', 'last_name', 'email', 'program'];
    required.forEach(field => {
        const col = mappedFields[field];
        if (!col) {
            errors.push(`Missing mapping for ${field}`);
            return;
        }
        const value = row[col] || '';
        if (!value.trim()) {
            errors.push(`${field.replace('_', ' ')} is required`);
        }
    });
    
    // Validate email format
    if (mappedFields.email && row[mappedFields.email]) {
        const email = row[mappedFields.email];
        if (!email.match(/^[^\s@]+@[^\s@]+\.[^\s@]+$/)) {
            errors.push('Invalid email format');
        }
    }
    
    // Update UI
    const statusTd = document.getElementById(`status-${rowIndex}`);
    const tr = statusTd?.closest('tr');
    const inputs = tr?.querySelectorAll('.editable-cell');
    
    if (errors.length > 0) {
        statusTd.innerHTML = '<i class="fa-solid fa-times-circle"></i>';
        tr?.classList.add('error-row');
        tr?.classList.remove('success-row');
        
        // Highlight fields with errors
        inputs?.forEach(input => {
            const col = input.dataset.column;
            const field = findFieldByColumn(col);
            if (field && required.includes(field)) {
                if (!input.value.trim()) {
                    input.classList.add('error');
                }
            }
        });
    } else {
        statusTd.innerHTML = '<i class="fa-solid fa-check-circle"></i>';
        tr?.classList.remove('error-row');
        tr?.classList.add('success-row');
        
        // Clear error states
        inputs?.forEach(input => {
            input.classList.remove('error');
        });
    }
    
    // Store validation result
    validationResults[rowIndex] = { errors, valid: errors.length === 0 };
}

// --- Validate All Rows ---
function validateAllRows() {
    const total = importedData.length;
    let validCount = 0;
    let errorCount = 0;
    
    for (let i = 0; i < total; i++) {
        validateSingleRow(i);
        if (validationResults[i]?.valid) {
            validCount++;
        } else {
            errorCount++;
        }
    }
    
    // Update summary
    updateValidationSummary(validCount, errorCount, total);
    
    // Enable/disable import button
    const importBtn = document.getElementById('confirmImportBtn');
    if (errorCount === 0 && validCount > 0) {
        importBtn.disabled = false;
        importBtn.style.opacity = '1';
    } else {
        importBtn.disabled = true;
        importBtn.style.opacity = '0.6';
    }
}

// --- Update Validation Summary ---
function updateValidationSummary(valid, errors, total) {
    const summary = document.getElementById('validationSummary');
    summary.style.display = 'block';
    
    summary.innerHTML = `
        <h5 style="margin: 0 0 0.75rem 0; color: var(--nu-blue);">
            <i class="fa-solid fa-clipboard-check"></i> Validation Summary
        </h5>
        <div class="validation-summary-grid">
            <div class="validation-stat success">
                <span class="stat-number">${valid}</span>
                <span class="stat-label">Valid Records</span>
            </div>
            <div class="validation-stat error">
                <span class="stat-number">${errors}</span>
                <span class="stat-label">Invalid Records</span>
            </div>
            <div class="validation-stat info">
                <span class="stat-number">${total}</span>
                <span class="stat-label">Total Records</span>
            </div>
        </div>
        ${errors > 0 ? `
            <div style="margin-top: 0.75rem; padding: 0.75rem; background: var(--danger-light); border-radius: var(--radius); border-left: 4px solid var(--danger);">
                <p style="font-size: 0.875rem; color: var(--danger); margin: 0;">
                    <i class="fa-solid fa-triangle-exclamation"></i>
                    ${errors} record(s) have errors. Please fix them before importing.
                </p>
            </div>
        ` : `
            <div style="margin-top: 0.75rem; padding: 0.75rem; background: var(--success-light); border-radius: var(--radius); border-left: 4px solid var(--success);">
                <p style="font-size: 0.875rem; color: var(--success); margin: 0;">
                    <i class="fa-solid fa-check-circle"></i>
                    All ${total} record(s) are valid and ready for import!
                </p>
            </div>
        `}
    `;
}

// --- Helper Functions ---
function getFieldMapping() {
    const mapping = {};
    
    // Combine BOTH required and optional fields so no data gets left behind during import!
    const allFields = [
        'student_id_number', 'first_name', 'last_name', 'email', 'program',
        'middle_name', 'phone_number', 'year_graduated', 'date_of_birth', 'sex'
    ];
    
    allFields.forEach(field => {
        const select = document.getElementById(`map-${field}`);
        // Only map it if the user (or the auto-detect) actually selected a column
        if (select && select.value !== '') {
            mapping[field] = select.value;
        }
    });
    
    return mapping;
}

function findFieldByColumn(column) {
    const mapping = getFieldMapping();
    for (const [field, col] of Object.entries(mapping)) {
        if (col === column) return field;
    }
    return null;
}

// --- Navigation Functions ---
function backToUpload() {
    document.getElementById('uploadStep').style.display = 'block';
    document.getElementById('previewStep').style.display = 'none';
    document.getElementById('bulkImportStatus').textContent = 'Returned to upload.';
    document.getElementById('bulkImportStatus').style.color = 'var(--gray-500)';
}

// --- Render Pagination Controls ---
function renderPaginationControls() {
    const container = document.getElementById('paginationControls');
    if (!container) return;
    
    const total = importedData.length;
    const totalPages = Math.ceil(total / pageSize);
    
    // Don't show pagination if all records are shown or only 1 page
    if (pageSize >= total || totalPages <= 1) {
        container.innerHTML = '';
        return;
    }
    
    let html = '';
    
    // Previous button
    html += `<button class="btn btn-sm btn-secondary" onclick="goToPage(${currentPage - 1})" ${currentPage <= 1 ? 'disabled' : ''}>
        <i class="fa-solid fa-chevron-left"></i>
    </button>`;
    
    // Page numbers
    const maxVisible = 7;
    let startPage = Math.max(1, currentPage - Math.floor(maxVisible / 2));
    let endPage = Math.min(totalPages, startPage + maxVisible - 1);
    
    if (endPage - startPage < maxVisible - 1) {
        startPage = Math.max(1, endPage - maxVisible + 1);
    }
    
    if (startPage > 1) {
        html += `<button class="btn btn-sm btn-secondary" onclick="goToPage(1)">1</button>`;
        if (startPage > 2) {
            html += `<span style="color: var(--gray-400); padding: 0 0.25rem;">...</span>`;
        }
    }
    
    for (let i = startPage; i <= endPage; i++) {
        html += `<button class="btn btn-sm ${i === currentPage ? 'btn-primary' : 'btn-secondary'}" onclick="goToPage(${i})">
            ${i}
        </button>`;
    }
    
    if (endPage < totalPages) {
        if (endPage < totalPages - 1) {
            html += `<span style="color: var(--gray-400); padding: 0 0.25rem;">...</span>`;
        }
        html += `<button class="btn btn-sm btn-secondary" onclick="goToPage(${totalPages})">${totalPages}</button>`;
    }
    
    // Next button
    html += `<button class="btn btn-sm btn-secondary" onclick="goToPage(${currentPage + 1})" ${currentPage >= totalPages ? 'disabled' : ''}>
        <i class="fa-solid fa-chevron-right"></i>
    </button>`;
    
    // Page info
    const start = (currentPage - 1) * pageSize + 1;
    const end = Math.min(currentPage * pageSize, total);
    html += `<span style="font-size: 0.8125rem; color: var(--gray-500); margin-left: 0.5rem;">
        ${start}-${end} of ${total}
    </span>`;
    
    container.innerHTML = html;
}


// --- Change Page Size ---
function changePageSize() {
    const select = document.getElementById('pageSizeSelect');
    const value = parseInt(select.value);
    
    if (value === 0) {
        // Show all records
        pageSize = importedData.length || 25;
        currentPage = 1;
    } else {
        pageSize = value;
        currentPage = 1;
    }
    
    renderPreviewTable(importedData);
    renderPaginationControls();
    validateAllRows();
}

// --- Validate and Import ---
async function validateAndImport() {
    // Double-check all rows are valid
    const total = importedData.length;
    let allValid = true;
    let invalidCount = 0;
    
    for (let i = 0; i < total; i++) {
        if (!validationResults[i]?.valid) {
            allValid = false;
            invalidCount++;
        }
    }
    
    if (!allValid) {
        showAlert(`Please fix ${invalidCount} error(s) before importing.`, 'error');
        return;
    }
    
    // Confirm with user
    if (!confirm(`Ready to import ${total} alumni records. Continue?`)) {
        return;
    }
    
    const status = document.getElementById('bulkImportStatus');
    const importBtn = document.getElementById('confirmImportBtn');
    importBtn.disabled = true;
    importBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Importing...';
    
    // Get mapping
    const mapping = getFieldMapping();
    
    // Prepare data for server - send the actual importedData with the mapping
    const payload = {
        data: importedData,
        mapping: mapping,
        _token: getCsrfToken()
    };
    
    try {
        const response = await fetch('/admin/alumni/process-bulk', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            },
            body: JSON.stringify(payload)
        });
        
        const result = await response.json();
        
        if (result.success) {
            const { results } = result;
            
            let message = `Import complete! ✅ ${results.successful} successful, ❌ ${results.failed} failed.`;
            
            if (results.duplicates && results.duplicates.length > 0) {
                message += `\n⚠️ ${results.duplicates.length} duplicates skipped.`;
            }
            
            if (results.warnings && results.warnings.length > 0) {
                message += `\n⚠️ ${results.warnings.length} warnings.`;
            }
            
            if (results.errors && results.errors.length > 0) {
                const errorList = results.errors.slice(0, 5).join('\n');
                message += `\n\nErrors:\n${errorList}`;
                if (results.errors.length > 5) {
                    message += `\n...and ${results.errors.length - 5} more errors.`;
                }
            }
            
            status.innerHTML = message.replace(/\n/g, '<br>');
            status.style.color = results.failed > 0 ? 'var(--warning)' : 'var(--success)';
            
            if (results.successful > 0) {
                showAlert(`Successfully imported ${results.successful} alumni!`, 'success');
                setTimeout(() => window.location.reload(), 2000);
            } else if (results.failed > 0) {
                showAlert('Import completed but no records were added. Check errors above.', 'warning');
            }
        } else {
            status.textContent = 'Error: ' + (result.message || 'Unknown error occurred.');
            status.style.color = 'var(--danger)';
            showAlert('Import failed: ' + result.message, 'error');
        }
        
    } catch (error) {
        console.error('Import error:', error);
        status.textContent = 'Error: ' + error.message;
        status.style.color = 'var(--danger)';
        showAlert('An error occurred during import.', 'error');
    } finally {
        importBtn.disabled = false;
        importBtn.innerHTML = '<i class="fa-solid fa-check"></i> Import All';
    }
}

// --- Go to Page ---
function goToPage(page) {
    const totalPages = Math.ceil(importedData.length / pageSize);
    if (page < 1 || page > totalPages) return;
    currentPage = page;
    renderPreviewTable(importedData);
    renderPaginationControls();
    validateAllRows();
}

// --- Update Import Progress ---
function updateImportProgress(current, total) {
    const status = document.getElementById('bulkImportStatus');
    const percentage = Math.round((current / total) * 100);
    status.textContent = `Importing... ${current}/${total} (${percentage}%)`;
    status.style.color = 'var(--info)';
}

// --- Clear All Errors ---
function clearAllErrors() {
    document.querySelectorAll('.error-row').forEach(tr => {
        tr.classList.remove('error-row');
    });
    document.querySelectorAll('.editable-cell.error').forEach(input => {
        input.classList.remove('error');
    });
    showAlert('Error indicators cleared.', 'info');
}

// --- Helper: Get CSRF Token ---
function getCsrfToken() {
    return document.querySelector('meta[name="csrf-token"]')?.content || '';
}

// --- Override the file input change event ---
document.addEventListener('DOMContentLoaded', function() {
    const fileInput = document.getElementById('bulkImportFile');
    const previewBtn = document.getElementById('previewBtn');
    const status = document.getElementById('bulkImportStatus');
    
    fileInput.addEventListener('change', function(e) {
        if (e.target.files[0]) {
            const fileName = e.target.files[0].name;
            status.textContent = `Selected: ${fileName}`;
            status.style.color = 'var(--success)';
            previewBtn.disabled = false;
            
            // Enable preview button and auto-preview if file is selected
            setTimeout(() => {
                previewBtn.click();
            }, 500);
        }
    });
});

    </script>
</body>
</html>