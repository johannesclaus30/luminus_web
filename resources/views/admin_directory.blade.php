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
            
            <div class="search-container">
                <input id="searchInput" type="text" placeholder="Search alumni by name or email..." class="search-bar" oninput="filterUsers()">
                <button id="createBtn" class="create-button" onclick="showModal()">Create Account</button>
            </div>
            <div class="user-container">
                <div class="user-box">
                    <img src="/assets/CLAUS_JOHANNES_PHOTO.jpg" alt="Johannes Claus" class="user-photo">
                    <div class="primary-info">
                        <h1 class="name">Louie Gutierrez</h1>
                        <p class="program">BS Computer Science</p>
                    </div>
                    <p class="email">gutierrezle@students.nu-lipa.edu.ph</p>
                    <div class="tools-container">
                        <div class="tools-container">
                            {{-- Message Icon --}}
                            <a href="#" class="tools-button" title="Message">
                                <i class="fa-solid fa-comment-dots"></i>
                            </a>
                            {{-- View Profile (The Eye Icon) --}}
                            <a href="#" class="tools-button" title="View Profile">
                                <i class="fa-solid fa-eye"></i>
                            </a>
                            {{-- Edit Info (The Info Icon) --}}
                            <a href="#" class="tools-button" title="Edit Info">
                                <i class="fa-solid fa-circle-info"></i>
                            </a>
                        </div>
                    </div>
                </div>

                <div class="user-box">
                    <img src="/assets/CLAUS_JOHANNES_PHOTO.jpg" alt="Johannes Claus" class="user-photo">
                    <div class="primary-info">
                        <h1 class="name">Jade Ahrens Caponpon</h1>
                        <p class="program">BS Medical Technology</p>
                    </div>
                    <p class="email">caponponjp@students.nu-lipa.edu.ph</p>
                    <div class="tools-container">
                        <div class="tools-container">
                            {{-- Message Icon --}}
                            <a href="#" class="tools-button" title="Message">
                                <i class="fa-solid fa-comment-dots"></i>
                            </a>
                            {{-- View Profile (The Eye Icon) --}}
                            <a href="#" class="tools-button" title="View Profile">
                                <i class="fa-solid fa-eye"></i>
                            </a>
                            {{-- Edit Info (The Info Icon) --}}
                            <a href="#" class="tools-button" title="Edit Info">
                                <i class="fa-solid fa-circle-info"></i>
                            </a>
                        </div>
                    </div>
                </div>

                <div class="user-box">
                    <img src="/assets/CLAUS_JOHANNES_PHOTO.jpg" alt="Johannes Claus" class="user-photo">
                    <div class="primary-info">
                        <h1 class="name">Johannes Emmanuel Claus</h1>
                        <p class="program">BS Information Technology</p>
                    </div>
                    <p class="email">clausja@students.nu-lipa.edu.ph</p>
                    <div class="tools-container">
                        <div class="tools-container">
                            {{-- Message Icon --}}
                            <a href="#" class="tools-button" title="Message">
                                <i class="fa-solid fa-comment-dots"></i>
                            </a>
                            {{-- View Profile (The Eye Icon) --}}
                            <a href="#" class="tools-button" title="View Profile">
                                <i class="fa-solid fa-eye"></i>
                            </a>
                            {{-- Edit Info (The Info Icon) --}}
                            <a href="#" class="tools-button" title="Edit Info">
                                <i class="fa-solid fa-circle-info"></i>
                            </a>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

