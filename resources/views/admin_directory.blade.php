<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Directory</title>

    <link rel="stylesheet" href="/css/directory.css">
    <link rel="stylesheet" href="/css/admin.css">
    <link rel="icon" type="image/png" href="/assets/logos/LumiNUs_Icon.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

</head>
<body>
    
    <nav class="nav-main">
        <img class="nav-logo" src="/assets/logos/LumiNUs_Logo_Landscape_White.png" alt="LumiNUs Logo">
    </nav>

    <div class="layout-wrapper">
        
        <div class="admin-menu">
            <div>
                <p class="text-titles">Admin Menu</p>

                <a href="dashboard" class="admin-menu-buttons">Admin Dashboard</a>
                <a href="directory" class="admin-menu-current">Alumni Directory</a>
                <a href="announcements" class="admin-menu-buttons">Announcement Editor</a>
                <a href="events" class="admin-menu-buttons">Event Organizer</a>
                <a href="perks" class="admin-menu-buttons">Perks and Discounts</a>
                <a href="alumni_tracer" class="admin-menu-buttons">NU Alumni Tracer</a>
                <a href="messages" class="admin-menu-buttons">Messages</a>
                <a href="settings" class="admin-menu-buttons">Settings</a>
                <a href="testing" class="admin-menu-buttons">Users Testing</a>
            </div>

            <a href="login" class="admin-menu-signout">Sign Out</a>
        </div>
        <div class="directory-container admin-scrollable">
            @if (session('status'))
                <div class="settings-alert settings-alert-success" style="margin-top: 15px;">
                    <strong>{{ session('status') }}</strong>
                </div>
            @endif

            @if ($errors->any())
                <div class="settings-alert settings-alert-error" style="margin-top: 15px;">
                    <strong>Please review the form.</strong>
                    <span>{{ $errors->first() }}</span>
                </div>
            @endif
            
            <div class="search-container">
                <input id="searchInput" type="text" placeholder="Search alumni by name or email..." class="search-bar" oninput="filterUsers()">
                <button id="createBtn" class="create-button" onclick="showModal()">Create Account</button>
            </div>
            <div class="user-container">
                @forelse ($alumni as $alumnus)
                    @php
                        $firstName = $alumnus->first_name ?? '';
                        $middleName = $alumnus->middle_name ?? '';
                        $lastName = $alumnus->last_name ?? '';
                        $email = $alumnus->email ?? '';
                        $program = $alumnus->program ?? '';
                        $middleInitial = $middleName !== '' ? strtoupper(mb_substr(trim($middleName), 0, 1)) . '.' : '';
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
                    @endphp
                    <div class="user-box">
                        <img src="{{ $photoUrl }}" alt="{{ $displayName ?: 'Alumni photo' }}" class="user-photo">
                        <div class="primary-info">
                            <h1 class="name">{{ $displayName ?: 'Unnamed Alumni' }}</h1>
                            <p class="program">{{ $program ?: 'Program not specified' }}</p>
                        </div>
                        <p class="email">{{ $email ?: 'Email not provided' }}</p>
                        <div class="tools-container">
                            <div class="tools-container">
                                <a href="#" class="tools-button" title="Message">
                                    <i class="fa-solid fa-comment-dots"></i>
                                </a>
                                <a href="#" class="tools-button" title="View Profile">
                                    <i class="fa-solid fa-eye"></i>
                                </a>
                                <a href="#" class="tools-button" title="Edit Info">
                                    <i class="fa-solid fa-circle-info"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="user-box">
                        <div class="primary-info">
                            <h1 class="name">No alumni records found</h1>
                            <p class="program">Create an alumni account to show it here.</p>
                        </div>
                    </div>
                @endforelse

            </div>
        </div>
    </div>

