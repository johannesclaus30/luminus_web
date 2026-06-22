<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Alumni | LumiNUs Admin</title>

    <!-- Fonts & Icons -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    
    <!-- Stylesheets -->
    <link rel="stylesheet" href="/css/admin.css">
    <link rel="stylesheet" href="/css/directory_modern.css">
    <link rel="icon" type="image/png" href="/assets/logos/LumiNUs_Icon.png">

    <style>
        .edit-container {
            max-width: 1000px;
            margin: 0 auto;
            padding: 20px;
        }

        .edit-card {
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.05);
            padding: 30px;
            margin-bottom: 20px;
        }

        .edit-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid #f0f0f0;
        }

        .edit-title-section h2 {
            margin: 0;
            font-size: 1.8rem;
            color: #1e293b;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .edit-title-section h2 i {
            color: #32418c;
        }

        .edit-title-section p {
            margin: 5px 0 0;
            color: #64748b;
            font-size: 0.95rem;
        }

        .btn-back {
            background: #e2e8f0;
            color: #475569;
            padding: 10px 20px;
            border: none;
            border-radius: 8px;
            font-weight: 500;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.2s;
        }

        .btn-back:hover {
            background: #cbd5e1;
            color: #1e293b;
        }

        .form-section {
            margin-bottom: 30px;
        }

        .section-title {
            font-size: 1.1rem;
            font-weight: 600;
            color: #32418c;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #e0e7ff;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .form-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
        }

        .form-group {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .form-group.full-width {
            grid-column: 1 / -1;
        }

        .form-group label {
            font-weight: 500;
            font-size: 0.9rem;
            color: #374151;
        }

        .form-group label .required {
            color: #ef4444;
        }

        .form-group input,
        .form-group select {
            padding: 12px 16px;
            border: 2px solid #e5e7eb;
            border-radius: 8px;
            font-size: 0.95rem;
            font-family: 'Poppins', sans-serif;
            transition: all 0.2s;
            background: #fff;
        }

        .form-group input:focus,
        .form-group select:focus {
            outline: none;
            border-color: #32418c;
            box-shadow: 0 0 0 4px rgba(50, 65, 140, 0.1);
        }

        .form-group input[type="file"] {
            padding: 10px;
            border: 2px dashed #d1d5db;
            background: #f9fafb;
        }

        .form-group input[type="file"]:hover {
            border-color: #32418c;
            background: #eff6ff;
        }

        .photo-section {
            background: #f8fafc;
            padding: 25px;
            border-radius: 10px;
            border: 2px solid #e2e8f0;
        }

        .photo-preview-wrapper {
            display: flex;
            align-items: center;
            gap: 25px;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }

        .photo-preview {
            width: 150px;
            height: 150px;
            border-radius: 10px;
            overflow: hidden;
            border: 4px solid #fff;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }

        .photo-preview img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .photo-info {
            flex: 1;
            min-width: 250px;
        }

        .photo-info h4 {
            margin: 0 0 10px;
            color: #1e293b;
            font-size: 1rem;
        }

        .photo-info p {
            margin: 0 0 15px;
            color: #64748b;
            font-size: 0.9rem;
        }

        .checkbox-wrapper {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 12px 16px;
            background: #fff;
            border-radius: 8px;
            border: 2px solid #fee2e2;
        }

        .checkbox-wrapper input[type="checkbox"] {
            width: 18px;
            height: 18px;
            cursor: pointer;
            accent-color: #ef4444;
        }

        .checkbox-wrapper label {
            margin: 0;
            cursor: pointer;
            color: #991b1b;
            font-weight: 500;
            user-select: none;
        }

        .form-actions {
            display: flex;
            justify-content: flex-end;
            gap: 12px;
            margin-top: 30px;
            padding-top: 25px;
            border-top: 2px solid #f0f0f0;
        }

        .btn {
            padding: 12px 24px;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            font-size: 0.95rem;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.2s;
            text-decoration: none;
        }

        .btn-primary {
            background: #32418c;
            color: white;
        }

        .btn-primary:hover {
            background: #2a3570;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(50, 65, 140, 0.3);
        }

        .btn-secondary {
            background: #e2e8f0;
            color: #475569;
        }

        .btn-secondary:hover {
            background: #cbd5e1;
            color: #1e293b;
        }

        .alert {
            padding: 16px 20px;
            border-radius: 8px;
            margin-bottom: 25px;
            display: flex;
            align-items: flex-start;
            gap: 12px;
        }

        .alert i {
            font-size: 1.2rem;
            margin-top: 2px;
        }

        .alert-success {
            background: #d1fae5;
            color: #065f46;
            border: 1px solid #a7f3d0;
        }

        .alert-error {
            background: #fee2e2;
            color: #991b1b;
            border: 1px solid #fecaca;
        }

        .alert-error ul {
            margin: 8px 0 0;
            padding-left: 20px;
        }

        .alert-error li {
            margin: 4px 0;
        }

        @media (max-width: 768px) {
            .form-grid {
                grid-template-columns: 1fr;
            }

            .edit-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 15px;
            }

            .photo-preview-wrapper {
                flex-direction: column;
            }

            .form-actions {
                flex-direction: column-reverse;
            }

            .btn {
                width: 100%;
                justify-content: center;
            }
        }

        .file-upload-wrapper {
            display: flex;
            flex-direction: column;
            gap: 10px;
            margin-top: 15px;
        }

        .file-upload-button {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            padding: 12px 24px;
            background: linear-gradient(135deg, #32418c 0%, #4c5dbf 100%);
            color: white;
            border-radius: 8px;
            font-weight: 600;
            font-size: 0.95rem;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 6px rgba(50, 65, 140, 0.2);
            user-select: none;
        }

        .file-upload-button:hover {
            background: linear-gradient(135deg, #2a3570 0%, #3d4a9c 100%);
            transform: translateY(-2px);
            box-shadow: 0 6px 12px rgba(50, 65, 140, 0.3);
        }

        .file-upload-button:active {
            transform: translateY(0);
        }

        .file-upload-button i {
            font-size: 1.1rem;
        }

        .file-name {
            font-size: 0.85rem;
            color: #64748b;
            padding: 8px 12px;
            background: #f1f5f9;
            border-radius: 6px;
            display: block;
            word-break: break-all;
        }

        .file-name:hover {
            background: #e2e8f0;
        }

    </style>
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

            <div class="edit-container">
                <!-- Alerts -->
                @if (session('status'))
                    <div class="alert alert-success">
                        <i class="fa-solid fa-circle-check"></i>
                        <div>
                            <strong>Success!</strong> {{ session('status') }}
                        </div>
                    </div>
                @endif

                @if ($errors->any())
                    <div class="alert alert-error">
                        <i class="fa-solid fa-circle-exclamation"></i>
                        <div>
                            <strong>Please review the form.</strong>
                            <ul>
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                @endif

                <div class="edit-card">
                    <!-- Header -->
                    <div class="edit-header">
                        <div class="edit-title-section">
                            <h2>
                                <i class="fa-solid fa-user-pen"></i>
                                Edit Alumni Information
                            </h2>
                            <p>Update the details of {{ $alumnus->first_name }} {{ $alumnus->last_name }}</p>
                        </div>
                        <a href="{{ route('admin.directory') }}" class="btn-back">
                            <i class="fa-solid fa-arrow-left"></i>
                            Back to Directory
                        </a>
                    </div>

                    <form method="POST" action="{{ route('admin.alumni.update', $alumnus->id) }}" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        <!-- Personal Information -->
                        <div class="form-section">
                            <h3 class="section-title">
                                <i class="fa-solid fa-user"></i>
                                Personal Information
                            </h3>
                            <div class="form-grid">
                                <div class="form-group">
                                    <label for="first_name">First Name <span class="required">*</span></label>
                                    <input type="text" id="first_name" name="first_name" value="{{ old('first_name', $alumnus->first_name) }}" required placeholder="Enter first name">
                                </div>

                                <div class="form-group">
                                    <label for="middle_name">Middle Name</label>
                                    <input type="text" id="middle_name" name="middle_name" value="{{ old('middle_name', $alumnus->middle_name) }}" placeholder="Enter middle name">
                                </div>

                                <div class="form-group">
                                    <label for="last_name">Last Name <span class="required">*</span></label>
                                    <input type="text" id="last_name" name="last_name" value="{{ old('last_name', $alumnus->last_name) }}" required placeholder="Enter last name">
                                </div>

                                <div class="form-group">
                                    <label for="student_id_number">Student ID Number <span class="required">*</span></label>
                                    <input type="text" id="student_id_number" name="student_id_number" value="{{ old('student_id_number', $alumnus->student_id_number) }}" required placeholder="e.g., 2020-00001">
                                </div>

                                <div class="form-group">
                                    <label for="email">Email <span class="required">*</span></label>
                                    <input type="email" id="email" name="email" value="{{ old('email', $alumnus->email) }}" required placeholder="email@example.com">
                                </div>

                                <div class="form-group">
                                    <label for="phone_number">Phone Number</label>
                                    <input type="text" id="phone_number" name="phone_number" value="{{ old('phone_number', $alumnus->phone_number) }}" placeholder="09xx xxx xxxx">
                                </div>

                                <div class="form-group">
                                    <label for="date_of_birth">Date of Birth</label>
                                    <input type="date" id="date_of_birth" name="date_of_birth" value="{{ old('date_of_birth', $alumnus->date_of_birth ? \Carbon\Carbon::parse($alumnus->date_of_birth)->format('Y-m-d') : '') }}">
                                </div>

                                <div class="form-group">
                                    <label for="sex">Sex</label>
                                    <select id="sex" name="sex">
                                        <option value="">Select sex</option>
                                        <option value="Male" {{ old('sex', $alumnus->sex) === 'Male' ? 'selected' : '' }}>Male</option>
                                        <option value="Female" {{ old('sex', $alumnus->sex) === 'Female' ? 'selected' : '' }}>Female</option>
                                        <option value="Prefer not to say" {{ old('sex', $alumnus->sex) === 'Prefer not to say' ? 'selected' : '' }}>Prefer not to say</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- Academic Information -->
                        <div class="form-section">
                            <h3 class="section-title">
                                <i class="fa-solid fa-graduation-cap"></i>
                                Academic Information
                            </h3>
                            <div class="form-grid">
                                <div class="form-group">
                                    <label for="program">Program</label>
                                    <input type="text" id="program" name="program" value="{{ old('program', $alumnus->program) }}" placeholder="e.g., BS Computer Science">
                                </div>

                                <div class="form-group">
                                    <label for="year_graduated">Year Graduated <span class="required">*</span></label>
                                    <input type="date" id="year_graduated" name="year_graduated" value="{{ old('year_graduated', $alumnus->year_graduated ? \Carbon\Carbon::parse($alumnus->year_graduated)->format('Y-m-d') : '') }}" required>
                                </div>
                            </div>
                        </div>

                        <!-- Photo Section -->
                        <div class="form-section">
                            <h3 class="section-title">
                                <i class="fa-solid fa-image"></i>
                                Card Photo
                            </h3>
                            
                            <!-- Replace the Photo Section in the form with this updated version -->

                            <!-- Photo Section -->
                            <div class="form-section">
                                <h3 class="section-title">
                                    <i class="fa-solid fa-image"></i>
                                    Card Photo
                                </h3>
                                <div class="photo-section">
                                    @php
                                        $photoPath = trim((string) ($alumnus->card_photo));
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
                                    @endphp

                                    <div class="photo-preview-wrapper">
                                        <div class="photo-preview">
                                            <img src="{{ $photoUrl }}" alt="Current Card Photo" onerror="this.src='/assets/FINAL-NULIPA.jpg'">
                                        </div>
                                        <div class="photo-info">
                                            <h4>Current Photo</h4>
                                            <p>Upload a new photo to replace the current one. Recommended size: 300x300px or larger.</p>
                                            
                                            <!-- Custom File Upload -->
                                            <div class="file-upload-wrapper">
                                                <input type="file" id="card_photo" name="card_photo" accept="image/*" hidden>
                                                <label for="card_photo" class="file-upload-button">
                                                    <i class="fa-solid fa-cloud-arrow-up"></i>
                                                    <span id="fileButtonText">Choose Photo</span>
                                                </label>
                                                <span id="fileName" class="file-name">No file selected</span>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="checkbox-wrapper">
                                        <input type="checkbox" id="remove_photo" name="remove_photo" value="1">
                                        <label for="remove_photo">
                                            <i class="fa-solid fa-trash"></i> Remove current photo
                                        </label>
                                    </div>
                                </div>
                            </div>
                            
                        </div>

                        <!-- Form Actions -->
                        <div class="form-actions">
                            <a href="{{ route('admin.directory') }}" class="btn btn-secondary">
                                <i class="fa-solid fa-xmark"></i> Cancel
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fa-solid fa-save"></i> Save Changes
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </main>
    </div>

    <!-- Add this JavaScript before the closing </body> tag -->
<script>
    // File upload button functionality
    const fileInput = document.getElementById('card_photo');
    const fileButtonText = document.getElementById('fileButtonText');
    const fileName = document.getElementById('fileName');

    fileInput.addEventListener('change', function(e) {
        if (e.target.files && e.target.files[0]) {
            const file = e.target.files[0];
            fileName.textContent = file.name;
            fileName.style.color = '#059669';
            fileButtonText.innerHTML = '<i class="fa-solid fa-check"></i> File Selected';
        }
    });

    // Mobile menu toggle
    function toggleMobileMenu() {
        const sidebar = document.getElementById('adminSidebar');
        const overlay = document.getElementById('mobileOverlay');
        sidebar.classList.toggle('mobile-open');
        overlay.classList.toggle('active');
        document.body.style.overflow = sidebar.classList.contains('mobile-open') ? 'hidden' : '';
    }

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
</script>

    <script>
        // Mobile menu toggle
        function toggleMobileMenu() {
            const sidebar = document.getElementById('adminSidebar');
            const overlay = document.getElementById('mobileOverlay');
            sidebar.classList.toggle('mobile-open');
            overlay.classList.toggle('active');
            document.body.style.overflow = sidebar.classList.contains('mobile-open') ? 'hidden' : '';
        }

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
    </script>
</body>
</html>