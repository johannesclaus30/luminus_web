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

            <!-- Account Management Quick Access -->
            <div class="account-quick-links">
                <div>
                    <span class="quick-links-label">
                        <i class="fa-solid fa-shield-halved"></i>
                        Account Management
                    </span>
                    
                    <a href="/admin/directory/archived" class="btn-sm-link archived">
                        <i class="fa-solid fa-box-archive"></i> 
                        Archived
                        <span class="badge-count has-items">{{ \App\Models\Alumni::onlyTrashed()->count() }}</span>
                    </a>
                    
                    <a href="/admin/directory/restricted" class="btn-sm-link restricted">
                        <i class="fa-solid fa-user-slash"></i> 
                        Restricted
                        <span class="badge-count has-items">{{ \App\Models\Alumni::whereNull('deleted_at')->where('account_status', 0)->count() }}</span>
                    </a>
                </div>
            </div>
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

            <!-- Search & Filter Toolbar - Unified -->
            <div class="directory-toolbar">
                <div class="search-wrapper">
                    <i class="fa-solid fa-magnifying-glass search-icon"></i>
                    <input id="searchInput" type="text" 
                        placeholder="Search by name, email, program..." 
                        class="search-bar" 
                        oninput="performFilter()">
                    <button id="clearSearch" class="clear-search" onclick="clearSearch()" title="Clear search">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>
                
                <div class="toolbar-actions">
                    <!-- Quick Type Filters -->
                    <div class="quick-filters">
                        <button class="filter-pill active" data-filter="all" onclick="applyQuickFilter('all')" title="Show all alumni">
                            <span>All</span>
                            <span class="filter-count" id="countAll">0</span>
                        </button>
                        <button class="filter-pill" data-filter="college" onclick="applyQuickFilter('college')" title="Show college alumni">
                            <i class="fa-solid fa-graduation-cap"></i>
                            <span>College</span>
                            <span class="filter-count" id="countCollege">0</span>
                        </button>
                        <button class="filter-pill" data-filter="shs" onclick="applyQuickFilter('shs')" title="Show SHS alumni">
                            <i class="fa-solid fa-school"></i>
                            <span>SHS</span>
                            <span class="filter-count" id="countShs">0</span>
                        </button>
                    </div>
                    
                    <!-- Advanced Filter Toggle -->
                    <button class="btn-filter-toggle" onclick="toggleAdvancedFilters()" title="Advanced filters">
                        <i class="fa-solid fa-sliders"></i>
                        <span>Filters</span>
                        <span class="filter-badge" id="activeFilterBadge" style="display:none;">0</span>
                    </button>
                </div>
            </div>

            <!-- Advanced Filters Panel - Integrated -->
            <div class="advanced-filters" id="advancedFilters" style="display:none;">
                <div class="filter-grid">
                    <!-- Program Filter -->
                    <div class="filter-group">
                        <label for="filterProgram">
                            <i class="fa-solid fa-building-columns"></i> Program
                        </label>
                        <select id="filterProgram" onchange="applyAdvancedFilters()">
                            <option value="">All Programs</option>
                        </select>
                    </div>
                    
                    <!-- Year Graduated Filter -->
                    <div class="filter-group">
                        <label for="filterYear">
                            <i class="fa-regular fa-calendar"></i> Year Graduated
                        </label>
                        <select id="filterYear" onchange="applyAdvancedFilters()">
                            <option value="">All Years</option>
                        </select>
                    </div>
                    
                    <!-- Sort By -->
                    <div class="filter-group">
                        <label for="filterSort">
                            <i class="fa-solid fa-arrow-up-wide-short"></i> Sort By
                        </label>
                        <select id="filterSort" onchange="applyAdvancedFilters()">
                            <option value="name_asc">Name (A-Z)</option>
                            <option value="name_desc">Name (Z-A)</option>
                            <option value="year_desc">Year Graduated (Newest)</option>
                            <option value="year_asc">Year Graduated (Oldest)</option>
                        </select>
                    </div>
                    
                    <!-- Filter Actions -->
                    <div class="filter-group filter-actions-group">
                        <label>&nbsp;</label>
                        <div class="filter-actions">
                            <button class="btn btn-secondary btn-sm" onclick="resetFilters()">
                                <i class="fa-solid fa-undo"></i> Reset
                            </button>
                            <button class="btn btn-primary btn-sm" onclick="applyAdvancedFilters(); toggleAdvancedFilters();">
                                <i class="fa-solid fa-check"></i> Apply
                            </button>
                        </div>
                    </div>
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
                            data-id="{{ $alumnus->id }}"
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
                                    @if($alumnus->alumni_type == 'shs')
                                        <span class="alumni-type-badge shs">
                                            <i class="fa-solid fa-school"></i>
                                            SHS
                                        </span>
                                    @elseif($alumnus->alumni_type == 'college')
                                        <span class="alumni-type-badge college">
                                            <i class="fa-solid fa-graduation-cap"></i>
                                            College
                                        </span>
                                    @else
                                        <span class="alumni-type-badge unknown">
                                            <i class="fa-solid fa-question-circle"></i>
                                            Unknown
                                        </span>
                                    @endif
                                </div>
                                <!-- alumni-actions remains the same -->
                                <div class="alumni-actions">
                                    <!-- Message Button -->
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
    <div id="manageModal" class="modal-overlay" aria-hidden="true" inert>
        <div class="modal-content-wrapper">
            <div class="modal-card" style="max-width: 650px; margin: 0 auto;">
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
                        <!-- Reset Password -->
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

                        <!-- Restrict/Unrestrict Account -->
                        <div class="manage-action-item" id="restrictActionItem">
                            <div class="manage-action-icon icon-warning" id="restrictActionIcon">
                                <i class="fa-solid fa-user-slash"></i>
                            </div>
                            <div class="manage-action-content">
                                <h4 id="restrictTitle">Restrict Account</h4>
                                <p id="restrictDesc">Temporarily suspend access for this alumnus.</p>
                            </div>
                            <button type="button" class="btn btn-warning" id="manageRestrictBtn" onclick="openRestrictModal()">
                                <i class="fa-solid fa-lock"></i> Restrict
                            </button>
                        </div>

                        <!-- Archive Account (Replaces Delete) -->
                        <div class="manage-action-item">
                            <div class="manage-action-icon icon-archive" style="background: #fef3c7; color: #d97706;">
                                <i class="fa-solid fa-box-archive"></i>
                            </div>
                            <div class="manage-action-content">
                                <h4>Archive Account</h4>
                                <p>Soft-delete this account. The alumni will not be able to log in.</p>
                            </div>
                            <button type="button" class="btn btn-warning" onclick="prepareArchive()">
                                <i class="fa-solid fa-box-archive"></i> Archive
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Restriction Reason Modal -->
    <div id="restrictReasonModal" class="modal-overlay" aria-hidden="true" inert>
        <div class="modal-content-wrapper" style="max-width: 550px;">
            <div class="modal-card">
                <div class="modal-header">
                    <div>
                        <h2 class="modal-title">
                            <i class="fa-solid fa-user-slash"></i>
                            <span id="restrictModalTitle">Restrict Account</span>
                        </h2>
                        <p class="modal-subtitle">Provide a reason for restricting <strong id="restrictModalName"></strong>'s account</p>
                    </div>
                    <button class="modal-close" onclick="hideRestrictReasonModal()" title="Close">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="restrictForm" onsubmit="executeRestrict(event)">
                        <input type="hidden" id="restrictAlumniId">
                        <input type="hidden" id="restrictAction" value="restrict">
                        
                        <div class="form-group">
                            <label for="restrictionReason">Reason for Restriction <span style="color: var(--danger);">*</span></label>
                            <select id="restrictionReason" class="form-control" required>
                                <option value="">Select a reason...</option>
                                <option value="spam_fraud">Spam or Fraud</option>
                                <option value="nudity_sexual">Nudity or Sexual Content</option>
                                <option value="hate_speech">Hate Speech or Symbols</option>
                                <option value="violence">Violence or Dangerous Organizations</option>
                                <option value="bullying">Bullying or Harassment</option>
                                <option value="illegal_goods">Sale of Illegal or Regulated Goods</option>
                                <option value="ip_violation">Intellectual Property Violation</option>
                                <option value="other">Other (Please Specify)</option>
                            </select>
                        </div>

                        <div class="form-group" id="customReasonGroup" style="display: none;">
                            <label for="customReason">Please specify:</label>
                            <input type="text" id="customReason" class="form-control" placeholder="Enter custom reason...">
                        </div>

                        <div class="form-group">
                            <label for="restrictionComment">Additional Comments <span style="font-weight: 400; color: var(--gray-500);">(Optional)</span></label>
                            <textarea id="restrictionComment" class="form-control" rows="3" 
                                    placeholder="Add any additional notes about this restriction..." 
                                    style="resize: vertical; min-height: 80px;"></textarea>
                        </div>

                        <div class="form-note" style="margin: 1rem 0 0;">
                            <i class="fa-solid fa-circle-info"></i>
                            <div>
                                <strong>Note:</strong> The reason and comments will be emailed to the alumni and shown when they try to log in.
                            </div>
                        </div>

                        <div class="modal-actions">
                            <button type="button" class="btn btn-secondary" onclick="hideRestrictReasonModal()">Cancel</button>
                            <button type="submit" class="btn btn-warning" id="restrictConfirmBtn">
                                <i class="fa-solid fa-lock"></i> <span id="restrictBtnText">Restrict Account</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Archive Confirmation Modal -->
    <div id="archiveConfirmModal" class="modal-overlay" aria-hidden="true" inert>
        <div class="modal-content-wrapper" style="max-width: 450px;">
            <div class="confirm-modal-card">
                <div class="confirm-icon-wrapper" style="background: #fef3c7; color: #d97706;">
                    <i class="fa-solid fa-box-archive"></i>
                </div>
                <h3 class="confirm-title">Archive Account</h3>
                <p class="confirm-message">
                    Are you sure you want to archive <strong id="archiveConfirmName"></strong>'s account?
                    <br><small>The alumni will not be able to log in. You can restore the account later from the Archived Accounts section.</small>
                </p>
                <div class="confirm-actions">
                    <button type="button" class="btn btn-secondary" onclick="hideArchiveConfirm()">Cancel</button>
                    <button type="button" class="btn btn-warning" onclick="executeArchive()">
                        <i class="fa-solid fa-box-archive"></i> Archive
                    </button>
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
    // ========================================
    // CONSOLE DEBUG - Check if script loads
    // ========================================
    console.log('✅ Alumni Directory page JavaScript loaded');

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

    // Bulk import logic
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

    // Alert toast system
    function showAlert(message, type = 'success') {
        const toast = document.getElementById('alertToast');
        const icon = toast.querySelector('.alert-icon');
        const msg = toast.querySelector('.alert-message');
        
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

    // Keyboard shortcuts
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            hideModal();
            hideManageModal();
            hideDeleteConfirm();
            hideAlert();
        }
        if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
            e.preventDefault();
            document.getElementById('searchInput')?.focus();
        }
    });

    // Safely handle clicks on manage buttons
    document.addEventListener('click', function(e) {
        const btn = e.target.closest('.manage-btn');
        if (btn) {
            const id = btn.dataset.id;
            const name = btn.dataset.name;
            const status = btn.dataset.status || 1;
            openManageModal(id, name, status);
        }
    });

    // ========================================
    // MANAGE MODAL - ENHANCED WITH AJAX
    // ========================================
    let currentManageAlumniId = null;
    let currentManageAlumniName = '';
    let currentManageAccountStatus = 1;

    function openManageModal(id, name, status) {
        const accountStatus = status !== undefined ? parseInt(status) : 1;
        
        currentManageAlumniId = id;
        currentManageAlumniName = name;
        currentManageAccountStatus = accountStatus;
        
        document.getElementById('manageAlumniId').value = id;
        document.getElementById('manageAlumniName').textContent = name;
        document.getElementById('manageAccountStatus').value = accountStatus;
        
        updateRestrictButton(accountStatus);
        
        const modal = document.getElementById('manageModal');
        // Remove closing class if it exists
        modal.classList.remove('closing');
        modal.classList.add('active');
        modal.removeAttribute('inert');
        document.body.style.overflow = 'hidden';
    }

    function hideManageModal() {
        const modal = document.getElementById('manageModal');
        if (modal) {
            // Add closing class for exit animation
            modal.classList.add('closing');
            
            // Wait for animation to complete before removing
            setTimeout(() => {
                modal.classList.remove('active');
                modal.classList.remove('closing');
                modal.setAttribute('inert', '');
                document.body.style.overflow = '';
                currentManageAlumniId = null;
            }, 300); // Match the animation duration (0.3s)
        }
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
    // ARCHIVE ACCOUNT - AJAX VERSION
    // ========================================
    function prepareArchive() {
        const id = document.getElementById('manageAlumniId').value;
        const name = document.getElementById('manageAlumniName').textContent;
        
        document.getElementById('archiveConfirmName').textContent = name;
        hideManageModal();
        
        const modal = document.getElementById('archiveConfirmModal');
        modal.classList.add('active');
        modal.removeAttribute('inert');
        document.body.style.overflow = 'hidden';
    }

    function hideArchiveConfirm() {
        const modal = document.getElementById('archiveConfirmModal');
        modal.classList.remove('active');
        modal.setAttribute('inert', '');
        document.body.style.overflow = '';
    }

    async function executeArchive() {
        const id = document.getElementById('manageAlumniId').value;
        
        try {
            const response = await fetch(`/admin/alumni/${id}/archive`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                }
            });
            
            const data = await response.json();
            
            hideArchiveConfirm();
            
            if (data.success) {
                showAlert(data.message, 'success');
                // Remove the card from the DOM
                removeAlumniCard(id);
                // Update stats
                updateDirectoryStats();
                // Update both badges
                updateArchivedBadge();
                updateRestrictedBadge();
            } else {
                showAlert(data.message || 'Failed to archive account.', 'error');
            }
        } catch (error) {
            console.error('Archive error:', error);
            hideArchiveConfirm();
            showAlert('An error occurred while archiving the account.', 'error');
        }
    }

    // ========================================
    // RESET PASSWORD - AJAX VERSION
    // ========================================
    function prepareResetPassword() {
        const id = document.getElementById('manageAlumniId').value;
        const name = document.getElementById('manageAlumniName').textContent;
        
        document.getElementById('resetConfirmName').textContent = name;
        hideManageModal();
        
        const modal = document.getElementById('resetPasswordConfirmModal');
        modal.classList.add('active');
        modal.removeAttribute('inert');
        document.body.style.overflow = 'hidden';
    }

    function hideResetPasswordConfirm() {
        const modal = document.getElementById('resetPasswordConfirmModal');
        modal.classList.remove('active');
        modal.setAttribute('inert', '');
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
    // DELETE CONFIRMATION
    // ========================================
    let pendingDeleteId = null;

    function prepareDelete() {
        const id = document.getElementById('manageAlumniId').value;
        const name = document.getElementById('manageAlumniName').textContent;
        
        pendingDeleteId = id;
        document.getElementById('confirmAlumniName').textContent = name;
        
        hideManageModal();
        
        const modal = document.getElementById('deleteConfirmModal');
        modal.classList.add('active');
        modal.removeAttribute('inert');
        document.body.style.overflow = 'hidden';
    }

    function hideDeleteConfirm() {
        const modal = document.getElementById('deleteConfirmModal');
        modal.classList.remove('active');
        modal.setAttribute('inert', '');
        document.body.style.overflow = '';
        pendingDeleteId = null;
    }

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
                // Remove the card from the DOM
                removeAlumniCard(pendingDeleteId);
                // Update stats
                updateDirectoryStats();
                // Update both badges (just in case)
                updateArchivedBadge();
                updateRestrictedBadge();
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

    // ========================================
    // DOM MANIPULATION HELPERS
    // ========================================

    // Remove an alumni card from the DOM with animation
    function removeAlumniCard(alumniId, useAnimation = true) {
        console.log('🗑️ Removing card with ID:', alumniId);
        const cards = document.querySelectorAll(`.alumni-card[data-id="${alumniId}"]`);
        console.log(`📊 Found ${cards.length} cards to remove`);
        
        cards.forEach(card => {
            if (useAnimation) {
                // Add fade-out animation
                card.style.transition = 'all 0.3s ease';
                card.style.opacity = '0';
                card.style.transform = 'scale(0.9)';
                card.style.height = card.offsetHeight + 'px';
                card.style.overflow = 'hidden';
                
                setTimeout(() => {
                    if (card.parentNode) {
                        card.remove();
                        console.log('✅ Card removed from DOM');
                    }
                }, 300);
            } else {
                if (card.parentNode) {
                    card.remove();
                    console.log('✅ Card removed from DOM (no animation)');
                }
            }
        });
    }
    // Update directory statistics
    function updateDirectoryStats() {
        const totalCards = document.querySelectorAll('.alumni-card').length;
        const statValues = document.querySelectorAll('.stat-value');
        if (statValues.length > 0) {
            statValues[0].textContent = totalCards;
        }
        
        // Update results count
        const resultsCount = document.getElementById('resultsCount');
        if (resultsCount) {
            resultsCount.textContent = `${totalCards} result${totalCards != 1 ? 's' : ''}`;
        }
    }

    // ========================================
    // ENHANCED BULK IMPORT WITH PREVIEW
    // ========================================

    let importedData = [];
    let currentPage = 1;
    let pageSize = 25;
    let columnMapping = {};
    let validationResults = {};
    let fileHeaders = [];

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
            
            const rawData = XLSX.utils.sheet_to_json(sheet, { header: 1, raw: false, defval: '' });
            
            if (!rawData || rawData.length < 2) {
                status.textContent = 'Error: File is empty or contains only headers.';
                status.style.color = 'var(--danger)';
                return;
            }

            let headerRowIndex = -1;
            for (let i = 0; i < Math.min(rawData.length, 10); i++) {
                const row = rawData[i] || [];
                const rowString = row.join(' ').toLowerCase();
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
                status.textContent = 'Error: Could not find required headers.';
                status.style.color = 'var(--danger)';
                return;
            }

            const headers = rawData[headerRowIndex].map(h => String(h || '').trim());
            const dataRows = [];
            
            for (let i = headerRowIndex + 1; i < rawData.length; i++) {
                const row = rawData[i] || [];
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

            importedData = dataRows;
            fileHeaders = headers;
            
            document.getElementById('uploadStep').style.display = 'none';
            document.getElementById('previewStep').style.display = 'block';
            document.getElementById('bulkImportStatus').textContent = `Loaded ${dataRows.length} records for preview.`;
            document.getElementById('bulkImportStatus').style.color = 'var(--success)';
            
            renderPreviewTable(dataRows);
            renderColumnMapping(headers);
            validateAllRows();
            
        } catch (error) {
            console.error('Preview error:', error);
            status.textContent = 'Error reading file: ' + error.message;
            status.style.color = 'var(--danger)';
        }
    }

    function renderPreviewTable(data) {
        const table = document.getElementById('previewTable');
        const thead = table.querySelector('thead');
        const tbody = table.querySelector('tbody');
        
        thead.innerHTML = '';
        tbody.innerHTML = '';
        
        const columnsToShow = fileHeaders;
        
        const headerRow = document.createElement('tr');
        
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
        
        columnsToShow.forEach((col) => {
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
        
        const total = data.length;
        const totalPages = Math.ceil(total / pageSize);
        
        if (currentPage > totalPages) currentPage = totalPages;
        if (currentPage < 1) currentPage = 1;
        
        const start = (currentPage - 1) * pageSize;
        const end = Math.min(start + pageSize, total);
        const pageData = data.slice(start, end);
        
        pageData.forEach((row, index) => {
            const actualIndex = start + index;
            const tr = document.createElement('tr');
            tr.dataset.rowIndex = actualIndex;
            tr.id = `row-${actualIndex}`;
            
            const numTd = document.createElement('td');
            numTd.className = 'row-number';
            numTd.textContent = actualIndex + 1;
            numTd.style.position = 'sticky';
            numTd.style.left = '0';
            numTd.style.zIndex = '5';
            numTd.style.background = 'var(--white)';
            tr.appendChild(numTd);
            
            const statusTd = document.createElement('td');
            statusTd.className = 'row-status';
            statusTd.id = `status-${actualIndex}`;
            statusTd.style.position = 'sticky';
            statusTd.style.left = '50px';
            statusTd.style.zIndex = '5';
            statusTd.style.background = 'var(--white)';
            statusTd.innerHTML = '<i class="fa-regular fa-hourglass-half" style="color: var(--gray-400);"></i>';
            tr.appendChild(statusTd);
            
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
        
        let countText = `Showing ${start + 1}-${end} of ${total} records`;
        if (totalPages > 1) {
            countText += ` (Page ${currentPage} of ${totalPages})`;
        }
        document.getElementById('recordCount').textContent = countText;
        
        renderPaginationControls();
    }

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

    function handleCellEdit(input, rowIndex, column) {
        const value = input.value;
        importedData[rowIndex][column] = value;
        
        input.classList.remove('error', 'success');
        const errorMsg = input.parentElement.querySelector('.error-message');
        if (errorMsg) errorMsg.remove();
    }

    function validateSingleRow(rowIndex) {
        const row = importedData[rowIndex];
        if (!row) return;
        
        const errors = [];
        const mappedFields = getFieldMapping();
        
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
        
        if (mappedFields.email && row[mappedFields.email]) {
            const email = row[mappedFields.email];
            if (!email.match(/^[^\s@]+@[^\s@]+\.[^\s@]+$/)) {
                errors.push('Invalid email format');
            }
        }
        
        const statusTd = document.getElementById(`status-${rowIndex}`);
        const tr = statusTd?.closest('tr');
        const inputs = tr?.querySelectorAll('.editable-cell');
        
        if (errors.length > 0) {
            statusTd.innerHTML = '<i class="fa-solid fa-times-circle"></i>';
            tr?.classList.add('error-row');
            tr?.classList.remove('success-row');
            
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
            
            inputs?.forEach(input => {
                input.classList.remove('error');
            });
        }
        
        validationResults[rowIndex] = { errors, valid: errors.length === 0 };
    }

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
        
        updateValidationSummary(validCount, errorCount, total);
        
        const importBtn = document.getElementById('confirmImportBtn');
        if (errorCount === 0 && validCount > 0) {
            importBtn.disabled = false;
            importBtn.style.opacity = '1';
        } else {
            importBtn.disabled = true;
            importBtn.style.opacity = '0.6';
        }
    }

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

    function getFieldMapping() {
        const mapping = {};
        const allFields = [
            'student_id_number', 'first_name', 'last_name', 'email', 'program',
            'middle_name', 'phone_number', 'year_graduated', 'date_of_birth', 'sex'
        ];
        
        allFields.forEach(field => {
            const select = document.getElementById(`map-${field}`);
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

    function backToUpload() {
        document.getElementById('uploadStep').style.display = 'block';
        document.getElementById('previewStep').style.display = 'none';
        document.getElementById('bulkImportStatus').textContent = 'Returned to upload.';
        document.getElementById('bulkImportStatus').style.color = 'var(--gray-500)';
    }

    function renderPaginationControls() {
        const container = document.getElementById('paginationControls');
        if (!container) return;
        
        const total = importedData.length;
        const totalPages = Math.ceil(total / pageSize);
        
        if (pageSize >= total || totalPages <= 1) {
            container.innerHTML = '';
            return;
        }
        
        let html = '';
        
        html += `<button class="btn btn-sm btn-secondary" onclick="goToPage(${currentPage - 1})" ${currentPage <= 1 ? 'disabled' : ''}>
            <i class="fa-solid fa-chevron-left"></i>
        </button>`;
        
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
        
        html += `<button class="btn btn-sm btn-secondary" onclick="goToPage(${currentPage + 1})" ${currentPage >= totalPages ? 'disabled' : ''}>
            <i class="fa-solid fa-chevron-right"></i>
        </button>`;
        
        const start = (currentPage - 1) * pageSize + 1;
        const end = Math.min(currentPage * pageSize, total);
        html += `<span style="font-size: 0.8125rem; color: var(--gray-500); margin-left: 0.5rem;">
            ${start}-${end} of ${total}
        </span>`;
        
        container.innerHTML = html;
    }

    function changePageSize() {
        const select = document.getElementById('pageSizeSelect');
        const value = parseInt(select.value);
        
        if (value === 0) {
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

    function goToPage(page) {
        const totalPages = Math.ceil(importedData.length / pageSize);
        if (page < 1 || page > totalPages) return;
        currentPage = page;
        renderPreviewTable(importedData);
        renderPaginationControls();
        validateAllRows();
    }

    async function validateAndImport() {
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
        
        if (!confirm(`Ready to import ${total} alumni records. Continue?`)) {
            return;
        }
        
        const status = document.getElementById('bulkImportStatus');
        const importBtn = document.getElementById('confirmImportBtn');
        importBtn.disabled = true;
        importBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Importing...';
        
        const mapping = getFieldMapping();
        
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
                    // Reload the page to show new alumni
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

    function clearAllErrors() {
        document.querySelectorAll('.error-row').forEach(tr => {
            tr.classList.remove('error-row');
        });
        document.querySelectorAll('.editable-cell.error').forEach(input => {
            input.classList.remove('error');
        });
        showAlert('Error indicators cleared.', 'info');
    }

    function getCsrfToken() {
        return document.querySelector('meta[name="csrf-token"]')?.content || '';
    }

    // ========================================
    // EXPORT FUNCTIONALITY
    // ========================================
    function exportAlumni() {
        const exportBtn = document.querySelector('.btn-secondary .fa-download')?.closest('button');
        if (exportBtn) {
            exportBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Exporting...';
            exportBtn.disabled = true;
        }
        
        const link = document.createElement('a');
        link.href = '{{ route('admin.alumni.export') }}';
        link.download = 'alumni_export_' + new Date().toISOString().split('T')[0] + '.csv';
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
        
        setTimeout(() => {
            if (exportBtn) {
                exportBtn.innerHTML = '<i class="fa-solid fa-download"></i> Export';
                exportBtn.disabled = false;
            }
        }, 2000);
        
        showAlert('Exporting alumni data...', 'info');
    }

    // ========================================
    // REDIRECT TO MESSAGES
    // ========================================
    function redirectToMessages(alumniId, alumniName) {
        sessionStorage.setItem('openChat', JSON.stringify({
            id: alumniId,
            name: alumniName,
            timestamp: Date.now()
        }));
        
        window.location.href = '/admin/messages';
    }

    // ========================================
    // CLOSE MODALS ON OVERLAY CLICK
    // ========================================
    document.getElementById('manageModal')?.addEventListener('click', function(e) {
        if (e.target === this) hideManageModal();
    });

    document.getElementById('archiveConfirmModal')?.addEventListener('click', function(e) {
        if (e.target === this) hideArchiveConfirm();
    });

    document.getElementById('resetPasswordConfirmModal')?.addEventListener('click', function(e) {
        if (e.target === this) hideResetPasswordConfirm();
    });

    document.getElementById('restrictReasonModal')?.addEventListener('click', function(e) {
        if (e.target === this) hideRestrictReasonModal();
    });

    // ========================================
    // FILE INPUT AUTO-PREVIEW
    // ========================================
    document.addEventListener('DOMContentLoaded', function() {
        const fileInput = document.getElementById('bulkImportFile');
        const previewBtn = document.getElementById('previewBtn');
        const status = document.getElementById('bulkImportStatus');
        
        if (fileInput) {
            fileInput.addEventListener('change', function(e) {
                if (e.target.files[0]) {
                    const fileName = e.target.files[0].name;
                    if (status) {
                        status.textContent = `Selected: ${fileName}`;
                        status.style.color = 'var(--success)';
                    }
                    if (previewBtn) {
                        previewBtn.disabled = false;
                        setTimeout(() => {
                            previewBtn.click();
                        }, 500);
                    }
                }
            });
        }
    });

    // ========================================
    // HANDLE SUCCESS MESSAGES FROM SERVER
    // ========================================
    document.addEventListener('DOMContentLoaded', function() {
        @if(session('success'))
            showAlert('{{ session('success') }}', 'success');
        @endif
        
        @if(session('error'))
            showAlert('{{ session('error') }}', 'error');
        @endif

        // Add data-id and data-alumni-type attributes to cards
        document.querySelectorAll('.alumni-card').forEach(card => {
            const manageBtn = card.querySelector('.manage-btn');
            if (manageBtn) {
                const id = manageBtn.dataset.id;
                if (id) {
                    card.dataset.id = id;
                }
            }
            
            // Get alumni type from the badge
            const typeBadge = card.querySelector('.alumni-type-badge');
            if (typeBadge) {
                const type = typeBadge.classList.contains('shs') ? 'shs' : 
                            typeBadge.classList.contains('college') ? 'college' : 'unknown';
                card.dataset.alumniType = type;
            }
        });
    });

   // ========================================
    // FIX: OPEN RESTRICT MODAL - SHOW HIDDEN MODAL
    // ========================================

    function openRestrictModal() {
        console.log('🔓 Opening restrict modal');
        
        // Get all required elements safely
        const id = document.getElementById('manageAlumniId')?.value;
        const nameEl = document.getElementById('manageAlumniName');
        const statusEl = document.getElementById('manageAccountStatus');
        
        if (!id || !nameEl) {
            console.error('❌ Required elements not found');
            return;
        }
        
        const name = nameEl.textContent;
        const isRestricted = statusEl ? statusEl.value == 0 : false;
        
        console.log('📊 Account status:', isRestricted ? 'Restricted' : 'Active');
        
        if (isRestricted) {
            prepareUnrestrictFromManage(id, name);
            return;
        }
        
        // Set up for restriction
        const restrictIdEl = document.getElementById('restrictAlumniId');
        const restrictNameEl = document.getElementById('restrictModalName');
        const restrictTitleEl = document.getElementById('restrictModalTitle');
        const restrictActionEl = document.getElementById('restrictAction');
        const restrictBtnTextEl = document.getElementById('restrictBtnText');
        const confirmBtn = document.getElementById('restrictConfirmBtn');
        const reasonEl = document.getElementById('restrictionReason');
        const customReasonEl = document.getElementById('customReason');
        const commentEl = document.getElementById('restrictionComment');
        const customReasonGroup = document.getElementById('customReasonGroup');
        
        // If elements are missing, the modal might have been removed from DOM
        if (!restrictIdEl || !restrictNameEl || !restrictTitleEl || !restrictActionEl || !restrictBtnTextEl || !confirmBtn) {
            console.warn('⚠️ Some modal elements missing, attempting to recover...');
            const modal = document.getElementById('restrictReasonModal');
            if (modal) {
                modal.classList.add('active');
                modal.removeAttribute('inert');
                modal.style.display = 'block';
                document.body.style.overflow = 'hidden';
                
                if (restrictIdEl) restrictIdEl.value = id;
                if (restrictNameEl) restrictNameEl.textContent = name;
                if (restrictTitleEl) restrictTitleEl.textContent = 'Restrict Account';
                if (restrictActionEl) restrictActionEl.value = 'restrict';
                if (restrictBtnTextEl) restrictBtnTextEl.textContent = 'Restrict Account';
                if (confirmBtn) {
                    confirmBtn.className = 'btn btn-warning';
                    confirmBtn.innerHTML = '<i class="fa-solid fa-lock"></i> Restrict Account';
                }
                // Reset values
                if (reasonEl) { reasonEl.value = ''; reasonEl.disabled = false; }
                if (customReasonEl) customReasonEl.value = '';
                if (commentEl) commentEl.value = '';
                if (customReasonGroup) customReasonGroup.style.display = 'none';
            } else {
                console.error('❌ Modal completely missing from DOM');
            }
            return;
        }
        
        // Elements exist, set values for current alumni
        restrictIdEl.value = id;
        restrictNameEl.textContent = name;
        restrictTitleEl.textContent = 'Restrict Account';
        restrictActionEl.value = 'restrict';
        restrictBtnTextEl.textContent = 'Restrict Account';
        
        confirmBtn.className = 'btn btn-warning';
        confirmBtn.innerHTML = '<i class="fa-solid fa-lock"></i> Restrict Account';
        
        // Reset and show reason inputs
        if (reasonEl) {
            reasonEl.disabled = false;
            reasonEl.value = ''; // Reset to empty
        }
        if (customReasonEl) {
            customReasonEl.value = ''; // Reset
        }
        if (commentEl) {
            commentEl.disabled = false;
            commentEl.value = ''; // Reset
        }
        if (customReasonGroup) {
            customReasonGroup.style.display = 'none';
        }
        
        const reasonGroup = document.getElementById('restrictionReason')?.closest('.form-group');
        if (reasonGroup) reasonGroup.style.display = 'block';
        
        const commentGroup = document.getElementById('restrictionComment')?.closest('.form-group');
        if (commentGroup) commentGroup.style.display = 'block';
        
        // Update note
        const note = document.querySelector('#restrictReasonModal .form-note');
        if (note) {
            note.innerHTML = `
                <i class="fa-solid fa-circle-info"></i>
                <div>
                    <strong>Note:</strong> The reason and comments will be emailed to the alumni and shown when they try to log in.
                </div>
            `;
        }
        
        // Close the manage modal
        hideManageModal();
        
        // Open restrict reason modal - make sure it's visible
        const modal = document.getElementById('restrictReasonModal');
        if (modal) {
            modal.classList.add('active');
            modal.removeAttribute('inert');
            modal.style.display = 'block';
            document.body.style.overflow = 'hidden';
        }
    }

    // ========================================
    // FIX: HIDE RESTRICT REASON MODAL - RESET FORM VALUES
    // ========================================

    function hideRestrictReasonModal() {
        const modal = document.getElementById('restrictReasonModal');
        if (modal) {
            // Reset the form values
            const reasonEl = document.getElementById('restrictionReason');
            const customReasonEl = document.getElementById('customReason');
            const commentEl = document.getElementById('restrictionComment');
            
            if (reasonEl) {
                reasonEl.value = '';
                reasonEl.disabled = false;
            }
            if (customReasonEl) {
                customReasonEl.value = '';
            }
            if (commentEl) {
                commentEl.value = '';
            }
            
            // Hide custom reason group
            const customReasonGroup = document.getElementById('customReasonGroup');
            if (customReasonGroup) {
                customReasonGroup.style.display = 'none';
            }
            
            // Only hide the modal, don't remove it from DOM
            modal.classList.remove('active');
            modal.setAttribute('inert', '');
            modal.style.display = 'none';
            document.body.style.overflow = '';
            
            // Ensure focus is removed from the modal
            if (document.activeElement && document.activeElement.closest('#restrictReasonModal')) {
                document.activeElement.blur();
            }
        }
    }

   // ========================================
    // FIX: PREPARE UNRESTRICT FROM MANAGE - SAFE VERSION
    // ========================================

    function prepareUnrestrictFromManage(id, name) {
        console.log('🔓 Preparing unrestrict for:', name);
        
        const restrictIdEl = document.getElementById('restrictAlumniId');
        const restrictNameEl = document.getElementById('restrictModalName');
        const restrictTitleEl = document.getElementById('restrictModalTitle');
        const restrictActionEl = document.getElementById('restrictAction');
        const restrictBtnTextEl = document.getElementById('restrictBtnText');
        const confirmBtn = document.getElementById('restrictConfirmBtn');
        
        if (!restrictIdEl || !restrictNameEl || !restrictTitleEl || !restrictActionEl || !restrictBtnTextEl || !confirmBtn) {
            console.error('❌ Restrict modal elements not found');
            return;
        }
        
        restrictIdEl.value = id;
        restrictNameEl.textContent = name;
        restrictTitleEl.textContent = 'Unrestrict Account';
        restrictActionEl.value = 'unrestrict';
        restrictBtnTextEl.textContent = 'Unrestrict Account';
        
        confirmBtn.className = 'btn btn-success';
        confirmBtn.innerHTML = '<i class="fa-solid fa-user-check"></i> Unrestrict Account';
        
        // Hide reason inputs
        const reasonGroup = document.getElementById('restrictionReason')?.closest('.form-group');
        if (reasonGroup) reasonGroup.style.display = 'none';
        const reasonEl = document.getElementById('restrictionReason');
        if (reasonEl) reasonEl.disabled = true;
        const customReasonGroup = document.getElementById('customReasonGroup');
        if (customReasonGroup) customReasonGroup.style.display = 'none';
        
        const commentGroup = document.getElementById('restrictionComment')?.closest('.form-group');
        if (commentGroup) commentGroup.style.display = 'none';
        const commentEl = document.getElementById('restrictionComment');
        if (commentEl) commentEl.disabled = true;
        
        // Update note
        const note = document.querySelector('#restrictReasonModal .form-note');
        if (note) {
            note.innerHTML = `
                <i class="fa-solid fa-circle-info"></i>
                <div>
                    <strong>Note:</strong> Unrestricting this account will allow the alumni to log in again.
                    They will receive an email notification.
                </div>
            `;
        }
        
        // Close the manage modal
        hideManageModal();
        
        // Open restrict reason modal
        const modal = document.getElementById('restrictReasonModal');
        if (modal) {
            modal.classList.add('active');
            modal.removeAttribute('inert');
            document.body.style.overflow = 'hidden';
        }
    }

// ========================================
// FIX: INDIVIDUAL ACCOUNT CREATION - AJAX
// ========================================

// Override the form submission to use AJAX
document.addEventListener('DOMContentLoaded', function() {
    const createForm = document.getElementById('singleCreateForm');
    if (createForm) {
        createForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const submitBtn = this.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;
            
            // Show loading state
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Creating...';
            
            try {
                const response = await fetch(this.action, {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
                    },
                    body: formData
                });
                
                const data = await response.json();
                
                if (response.ok && data.success) {
                    showAlert(data.message || 'Alumni created successfully!', 'success');
                    hideModal();
                    // Reload to show the new alumni
                    setTimeout(() => window.location.reload(), 1500);
                } else {
                    // Handle validation errors
                    let errorMessage = data.message || 'Failed to create alumni.';
                    if (data.errors) {
                        const errorList = Object.values(data.errors).flat().join('\n');
                        errorMessage = errorList;
                    }
                    showAlert(errorMessage, 'error');
                    
                    // Re-enable button
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalText;
                }
            } catch (error) {
                console.error('Create error:', error);
                showAlert('An error occurred while creating the alumni.', 'error');
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalText;
            }
        });
    }
});

// ========================================
// FIX: ENSURE DATA-ID ATTRIBUTE IS SET
// ========================================

// This runs on page load to ensure all cards have data-id
document.addEventListener('DOMContentLoaded', function() {
    // Ensure all alumni cards have data-id
    document.querySelectorAll('.alumni-card').forEach(card => {
        const manageBtn = card.querySelector('.manage-btn');
        if (manageBtn && !card.dataset.id) {
            const id = manageBtn.dataset.id;
            if (id) {
                card.dataset.id = id;
                console.log('✅ Added data-id to card:', id);
            }
        }
    });
});

// ========================================
// FIX: UPDATE CARD STATUS FOR RESTRICT/UNRESTRICT - COMPLETE VERSION
// ========================================

function updateCardStatus(alumniId, newStatus) {
    console.log('🔄 Updating card status for ID:', alumniId, 'New status:', newStatus);
    
    // Try to find the card by data-id first
    let cards = document.querySelectorAll(`.alumni-card[data-id="${alumniId}"]`);
    
    // If not found, try to find by manage-btn
    if (cards.length === 0) {
        const manageBtn = document.querySelector(`.manage-btn[data-id="${alumniId}"]`);
        if (manageBtn) {
            const card = manageBtn.closest('.alumni-card');
            if (card) {
                cards = [card];
                console.log('✅ Found card via manage-btn for status update');
            }
        }
    }
    
    cards.forEach(card => {
        // Update the status badge
        const statusBadge = card.querySelector('.alumni-status');
        if (statusBadge) {
            if (newStatus === 0) {
                // Restricted
                statusBadge.innerHTML = `
                    <span class="alumni-type-badge restricted" style="background: var(--danger-light); color: var(--danger);">
                        <i class="fa-solid fa-user-slash"></i>
                        Restricted
                    </span>
                `;
                console.log('✅ Updated badge to Restricted');
            } else {
                // Active - show original type
                const type = card.dataset.alumniType || 'college';
                const typeLabels = {
                    'shs': { icon: 'fa-solid fa-school', label: 'SHS' },
                    'college': { icon: 'fa-solid fa-graduation-cap', label: 'College' }
                };
                const info = typeLabels[type] || typeLabels['college'];
                statusBadge.innerHTML = `
                    <span class="alumni-type-badge ${type}">
                        <i class="${info.icon}"></i>
                        ${info.label}
                    </span>
                `;
                console.log('✅ Updated badge to', info.label);
            }
        }
        
        // Update the manage button data-status
        const manageBtn = card.querySelector('.manage-btn');
        if (manageBtn) {
            manageBtn.dataset.status = newStatus;
        }
        
        // 👇 THIS IS CRITICAL - Update the hidden input in manage modal
        const statusInput = document.getElementById('manageAccountStatus');
        if (statusInput) {
            statusInput.value = newStatus;
        }
        
        // 👇 THIS IS CRITICAL - Update current status variable
        currentManageAccountStatus = newStatus;
    });
}

// ========================================
// FIX: IMPROVED REMOVE ALUMNI CARD - COMPLETE VERSION
// ========================================

function removeAlumniCard(alumniId, useAnimation = true) {
    console.log('🗑️ Removing card with ID:', alumniId);
    
    if (!alumniId) {
        console.warn('⚠️ No alumni ID provided');
        return;
    }
    
    // Try multiple selectors
    let cards = document.querySelectorAll(`.alumni-card[data-id="${alumniId}"]`);
    
    // If not found, try to find by manage-btn data-id
    if (cards.length === 0) {
        const manageBtn = document.querySelector(`.manage-btn[data-id="${alumniId}"]`);
        if (manageBtn) {
            const card = manageBtn.closest('.alumni-card');
            if (card) {
                cards = [card];
                console.log('✅ Found card via manage-btn');
            }
        }
    }
    
    // If still not found, try to find by scanning all cards
    if (cards.length === 0) {
        document.querySelectorAll('.alumni-card').forEach(card => {
            const btn = card.querySelector('.manage-btn');
            if (btn && btn.dataset.id == alumniId) {
                cards = [card];
                console.log('✅ Found card by scanning all cards');
            }
        });
    }
    
    console.log(`📊 Found ${cards.length} cards to remove`);
    
    if (cards.length === 0) {
        console.warn('⚠️ No cards found with ID:', alumniId);
        // Update stats anyway
        updateDirectoryStats();
        return;
    }
    
    cards.forEach(card => {
        if (useAnimation) {
            // Add fade-out animation
            card.style.transition = 'all 0.3s ease';
            card.style.opacity = '0';
            card.style.transform = 'scale(0.9)';
            card.style.height = card.offsetHeight + 'px';
            card.style.overflow = 'hidden';
            
            setTimeout(() => {
                if (card.parentNode) {
                    card.remove();
                    console.log('✅ Card removed from DOM');
                }
            }, 300);
        } else {
            if (card.parentNode) {
                card.remove();
                console.log('✅ Card removed from DOM (no animation)');
            }
        }
    });
    
    // Update stats after a short delay
    setTimeout(() => {
        updateDirectoryStats();
    }, 400);
}

// ========================================
// FIX: EXECUTE RESTRICT - CLOSE BOTH MODALS
// ========================================

async function executeRestrict(event) {
    if (event) event.preventDefault();
    
    const id = document.getElementById('restrictAlumniId').value;
    const action = document.getElementById('restrictAction').value;
    const isRestricting = action === 'restrict';
    
    console.log('🔒 Executing restrict/unrestrict:', { id, action, isRestricting });
    
    let payload = {
        restrict: isRestricting ? 1 : 0
    };
    
    if (isRestricting) {
        const reason = document.getElementById('restrictionReason').value;
        const customReason = document.getElementById('customReason').value;
        const comment = document.getElementById('restrictionComment').value;
        
        if (!reason) {
            showAlert('Please select a reason for restriction.', 'error');
            return;
        }
        
        const finalReason = reason === 'other' ? customReason : reason;
        
        if (reason === 'other' && !customReason.trim()) {
            showAlert('Please specify a custom reason.', 'error');
            return;
        }
        
        payload.restriction_reason = finalReason;
        payload.restriction_comment = comment || '';
    }
    
    // Show loading state on button
    const submitBtn = document.getElementById('restrictConfirmBtn');
    const originalText = submitBtn.innerHTML;
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Processing...';
    
    try {
        const response = await fetch(`/admin/alumni/${id}/toggle-restrict`, {
            method: 'PATCH',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify(payload)
        });
        
        const data = await response.json();
        console.log('📥 Restrict response:', data);
        
        // ✅ CLOSE BOTH MODALS
        hideRestrictReasonModal();
        hideManageModal();
        
        if (data.success) {
            showAlert(data.message, 'success');
            
            // Wait a tiny bit before removing the card for smoother UX
            setTimeout(() => {
                removeAlumniCard(id, true);
            }, 300);
            
            // Update stats after animation
            setTimeout(() => {
                updateDirectoryStats();
                // Update both badges
                updateArchivedBadge();
                updateRestrictedBadge();
            }, 700);
        } else {
            showAlert(data.message || 'Failed to update account status.', 'error');
        }
    } catch (error) {
        console.error('❌ Toggle restrict error:', error);
        hideRestrictReasonModal();
        hideManageModal();
        showAlert('An error occurred while updating account status. Check console for details.', 'error');
    } finally {
        // Reset button
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalText;
    }
}

// ========================================
// UPDATE BADGE COUNTS
// ========================================

function updateArchivedBadge() {
    const archivedBadge = document.querySelector('.btn-sm-link.archived .badge-count');
    if (archivedBadge) {
        fetch('/admin/alumni/archived-count', {
            method: 'GET',
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.count !== undefined) {
                archivedBadge.textContent = data.count;
                if (data.count > 0) {
                    archivedBadge.classList.add('has-items');
                } else {
                    archivedBadge.classList.remove('has-items');
                }
            }
        })
        .catch(error => console.error('Error updating archived badge:', error));
    }
}

function updateRestrictedBadge() {
    const restrictedBadge = document.querySelector('.btn-sm-link.restricted .badge-count');
    if (restrictedBadge) {
        fetch('/admin/alumni/restricted-count', {
            method: 'GET',
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.count !== undefined) {
                restrictedBadge.textContent = data.count;
                if (data.count > 0) {
                    restrictedBadge.classList.add('has-items');
                } else {
                    restrictedBadge.classList.remove('has-items');
                }
            }
        })
        .catch(error => console.error('Error updating restricted badge:', error));
    }
}

// ========================================
// UNIFIED FILTER SYSTEM
// ========================================

let currentQuickFilter = 'all';
let currentAdvancedFilters = {
    program: '',
    year: '',
    sort: 'name_asc'
};
let activeFilterCount = 0;
let availablePrograms = new Set();
let availableYears = new Set();

// Populate filter dropdowns from existing alumni cards
function populateFilterOptions() {
    const programSelect = document.getElementById('filterProgram');
    const yearSelect = document.getElementById('filterYear');
    
    programSelect.innerHTML = '<option value="">All Programs</option>';
    yearSelect.innerHTML = '<option value="">All Years</option>';
    
    availablePrograms = new Set();
    availableYears = new Set();
    
    document.querySelectorAll('.alumni-card').forEach(card => {
        // Get program
        const programEl = card.querySelector('.alumni-program');
        if (programEl) {
            const programText = programEl.textContent.replace(/[^\w\s\-\(\)]/g, '').trim();
            if (programText && programText !== 'Program not specified') {
                availablePrograms.add(programText);
            }
        }
        
        // Get year
        const metaItems = card.querySelectorAll('.meta-item');
        metaItems.forEach(item => {
            const text = item.textContent.trim();
            if (text.includes('Graduated:')) {
                const yearMatch = text.match(/(\d{4})/);
                if (yearMatch) availableYears.add(yearMatch[1]);
            }
        });
    });
    
    // Sort and populate
    Array.from(availablePrograms).sort().forEach(program => {
        const option = document.createElement('option');
        option.value = program;
        option.textContent = program;
        programSelect.appendChild(option);
    });
    
    Array.from(availableYears).sort((a, b) => b - a).forEach(year => {
        const option = document.createElement('option');
        option.value = year;
        option.textContent = year;
        yearSelect.appendChild(option);
    });
}

// Update filter counts
function updateFilterCounts() {
    const cards = document.querySelectorAll('.alumni-card');
    let total = 0, college = 0, shs = 0;
    
    cards.forEach(card => {
        const isVisible = card.style.display !== 'none';
        if (isVisible) {
            total++;
            const typeBadge = card.querySelector('.alumni-type-badge');
            if (typeBadge) {
                if (typeBadge.classList.contains('college')) college++;
                else if (typeBadge.classList.contains('shs')) shs++;
            }
        }
    });
    
    document.getElementById('countAll').textContent = total;
    document.getElementById('countCollege').textContent = college;
    document.getElementById('countShs').textContent = shs;
    document.getElementById('resultsCount').textContent = 
        `${total} result${total != 1 ? 's' : ''}`;
}

// Apply quick filter
function applyQuickFilter(type) {
    currentQuickFilter = type;
    
    document.querySelectorAll('.filter-pill').forEach(pill => {
        pill.classList.toggle('active', pill.dataset.filter === type);
    });
    
    // Reset advanced filters
    document.getElementById('filterProgram').value = '';
    document.getElementById('filterYear').value = '';
    currentAdvancedFilters.program = '';
    currentAdvancedFilters.year = '';
    updateActiveFilterBadge();
    
    performFilter();
}

// Toggle advanced filters panel
function toggleAdvancedFilters() {
    const panel = document.getElementById('advancedFilters');
    const btn = document.querySelector('.btn-filter-toggle');
    
    if (panel.style.display === 'none') {
        panel.style.display = 'block';
        btn.classList.add('active');
        populateFilterOptions();
    } else {
        panel.style.display = 'none';
        btn.classList.remove('active');
    }
}

// Apply advanced filters
function applyAdvancedFilters() {
    currentAdvancedFilters.program = document.getElementById('filterProgram').value;
    currentAdvancedFilters.year = document.getElementById('filterYear').value;
    currentAdvancedFilters.sort = document.getElementById('filterSort').value;
    
    // Unset quick filter if advanced filters are active
    if (currentAdvancedFilters.program || currentAdvancedFilters.year) {
        document.querySelectorAll('.filter-pill').forEach(pill => {
            pill.classList.remove('active');
        });
        currentQuickFilter = 'all';
    }
    
    updateActiveFilterBadge();
    performFilter();
}

// Update active filter badge
function updateActiveFilterBadge() {
    const badge = document.getElementById('activeFilterBadge');
    const count = Object.values(currentAdvancedFilters)
        .filter(v => v !== '' && v !== 'name_asc').length;
    activeFilterCount = count;
    
    if (count > 0) {
        badge.textContent = count;
        badge.style.display = 'inline-block';
    } else {
        badge.style.display = 'none';
    }
}

// Perform the actual filtering
function performFilter() {
    const cards = document.querySelectorAll('.alumni-card');
    const searchQuery = document.getElementById('searchInput').value.toLowerCase().trim();
    let visibleCount = 0;
    let visibleCards = [];
    
    // Show/hide clear button
    document.getElementById('clearSearch').style.display = searchQuery ? 'flex' : 'none';
    
    cards.forEach(card => {
        // Get card data
        const name = (card.dataset.name || '').toLowerCase();
        const email = (card.dataset.email || '').toLowerCase();
        const program = (card.dataset.program || '').toLowerCase();
        const type = card.dataset.alumniType || '';
        
        // Search filter
        let matchesSearch = !searchQuery || 
            name.includes(searchQuery) || 
            email.includes(searchQuery) || 
            program.includes(searchQuery);
        
        // Quick filter
        let matchesQuick = true;
        if (currentQuickFilter !== 'all') {
            matchesQuick = type === currentQuickFilter;
        }
        
        // Advanced filters
        let matchesAdvanced = true;
        
        if (currentAdvancedFilters.program) {
            const cardProgram = card.querySelector('.alumni-program')?.textContent.replace(/[^\w\s\-\(\)]/g, '').trim() || '';
            matchesAdvanced = matchesAdvanced && cardProgram === currentAdvancedFilters.program;
        }
        
        if (currentAdvancedFilters.year) {
            let cardYear = '';
            card.querySelectorAll('.meta-item').forEach(item => {
                const text = item.textContent.trim();
                if (text.includes('Graduated:')) {
                    const yearMatch = text.match(/(\d{4})/);
                    if (yearMatch) cardYear = yearMatch[1];
                }
            });
            matchesAdvanced = matchesAdvanced && cardYear === currentAdvancedFilters.year;
        }
        
        const visible = matchesSearch && matchesQuick && matchesAdvanced;
        
        card.style.display = visible ? 'flex' : 'none';
        if (visible) {
            visibleCount++;
            visibleCards.push(card);
        }
    });
    
    // Apply sorting
    applySorting(visibleCards);
    updateFilterCounts();
    
    // Show/hide no results
    const grid = document.getElementById('alumniGrid');
    const existingNoResults = grid.querySelector('.no-results');
    
    if (visibleCount === 0 && cards.length > 0) {
        if (!existingNoResults) {
            const noResults = document.createElement('div');
            noResults.className = 'no-results';
            noResults.innerHTML = `
                <div class="empty-icon"><i class="fa-solid fa-filter-circle-xmark"></i></div>
                <h4>No matching alumni found</h4>
                <p>Try adjusting your search or filters</p>
                <button class="btn btn-secondary btn-sm" onclick="resetFilters()" style="margin-top: 0.5rem;">
                    <i class="fa-solid fa-undo"></i> Reset Filters
                </button>
            `;
            grid.appendChild(noResults);
        }
    } else {
        if (existingNoResults) existingNoResults.remove();
    }
}

// Apply sorting
function applySorting(visibleCards) {
    const sort = currentAdvancedFilters.sort || 'name_asc';
    const grid = document.getElementById('alumniGrid');
    
    if (visibleCards.length <= 1) return;
    
    visibleCards.sort((a, b) => {
        const getName = (card) => {
            const nameEl = card.querySelector('.alumni-name');
            return nameEl ? nameEl.textContent.trim() : '';
        };
        const getYear = (card) => {
            let year = 0;
            card.querySelectorAll('.meta-item').forEach(item => {
                const text = item.textContent.trim();
                if (text.includes('Graduated:')) {
                    const yearMatch = text.match(/(\d{4})/);
                    if (yearMatch) year = parseInt(yearMatch[1]);
                }
            });
            return year;
        };
        
        const nameA = getName(a), nameB = getName(b);
        const yearA = getYear(a), yearB = getYear(b);
        
        switch(sort) {
            case 'name_asc': return nameA.localeCompare(nameB);
            case 'name_desc': return nameB.localeCompare(nameA);
            case 'year_desc': return yearB - yearA;
            case 'year_asc': return yearA - yearB;
            default: return 0;
        }
    });
    
    visibleCards.forEach(card => grid.appendChild(card));
}

// Reset all filters
function resetFilters() {
    currentQuickFilter = 'all';
    document.querySelectorAll('.filter-pill').forEach(pill => {
        pill.classList.toggle('active', pill.dataset.filter === 'all');
    });
    
    document.getElementById('filterProgram').value = '';
    document.getElementById('filterYear').value = '';
    document.getElementById('filterSort').value = 'name_asc';
    document.getElementById('searchInput').value = '';
    
    currentAdvancedFilters = { program: '', year: '', sort: 'name_asc' };
    updateActiveFilterBadge();
    performFilter();
}

// Clear search
function clearSearch() {
    document.getElementById('searchInput').value = '';
    performFilter();
    document.getElementById('searchInput').focus();
}

// Initialize filter system
document.addEventListener('DOMContentLoaded', function() {
    setTimeout(() => {
        populateFilterOptions();
        updateFilterCounts();
        document.querySelector('.filter-pill[data-filter="all"]')?.classList.add('active');
    }, 300);
});

</script>

</body>
</html>

{{-- This is admin_directory.blade.php --}}