<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Messages | LumiNUs Admin</title>

    <!-- Fonts & Icons -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Stylesheets -->
    <link rel="stylesheet" href="/css/admin.css">
    <link rel="stylesheet" href="/css/messages_modern.css">
    <link rel="icon" type="image/png" href="/assets/logos/LumiNUs_Icon.png">
    
    <style>
    /* Fix the main layout */
    .admin-main {
        margin-left: var(--sidebar-width);
        height: calc(100vh - 73px); /* Account for navbar */
        overflow: hidden;
        display: flex;
        flex-direction: column;
    }
    
    .messages-wrapper {
        flex: 1;
        display: flex;
        min-height: 0;
        overflow: hidden;
    }
    
    .contacts-panel {
        height: 100%;
        display: flex;
        flex-direction: column;
    }
    
    .contacts-list {
        flex: 1;
        overflow-y: auto;
        min-height: 0;
    }
    
    .chat-panel {
        flex: 1;
        display: flex;
        flex-direction: column;
        height: 100%;
        min-height: 0;
    }
    
    .chat-messages-area {
        flex: 1;
        overflow-y: auto;
        min-height: 0;
    }
    
    .chat-input-container {
        flex-shrink: 0;
        margin-top: auto;
    }
</style>
</head>
<body>
    
    @include('partials.admin-navbar')

    <!-- Mobile Menu Overlay -->
    <div class="mobile-overlay" id="mobileOverlay" onclick="toggleMobileMenu()"></div>

    <div class="admin-layout">
        <!-- Sidebar Navigation (EXACT MATCH TO OTHER PAGES) -->
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
                <a href="{{ route('perks.index') }}" class="nav-item">
                    <i class="fa-solid fa-gift"></i>
                    <span>Perks & Discounts</span>
                </a>
                <a href="/admin/alumni_tracer" class="nav-item">
                    <i class="fa-solid fa-location-dot"></i>
                    <span>Alumni Tracer</span>
                </a>
                <a href="/admin/messages" class="nav-item active">
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

            <!-- Messages Interface -->
            <div class="messages-wrapper">
                
                <!-- Left Panel: Contacts & Search -->
                <aside class="contacts-panel">
                    <div class="panel-header">
                        <h2>Messages</h2>
                        <button class="btn-icon" title="New Message">
                            <i class="fa-solid fa-pen-to-square"></i>
                        </button>
                    </div>

                    <div class="search-container">
                        <i class="fa-solid fa-magnifying-glass"></i>
                        <input type="text" placeholder="Search alumni by name or batch...">
                    </div>

                    <div class="filter-tabs">
                        <button class="tab-btn active">All Chats</button>
                        <button class="tab-btn">Unread <span class="badge">2</span></button>
                        <button class="tab-btn">Archived</button>
                    </div>

                    <div class="contacts-list">
                        <!-- Active Contact -->
                        <div class="contact-card active">
                            <div class="contact-avatar">JM</div>
                            <div class="contact-details">
                                <div class="contact-top">
                                    <span class="contact-name">Dela Cruz, Juan Miguel</span>
                                    <span class="contact-time">11:40 AM</span>
                                </div>
                                <div class="contact-bottom">
                                    <span class="contact-batch">Batch 2023 | BSIT</span>
                                    <span class="contact-preview">Perfect. Registered! Thanks so...</span>
                                </div>
                            </div>
                        </div>

                        <!-- Unread Contact -->
                        <div class="contact-card unread">
                            <div class="contact-avatar">AR</div>
                            <div class="contact-details">
                                <div class="contact-top">
                                    <span class="contact-name">Reyes, Althea</span>
                                    <span class="contact-time">10:15 AM</span>
                                </div>
                                <div class="contact-bottom">
                                    <span class="contact-batch">Batch 2026 | BSA</span>
                                    <span class="contact-preview">Is the deadline for the tracer...</span>
                                    <span class="unread-count">2</span>
                                </div>
                            </div>
                        </div>

                        <div class="contact-card">
                            <div class="contact-avatar">LG</div>
                            <div class="contact-details">
                                <div class="contact-top">
                                    <span class="contact-name">Gutierrez, Louie Andres</span>
                                    <span class="contact-time">Yesterday</span>
                                </div>
                                <div class="contact-bottom">
                                    <span class="contact-batch">Batch 2023 | BSCS</span>
                                    <span class="contact-preview"><i class="fa-solid fa-check-double read-check"></i> Thank you, Mr!</span>
                                </div>
                            </div>
                        </div>

                        <div class="contact-card">
                            <div class="contact-avatar">TA</div>
                            <div class="contact-details">
                                <div class="contact-top">
                                    <span class="contact-name">Asada, Timothy Jan</span>
                                    <span class="contact-time">Yesterday</span>
                                </div>
                                <div class="contact-bottom">
                                    <span class="contact-batch">Batch 2025 | BSN</span>
                                    <span class="contact-preview"><i class="fa-solid fa-check-double read-check"></i> Thank you, Mr!</span>
                                </div>
                            </div>
                        </div>

                        <div class="contact-card">
                            <div class="contact-avatar">JC</div>
                            <div class="contact-details">
                                <div class="contact-top">
                                    <span class="contact-name">Caponpon, Jade Ahrenz</span>
                                    <span class="contact-time">Mon</span>
                                </div>
                                <div class="contact-bottom">
                                    <span class="contact-batch">Batch 2026 | BSBA-MM</span>
                                    <span class="contact-preview">I will be attending, thank you!</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </aside>

                <!-- Right Panel: Active Chat -->
                <main class="chat-panel">
                    <!-- Chat Header -->
                    <div class="chat-header">
                        <div class="chat-user-info">
                            <div class="contact-avatar large">JM</div>
                            <div class="user-meta">
                                <h3>Dela Cruz, Juan Miguel</h3>
                                <span class="user-status"><span class="status-dot online"></span> Online</span>
                            </div>
                        </div>
                        <div class="chat-actions">
                            <button class="btn-icon" title="Search chat"><i class="fa-solid fa-magnifying-glass"></i></button>
                            <button class="btn-icon" title="Voice call"><i class="fa-solid fa-phone"></i></button>
                            <button class="btn-icon" title="More options"><i class="fa-solid fa-ellipsis-vertical"></i></button>
                        </div>
                    </div>

                    <!-- Chat Messages Area -->
                    <div class="chat-messages-area">
                        <div class="date-divider">
                            <span>Today</span>
                        </div>

                        <!-- Received Message -->
                        <div class="message-group received">
                            <div class="message-bubble">
                                <p>Good morning! I saw the post about the Pickle Bark event on March 14. Is it open to beginners? I've never played pickleball before. 😅</p>
                                <span class="msg-time">11:40 AM</span>
                            </div>
                        </div>

                        <!-- Sent Message -->
                        <div class="message-group sent">
                            <div class="message-bubble">
                                <p>Good morning, Sir! Yes, absolutely! The Pickle Bark Open Play is open to all skill levels. We'll have people on-site to help with the basics if it's your first time.</p>
                                <span class="msg-time">11:42 AM <i class="fa-solid fa-check-double read-check"></i></span>
                            </div>
                        </div>

                        <!-- Received Message -->
                        <div class="message-group received">
                            <div class="message-bubble">
                                <p>Awesome! Do I need to bring my own paddle, or is there a rental at GoldenTop Sports Center?</p>
                                <span class="msg-time">11:43 AM</span>
                            </div>
                        </div>

                        <!-- Sent Message -->
                        <div class="message-group sent">
                            <div class="message-bubble">
                                <p>We recommend bringing your own if you have one, but we will have a limited number of spare paddles available. Just make sure to register via the QR code so we can head-count!</p>
                                <span class="msg-time">11:45 AM <i class="fa-solid fa-check-double read-check"></i></span>
                            </div>
                        </div>

                        <!-- Received Message -->
                        <div class="message-group received">
                            <div class="message-bubble">
                                <p>Perfect. Registered! Thanks so much.</p>
                                <span class="msg-time">11:48 AM</span>
                            </div>
                        </div>
                    </div>

                    <!-- Chat Input Area -->
                    <div class="chat-input-container">
                        <button class="btn-attach" title="Attach file">
                            <i class="fa-solid fa-paperclip"></i>
                        </button>
                        <div class="input-wrapper">
                            <input type="text" placeholder="Type a message here...">
                            <button class="btn-emoji" title="Add emoji">
                                <i class="fa-regular fa-face-smile"></i>
                            </button>
                        </div>
                        <button class="btn-send" title="Send message">
                            <i class="fa-solid fa-paper-plane"></i>
                        </button>
                    </div>
                </main>
            </div>
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
    </script>
</body>
</html>