<div id="createModal" class="modal" aria-hidden="true">
    <div class="modal-content">
        <div class="modal-header">
            <div>
                <h2 style="color: #32418c; font-weight: 700;">Create Alumni Account</h2>
                <p class="muted" style="margin:0">Add a single member or upload a list.</p>
            </div>
            <button class="close-button" onclick="hideModal()">&times;</button>
        </div>

        <div class="modal-body">
            <form id="singleCreateForm" onsubmit="createSingle(event)">
                
                <div>
                    <label for="name">Full Name</label>
                    <input id="name" name="name" required placeholder="Jane Doe">
                </div>

                <div class="modal-row">
                    <div>
                        <label for="program">Program</label>
                        <input id="program" name="program" placeholder="BS Computer Science">
                    </div>
                    <div>
                        <label for="email">Email</label>
                        <input id="email" name="email" type="email" required placeholder="email@example.com">
                    </div>
                </div>

                <div class="modal-actions">
                    <button type="submit" class="btn btn-primary">Create Account</button>
                    <button type="button" class="btn btn-secondary" onclick="hideModal()">Cancel</button>
                </div>
            </form>

            <hr style="margin: 30px 0; border: 0; border-top: 1px solid #eee;">

            <div class="bulk-import" style="background: #f9f9f9; padding: 20px; border-radius: 15px; border: 2px dashed #ccc; text-align: center;">
                <h3 style="margin-top: 0; color: #32418c;">Bulk Import</h3>
                <p class="muted">Upload a CSV file (Name, Email, Program)</p>
                <input id="csvFile" type="file" accept=".csv" onchange="handleCSV(event)" style="margin-top: 10px;">
                <div id="importResult" class="import-result"></div>
            </div>
        </div>
    </div>
</div>

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
    
    // Restore background scrolling
    document.body.style.overflow = 'auto';
}

        function createUserBox(name, email, program) {
            const container = document.querySelector('.user-container');
            const box = document.createElement('div');
            box.className = 'user-box';

            const img = document.createElement('img');
            img.src = '/assets/CLAUS_JOHANNES_PHOTO.jpg';
            img.alt = name;
            img.className = 'user-photo';

            const primary = document.createElement('div');
            primary.className = 'primary-info';
            const h1 = document.createElement('h1');
            h1.className = 'name';
            h1.textContent = name;
            const pprog = document.createElement('p');
            pprog.className = 'program';
            pprog.textContent = program || '';
            primary.appendChild(h1);
            primary.appendChild(pprog);

            const pemail = document.createElement('p');
            pemail.className = 'email';
            pemail.textContent = email || '';

            const tools = document.createElement('div');
            tools.className = 'tools-container';
            tools.innerHTML = `
                <div class="tools-container">
                    <a href="#" class="tools-button" title="Message"><i class="fa-solid fa-comment-dots"></i></a>
                    <a href="#" class="tools-button" title="View Profile"><i class="fa-solid fa-eye"></i></a>
                    <a href="#" class="tools-button" title="Edit Info"><i class="fa-solid fa-circle-info"></i></a>
                </div>`;

            box.appendChild(img);
            box.appendChild(primary);
            box.appendChild(pemail);
            box.appendChild(tools);

            container.prepend(box);
        }

        function createSingle(e) {
            e.preventDefault();
            const name = document.getElementById('name').value.trim();
            const email = document.getElementById('email').value.trim();
            const program = document.getElementById('program').value.trim();
            if (!name || !email) return;
            createUserBox(name, email, program);
            hideModal();
        }

        // Read CSV and create user boxes (very basic parser)
        function handleCSV(e) {
            const file = e.target.files[0];
            if (!file) return;
            const reader = new FileReader();
            reader.onload = () => {
                const text = reader.result;
                const lines = text.split(/\r?\n/).map(l => l.trim()).filter(Boolean);
                let created = 0;
                for (let i = 0; i < lines.length; i++) {
                    const parts = lines[i].split(',').map(p => p.trim());
                    // Skip header if it looks like one
                    if (i === 0 && /name/i.test(parts[0]) && /email/i.test(parts[1])) continue;
                    if (parts.length >= 2) {
                        const [name, email, program] = parts;
                        createUserBox(name || 'Unnamed', email || '', program || '');
                        created++;
                    }
                }
                document.getElementById('importResult').textContent = created + ' accounts imported.';
            };
            reader.readAsText(file);
        }
    </script>

</body>
</html>