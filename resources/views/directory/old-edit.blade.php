<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Alumni | LumiNUs Admin</title>

    <link rel="stylesheet" href="{{ asset('css/directory.css') }}">
    <link rel="stylesheet" href="{{ asset('css/admin.css') }}">
    <link rel="icon" type="image/png" href="/assets/logos/LumiNUs_Icon.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    
    <style>
        /* === Minimal, non-conflicting edits only === */
        
        .edit-form-wrapper {
            padding: 24px;
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 2px 12px rgba(0,0,0,0.08);
            max-width: 1000px;
            margin: 20px auto;
        }

        .edit-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 24px;
            padding-bottom: 16px;
            border-bottom: 1px solid #e5e7eb;
        }

        .edit-header h1 {
            color: #32418c;
            font-size: 24px;
            font-weight: 700;
            margin: 0;
        }

        .back-link {
            color: #6b7280;
            text-decoration: none;
            font-size: 14px;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 8px 16px;
            border-radius: 6px;
            transition: background 0.2s;
        }
        .back-link:hover {
            background: #f3f4f6;
            color: #374151;
        }

        .form-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 16px 20px;
        }

        .form-row {
            display: flex;
            flex-direction: column;
            gap: 6px;
        }

        .form-row label {
            font-weight: 600;
            font-size: 13px;
            color: #374151;
        }

        .form-row input,
        .form-row select {
            padding: 10px 12px;
            border: 1px solid #d1d5db;
            border-radius: 6px;
            font-size: 14px;
            background: #fff;
            transition: border-color 0.2s, box-shadow 0.2s;
        }

        .form-row input:focus,
        .form-row select:focus {
            outline: none;
            border-color: #32418c;
            box-shadow: 0 0 0 3px rgba(50, 65, 140, 0.1);
        }

        .photo-preview {
            margin: 12px 0;
        }
        .photo-preview img {
            max-width: 180px;
            border-radius: 8px;
            border: 1px solid #e5e7eb;
            display: block;
        }

        .form-actions {
            display: flex;
            justify-content: flex-end;
            gap: 12px;
            margin-top: 28px;
            padding-top: 20px;
            border-top: 1px solid #e5e7eb;
        }

        .btn-primary {
            background: #32418c;
            color: white;
            border: none;
            padding: 10px 24px;
            border-radius: 6px;
            font-weight: 600;
            font-size: 14px;
            cursor: pointer;
            transition: background 0.2s;
        }
        .btn-primary:hover {
            background: #2a3570;
        }

        .btn-secondary {
            background: #e5e7eb;
            color: #374151;
            border: none;
            padding: 10px 24px;
            border-radius: 6px;
            font-weight: 600;
            font-size: 14px;
            cursor: pointer;
            text-decoration: none;
            transition: background 0.2s;
        }
        .btn-secondary:hover {
            background: #d1d5db;
        }

        .alert {
            padding: 12px 16px;
            border-radius: 6px;
            margin-bottom: 20px;
            font-size: 14px;
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
            margin: 6px 0 0;
            padding-left: 20px;
        }

        .checkbox-row {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-top: 8px;
            font-size: 14px;
            color: #374151;
        }
        .checkbox-row input[type="checkbox"] {
            width: 16px;
            height: 16px;
            cursor: pointer;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .form-grid {
                grid-template-columns: 1fr;
            }
            .edit-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 12px;
            }
            .form-actions {
                flex-direction: column-reverse;
            }
            .btn-primary, .btn-secondary {
                width: 100%;
            }
        }
    </style>