<div id="createModal" class="modal" aria-hidden="true">
    <div class="modal-content">
        <div class="modal-header">
            <div>
                <h2 style="color: #32418c; font-weight: 700;">Create Alumni Account</h2>
                <p class="muted" style="margin:0">Add an alumni record. The initial password will be password123 for testing.</p>
            </div>
            <button class="close-button" onclick="hideModal()">&times;</button>
        </div>

        <div class="modal-tabbar" role="tablist" aria-label="Alumni creation options">
            <button type="button" class="modal-tab active" data-modal-tab="single" onclick="switchModalMode('single')">Individual Creation</button>
            <button type="button" class="modal-tab" data-modal-tab="bulk" onclick="switchModalMode('bulk')">Bulk Import</button>
        </div>

        <div class="modal-body">
            <section class="modal-section modal-section-active" data-modal-section="single">
                <form id="singleCreateForm" method="POST" action="{{ route('admin.alumni.store') }}" enctype="multipart/form-data">
                    @csrf

                    <div class="modal-row">
                        <div>
                            <label for="first_name">First Name</label>
                            <input id="first_name" name="first_name" value="{{ old('first_name') }}" required placeholder="Jane">
                        </div>
                        <div>
                            <label for="middle_name">Middle Name</label>
                            <input id="middle_name" name="middle_name" value="{{ old('middle_name') }}" placeholder="Dela">
                        </div>
                    </div>

                    <div class="modal-row">
                        <div>
                            <label for="last_name">Last Name</label>
                            <input id="last_name" name="last_name" value="{{ old('last_name') }}" required placeholder="Cruz">
                        </div>
                        <div>
                            <label for="student_id_number">Student ID Number</label>
                            <input id="student_id_number" name="student_id_number" value="{{ old('student_id_number') }}" required placeholder="2020-00001">
                        </div>
                    </div>

                    <div class="modal-row">
                        <div>
                            <label for="date_of_birth">Date of Birth</label>
                            <input id="date_of_birth" name="date_of_birth" value="{{ old('date_of_birth') }}" type="date">
                        </div>
                        <div>
                            <label for="year_graduated">Year Graduated</label>
                            <input id="year_graduated" name="year_graduated" value="{{ old('year_graduated') }}" type="date">
                        </div>
                    </div>

                    <div class="modal-row">
                        <div>
                            <label for="sex">Sex</label>
                            <select id="sex" name="sex">
                                <option value="">Select sex</option>
                                <option value="Male" @selected(old('sex') === 'Male')>Male</option>
                                <option value="Female" @selected(old('sex') === 'Female')>Female</option>
                                <option value="Prefer not to say" @selected(old('sex') === 'Prefer not to say')>Prefer not to say</option>
                            </select>
                        </div>
                        <div>
                            <label for="program">Program</label>
                            <input id="program" name="program" value="{{ old('program') }}" required placeholder="BS Computer Science">
                        </div>
                    </div>

                    <div class="modal-row">
                        <div>
                            <label for="email">Email</label>
                            <input id="email" name="email" value="{{ old('email') }}" type="email" required placeholder="email@example.com">
                        </div>
                        <div>
                            <label for="phone_number">Phone Number</label>
                            <input id="phone_number" name="phone_number" value="{{ old('phone_number') }}" required placeholder="09xx xxx xxxx">
                        </div>
                    </div>

                    <div class="upload-card-panel">
                        <label for="card_photo">Card Photo</label>
                        <div class="upload-card-row">
                            <input id="card_photo" name="card_photo" type="file" accept="image/*">
                        </div>
                        <p class="upload-card-hint">Upload the alumni card photo here. This will be stored in the luminus_assets/card_photo folder.</p>
                    </div>

                    <div class="settings-form-note" style="margin-top: 18px;">
                        <strong>Testing password</strong>
                        <p style="margin: 6px 0 0;">All newly created alumni accounts will use <span style="font-weight: 700;">password123</span> for now.</p>
                    </div>

                    <div class="modal-actions">
                        <button type="submit" class="btn btn-primary">Create Account</button>
                        <button type="button" class="btn btn-secondary" onclick="hideModal()">Cancel</button>
                    </div>
                </form>
            </section>

            <section class="modal-section" data-modal-section="bulk">
                <div class="bulk-import bulk-import-panel">
                    <div class="bulk-import-header">
                        <div>
                            <h3>Bulk Import</h3>
                            <p>Upload a CSV or Excel file to create multiple alumni accounts at once.</p>
                        </div>
                        <span class="settings-badge settings-badge-muted">CSV / XLSX</span>
                    </div>

                    <div class="bulk-import-body">
                        <input id="bulkImportFile" type="file" accept=".csv,.xls,.xlsx" />
                        <button type="button" class="btn btn-primary" onclick="handleBulkImport()">Import File</button>
                    </div>

                    <p id="bulkImportStatus" class="bulk-import-status">No file selected yet.</p>
                </div>
            </section>
        </div>
    </div>
</div>