</head>
<body>
    
    @include('partials.admin-navbar')

    <div class="layout-wrapper">
        
        <div class="admin-menu">
            <div>
                <p class="text-titles">Admin Menu</p>
                <a href="{{ url('/admin/dashboard') }}" class="admin-menu-buttons">Admin Dashboard</a>
                <a href="{{ route('admin.directory') }}" class="admin-menu-buttons">Alumni Directory</a>
                <a href="{{ route('announcements.index') }}" class="admin-menu-buttons">Announcement Editor</a>
                <a href="{{ route('events.index') }}" class="admin-menu-buttons">Event Organizer</a>
                <a href="{{ route('perks.index') }}" class="admin-menu-buttons">Perks and Discounts</a>
                <a href="{{ url('/admin/alumni_tracer') }}" class="admin-menu-buttons">NU Alumni Tracer</a>
                <a href="{{ url('/admin/messages') }}" class="admin-menu-buttons">Messages</a>
                <a href="{{ route('admin.settings') }}" class="admin-menu-buttons">Settings</a>
            </div>
            <a href="{{ route('admin.logout') }}" class="admin-menu-signout">Sign Out</a>
        </div>

        {{-- Main Content Area --}}
        <div class="directory-container admin-scrollable" style="padding: 20px;">
            
            <div class="edit-form-wrapper">
                
                @if (session('status'))
                    <div class="alert alert-success">
                        <strong>{{ session('status') }}</strong>
                    </div>
                @endif

                @if ($errors->any())
                    <div class="alert alert-error">
                        <strong>Please review the form.</strong>
                        <ul>
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div class="edit-header">
                    <h1>Edit Alumni Information</h1>
                    <a href="{{ route('admin.directory') }}" class="back-link">
                        <i class="fa-solid fa-arrow-left"></i>
                        Back to Directory
                    </a>
                </div>

                <form method="POST" action="{{ route('admin.alumni.update', $alumnus->id) }}" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')

                    <div class="form-grid">
                        <div class="form-row">
                            <label for="first_name">First Name *</label>
                            <input type="text" id="first_name" name="first_name" value="{{ old('first_name', $alumnus->first_name) }}" required>
                        </div>

                        <div class="form-row">
                            <label for="middle_name">Middle Name</label>
                            <input type="text" id="middle_name" name="middle_name" value="{{ old('middle_name', $alumnus->middle_name) }}">
                        </div>

                        <div class="form-row">
                            <label for="last_name">Last Name *</label>
                            <input type="text" id="last_name" name="last_name" value="{{ old('last_name', $alumnus->last_name) }}" required>
                        </div>

                        <div class="form-row">
                            <label for="student_id_number">Student ID Number *</label>
                            <input type="text" id="student_id_number" name="student_id_number" value="{{ old('student_id_number', $alumnus->student_id_number) }}" required>
                        </div>

                        <div class="form-row">
                            <label for="email">Email *</label>
                            <input type="email" id="email" name="email" value="{{ old('email', $alumnus->email) }}" required>
                        </div>

                        <div class="form-row">
                            <label for="phone_number">Phone Number</label>
                            <input type="text" id="phone_number" name="phone_number" value="{{ old('phone_number', $alumnus->phone_number) }}">
                        </div>

                        <div class="form-row">
                            <label for="program">Program</label>
                            <input type="text" id="program" name="program" value="{{ old('program', $alumnus->program) }}">
                        </div>

                        <div class="form-row">
                            <label for="year_graduated">Year Graduated *</label>
                            <input type="date" id="year_graduated" name="year_graduated" value="{{ old('year_graduated', $alumnus->year_graduated ? \Carbon\Carbon::parse($alumnus->year_graduated)->format('Y-m-d') : '') }}" required>
                        </div>

                        <div class="form-row">
                            <label for="date_of_birth">Date of Birth</label>
                            <input type="date" id="date_of_birth" name="date_of_birth" value="{{ old('date_of_birth', $alumnus->date_of_birth ? \Carbon\Carbon::parse($alumnus->date_of_birth)->format('Y-m-d') : '') }}">
                        </div>

                        <div class="form-row">
                            <label for="sex">Sex</label>
                            <select id="sex" name="sex">
                                <option value="">Select sex</option>
                                <option value="Male" {{ old('sex', $alumnus->sex) === 'Male' ? 'selected' : '' }}>Male</option>
                                <option value="Female" {{ old('sex', $alumnus->sex) === 'Female' ? 'selected' : '' }}>Female</option>
                                <option value="Prefer not to say" {{ old('sex', $alumnus->sex) === 'Prefer not to say' ? 'selected' : '' }}>Prefer not to say</option>
                            </select>
                        </div>
                    </div>

                    {{-- Photo Section --}}
                    <div style="margin-top: 24px; padding-top: 20px; border-top: 1px solid #e5e7eb;">
                        <label style="font-weight: 600; font-size: 14px; color: #374151; display: block; margin-bottom: 12px;">Card Photo</label>
                        
                        @php
                            $photoPath = trim((string) ($alumnus->card_photo));
                            if ($photoPath === '') {
                                $photoUrl = '/assets/FINAL-NULIPA.jpg';
                            } elseif (preg_match('/^https?:\/\//i', $photoPath)) {
                                $photoUrl = $photoPath;
                            } else {
                                $photoUrl = asset('storage/' . ltrim($photoPath, '/'));
                            }
                        @endphp

                        <div class="photo-preview">
                            <img src="{{ $photoUrl }}" alt="Current Card Photo" onerror="this.src='/assets/FINAL-NULIPA.jpg'">
                        </div>

                        <div class="form-row" style="max-width: 400px;">
                            <label for="card_photo">Upload New Photo</label>
                            <input type="file" id="card_photo" name="card_photo" accept="image/*">
                        </div>

                        <div class="checkbox-row">
                            <input type="checkbox" id="remove_photo" name="remove_photo" value="1">
                            <label for="remove_photo" style="margin: 0; cursor: pointer;">Remove current photo</label>
                        </div>
                    </div>

                    <div class="form-actions">
                        <a href="{{ route('admin.directory') }}" class="btn-secondary">Cancel</a>
                        <button type="submit" class="btn-primary">
                            <i class="fa-solid fa-save"></i> Save Changes
                        </button>
                    </div>
                </form>
            </div>

        </div>
    </div>

</body>
</html>