<script src="https://cdn.sheetjs.com/xlsx-latest/package/dist/xlsx.full.min.js"></script>
    <script>
        // Basic search filter by name or email
        function filterUsers() {
            const q = document.getElementById('searchInput').value.toLowerCase().trim();
            document.querySelectorAll('.user-box').forEach(box => {
                const name = (box.querySelector('.name')?.textContent || '').toLowerCase();
                const email = (box.querySelector('.email')?.textContent || '').toLowerCase();
                const visible = !q || name.includes(q) || email.includes(q);
                box.style.display = visible ? 'flex' : 'none';
            });
        }

        function showModal() {
    // Correct way to trigger the centering
    document.getElementById('createModal').style.display = 'flex';
    
    // Optional: Prevent the background page from scrolling while modal is open
    document.body.style.overflow = 'hidden';
}

function hideModal() {
            document.getElementById('createModal').style.display = 'none';
            document.body.style.overflow = 'auto';
        }

        function switchModalMode(mode) {
            document.querySelectorAll('[data-modal-tab]').forEach((tab) => {
                tab.classList.toggle('active', tab.dataset.modalTab === mode);
            });

            document.querySelectorAll('[data-modal-section]').forEach((section) => {
                section.classList.toggle('modal-section-active', section.dataset.modalSection === mode);
            });

            if (mode === 'bulk') {
                const status = document.getElementById('bulkImportStatus');
                if (status && status.textContent === 'No file selected yet.') {
                    status.textContent = 'No file selected yet.';
                }
            }
        }

        function parseDelimitedText(text) {
            const lines = text.split(/\r?\n/).map((line) => line.trim()).filter(Boolean);
            if (!lines.length) {
                return [];
            }

            const headers = lines[0].split(',').map((header) => header.trim().toLowerCase());
            const records = [];

            for (let i = 1; i < lines.length; i++) {
                const values = lines[i].split(',').map((value) => value.trim());
                const row = {};

                headers.forEach((header, index) => {
                    row[header] = values[index] ?? '';
                });

                records.push(row);
            }

            return records;
        }

        function normalizeBulkRow(row) {
            return {
                first_name: row.first_name || row.firstname || row['first name'] || '',
                middle_name: row.middle_name || row.middlename || row['middle name'] || '',
                last_name: row.last_name || row.lastname || row['last name'] || '',
                date_of_birth: row.date_of_birth || row['date of birth'] || '',
                sex: row.sex || '',
                year_graduated: row.year_graduated || row['year graduated'] || '',
                student_id_number: row.student_id_number || row['student id number'] || '',
                email: row.email || '',
                phone_number: row.phone_number || row['phone number'] || '',
                program: row.program || '',
            };
        }

        async function createAlumniRecord(record) {
            const formData = new FormData();
            formData.append('_token', '{{ csrf_token() }}');
            formData.append('first_name', record.first_name);
            formData.append('middle_name', record.middle_name);
            formData.append('last_name', record.last_name);
            formData.append('date_of_birth', record.date_of_birth);
            formData.append('sex', record.sex);
            formData.append('year_graduated', record.year_graduated);
            formData.append('student_id_number', record.student_id_number);
            formData.append('email', record.email);
            formData.append('phone_number', record.phone_number);
            formData.append('program', record.program);

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
            const file = fileInput.files[0];

            if (!file) {
                status.textContent = 'Please choose a CSV or Excel file first.';
                return;
            }

            status.textContent = 'Reading file...';

            let rows = [];

            if (/\.csv$/i.test(file.name)) {
                const text = await file.text();
                rows = parseDelimitedText(text);
            } else {
                if (!window.XLSX) {
                    status.textContent = 'Excel parsing is unavailable right now.';
                    return;
                }

                const buffer = await file.arrayBuffer();
                const workbook = XLSX.read(buffer, { type: 'array' });
                const sheet = workbook.Sheets[workbook.SheetNames[0]];
                rows = XLSX.utils.sheet_to_json(sheet, { defval: '' });
            }

            const records = rows
                .map(normalizeBulkRow)
                .filter((record) => record.first_name && record.last_name && record.student_id_number && record.email && record.phone_number && record.program);

            if (!records.length) {
                status.textContent = 'No valid alumni rows were found in the selected file.';
                return;
            }

            let created = 0;
            let failed = 0;

            status.textContent = `Importing ${records.length} alumni record(s)...`;

            for (const record of records) {
                try {
                    await createAlumniRecord(record);
                    created += 1;
                } catch (error) {
                    failed += 1;
                }
            }

            status.textContent = `Import complete: ${created} created${failed ? `, ${failed} failed` : ''}.`;

            if (created > 0) {
                window.location.reload();
            }
        }
    </script>

</body>
</html>