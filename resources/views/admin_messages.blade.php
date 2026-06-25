<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
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
    
    <!-- Supabase JS Client -->
    <script src="https://cdn.jsdelivr.net/npm/@supabase/supabase-js@2"></script>
    
    <style>
        .admin-main {
            margin-left: var(--sidebar-width);
            height: calc(100vh - 73px);
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
            padding: 20px;
        }
        
        .chat-input-container {
            flex-shrink: 0;
            margin-top: auto;
        }
        
        .loading-spinner {
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 40px;
            color: #6b7280;
            gap: 8px;
        }
        
        .empty-state {
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            height: 100%;
            color: #9ca3af;
            text-align: center;
            padding: 40px;
        }
        
        .empty-state i {
            font-size: 64px;
            margin-bottom: 16px;
            opacity: 0.5;
        }
        
        .empty-state h3 {
            font-size: 18px;
            margin-bottom: 8px;
            color: #6b7280;
        }
        
        .contact-avatar-img {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            object-fit: cover;
        }
        
        .contact-card {
            cursor: pointer;
        }
        
        /* Modal Styles */
        .modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.5);
            z-index: 1000;
            display: none;
            justify-content: center;
            align-items: center;
        }
        
        .modal-overlay.active {
            display: flex;
        }
        
        .modal {
            background: white;
            border-radius: 12px;
            width: 90%;
            max-width: 500px;
            max-height: 80vh;
            display: flex;
            flex-direction: column;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
        }
        
        .modal-header {
            padding: 20px;
            border-bottom: 1px solid #e5e7eb;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .modal-header h3 {
            margin: 0;
            font-size: 18px;
        }
        
        .modal-body {
            padding: 20px;
            overflow-y: auto;
            flex: 1;
        }
        
        .search-results .alumni-item {
            display: flex;
            align-items: center;
            padding: 12px;
            border-radius: 8px;
            cursor: pointer;
            transition: background 0.2s;
        }
        
        .search-results .alumni-item:hover {
            background: #f3f4f6;
        }
        
        .alumni-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: #3b82f6;
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            margin-right: 12px;
            flex-shrink: 0;
        }
        
        .alumni-info {
            flex: 1;
        }
        
        .alumni-info .name {
            font-weight: 500;
            color: #1f2937;
        }
        
        .alumni-info .details {
            font-size: 12px;
            color: #6b7280;
        }
        
        .online-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: #22c55e;
            margin-left: 8px;
            flex-shrink: 0;
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
                        <button class="btn-icon" title="New Message" onclick="openNewMessageModal()">
                            <i class="fa-solid fa-pen-to-square"></i>
                        </button>
                    </div>

                    <div class="search-container">
                        <i class="fa-solid fa-magnifying-glass"></i>
                        <input type="text" id="searchContacts" placeholder="Search alumni by name..." oninput="filterContacts()">
                    </div>

                    <div class="filter-tabs">
                        <button class="tab-btn active" onclick="filterByTab('all', this)">All Chats</button>
                        <button class="tab-btn" onclick="filterByTab('unread', this)">Unread <span class="badge" id="unreadBadge">0</span></button>
                        <button class="tab-btn" onclick="filterByTab('online', this)">Online</button>
                    </div>

                    <div class="contacts-list" id="contactsList">
                        <div class="loading-spinner">
                            <i class="fa-solid fa-spinner fa-spin"></i> Loading conversations...
                        </div>
                    </div>
                </aside>

                <!-- Right Panel: Active Chat -->
                <main class="chat-panel" id="chatPanel">
                    <div class="empty-state" id="noChatSelected">
                        <i class="fa-solid fa-comments"></i>
                        <h3>Select a conversation</h3>
                        <p>Choose an alumni from the left panel to start messaging</p>
                    </div>
                    
                    <!-- Chat Header -->
                    <div class="chat-header" id="chatHeader" style="display: none;">
                        <div class="chat-user-info">
                            <div class="contact-avatar large" id="chatAvatar">--</div>
                            <div class="user-meta">
                                <h3 id="chatName">--</h3>
                                <span class="user-status" id="chatStatus">
                                    <span class="status-dot"></span> Offline
                                </span>
                            </div>
                        </div>
                        <div class="chat-actions">
                            <button class="btn-icon" title="Search chat"><i class="fa-solid fa-magnifying-glass"></i></button>
                            <button class="btn-icon" title="More options"><i class="fa-solid fa-ellipsis-vertical"></i></button>
                        </div>
                    </div>

                    <!-- Chat Messages Area -->
                    <div class="chat-messages-area" id="chatMessages" style="display: none;"></div>

                    <!-- Chat Input Area -->
                    <div class="chat-input-container" id="chatInput" style="display: none;">
                        <button class="btn-attach" title="Attach file">
                            <i class="fa-solid fa-paperclip"></i>
                        </button>
                        <div class="input-wrapper">
                            <input type="text" id="messageInput" placeholder="Type a message here..." onkeypress="handleKeyPress(event)">
                            <button class="btn-emoji" title="Add emoji">
                                <i class="fa-regular fa-face-smile"></i>
                            </button>
                        </div>
                        <button class="btn-send" title="Send message" onclick="sendMessage()">
                            <i class="fa-solid fa-paper-plane"></i>
                        </button>
                    </div>
                </main>
            </div>
        </main>
    </div>

    <!-- New Message Modal -->
    <div class="modal-overlay" id="newMessageModal">
        <div class="modal">
            <div class="modal-header">
                <h3>New Message</h3>
                <button class="btn-icon" onclick="closeNewMessageModal()">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>
            <div class="modal-body">
                <div class="search-container" style="margin-bottom: 15px;">
                    <i class="fa-solid fa-magnifying-glass"></i>
                    <input type="text" id="alumniSearch" placeholder="Search alumni by name, ID, or program..." oninput="searchAlumni()">
                </div>
                <div class="search-results" id="searchResults">
                    <p style="color: #9ca3af; text-align: center;">Start typing to search for alumni</p>
                </div>
            </div>
        </div>
    </div>

    <script>
        // ============================================
        // GLOBAL STATE
        // ============================================
        const adminId = {{ $admin->id ?? 0 }};
        let currentChat = null;
        let allContacts = [];
        let activeTab = 'all';
        let supabase;
        let searchTimeout;
        
        // ============================================
        // SUPABASE INITIALIZATION
        // ============================================
        function initSupabase() {
            const supabaseUrl = '{{ env("SUPABASE_URL") }}';
            const supabaseKey = '{{ env("SUPABASE_KEY") }}';
            
            if (!supabaseUrl || !supabaseKey) {
                console.warn('Supabase credentials not configured');
                return;
            }
            
            supabase = window.supabase.createClient(supabaseUrl, supabaseKey);
            
            // Subscribe to realtime messages for this admin
            const channel = supabase
                .channel('admin-messages-' + adminId)
                .on('postgres_changes', {
                    event: 'INSERT',
                    schema: 'public',
                    table: 'messages',
                    filter: `receiver_id=eq.${adminId}`,
                }, (payload) => {
                    handleNewMessage(payload.new);
                })
                .subscribe((status) => {
                    if (status === 'SUBSCRIBED') {
                        console.log('✅ Supabase Realtime connected');
                    } else if (status === 'CHANNEL_ERROR') {
                        console.error('❌ Supabase Realtime error');
                    }
                });
        }
        
        function handleNewMessage(message) {
            // Only process if it's an alumni-to-admin message for this admin
            if (message.receiver_type === 'admin' && message.receiver_id == adminId && message.sender_type === 'alumni') {
                // Reload conversations to update the list
                loadConversations();
                
                // If currently chatting with this sender, add message to chat
                if (currentChat && message.sender_id == currentChat) {
                    appendMessage({
                        id: message.id,
                        content: message.content,
                        sender_type: 'alumni',
                        is_read: message.is_read,
                        created_at: message.created_at,
                        time: formatTime(new Date(message.created_at)),
                        attachments: []
                    });
                    scrollToBottom();
                    
                    // Mark as read
                    markMessagesAsRead(currentChat);
                }
            }
        }
        
        // ============================================
        // CONVERSATIONS MANAGEMENT
        // ============================================
        async function loadConversations() {
            try {
                const response = await fetch('/admin/messages/conversations');
                if (!response.ok) throw new Error('Failed to load conversations');
                
                allContacts = await response.json();
                applyFilter();
                updateUnreadBadge();
            } catch (error) {
                console.error('Error loading conversations:', error);
                document.getElementById('contactsList').innerHTML = `
                    <div class="empty-state">
                        <i class="fa-solid fa-exclamation-circle"></i>
                        <h3>Error loading conversations</h3>
                        <p>Please try refreshing the page</p>
                    </div>
                `;
            }
        }
        
        function renderContacts(contacts) {
            const contactsList = document.getElementById('contactsList');
            
            if (!contacts || contacts.length === 0) {
                contactsList.innerHTML = `
                    <div class="empty-state">
                        <i class="fa-solid fa-inbox"></i>
                        <h3>No conversations yet</h3>
                        <p>Click the + button to start chatting with alumni</p>
                    </div>
                `;
                return;
            }
            
            contactsList.innerHTML = contacts.map(contact => `
                <div class="contact-card ${currentChat == contact.id ? 'active' : ''} ${contact.unread_count > 0 ? 'unread' : ''}" 
                     onclick="openChat(${contact.id})">
                    ${contact.avatar 
                        ? `<img src="${contact.avatar}" class="contact-avatar-img" alt="${contact.full_name}">`
                        : `<div class="contact-avatar">${contact.initials}</div>`
                    }
                    <div class="contact-details">
                        <div class="contact-top">
                            <span class="contact-name">${escapeHtml(contact.full_name)}</span>
                            <span class="contact-time">${contact.last_message_time || ''}</span>
                        </div>
                        <div class="contact-bottom">
                            <span class="contact-batch">Batch ${contact.batch} | ${contact.program || 'N/A'}</span>
                            <span class="contact-preview">${contact.last_message ? truncateText(contact.last_message, 25) : 'Start a conversation'}</span>
                            ${contact.unread_count > 0 ? `<span class="unread-count">${contact.unread_count}</span>` : ''}
                        </div>
                    </div>
                </div>
            `).join('');
        }
        
        function filterContacts() {
            applyFilter();
        }
        
        function filterByTab(tab, element) {
            activeTab = tab;
            document.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('active'));
            element.classList.add('active');
            applyFilter();
        }
        
        function applyFilter() {
            const query = document.getElementById('searchContacts')?.value?.toLowerCase() || '';
            
            let filtered = allContacts;
            
            // Apply tab filter
            switch(activeTab) {
                case 'unread':
                    filtered = filtered.filter(c => c.unread_count > 0);
                    break;
                case 'online':
                    filtered = filtered.filter(c => c.is_online);
                    break;
            }
            
            // Apply search filter
            if (query) {
                filtered = filtered.filter(contact => 
                    contact.full_name.toLowerCase().includes(query) ||
                    (contact.program && contact.program.toLowerCase().includes(query)) ||
                    contact.batch.toString().includes(query)
                );
            }
            
            renderContacts(filtered);
        }
        
        function updateUnreadBadge() {
            const totalUnread = allContacts.reduce((sum, c) => sum + (c.unread_count || 0), 0);
            const badge = document.getElementById('unreadBadge');
            if (badge) {
                badge.textContent = totalUnread;
                badge.style.display = totalUnread > 0 ? 'inline' : 'none';
            }
        }
        
        // ============================================
        // CHAT FUNCTIONALITY
        // ============================================
        async function openChat(alumniId) {
            currentChat = alumniId;
            
            // Update contact list active state
            document.querySelectorAll('.contact-card').forEach(card => card.classList.remove('active'));
            const activeCard = document.querySelector(`.contact-card[onclick="openChat(${alumniId})"]`);
            if (activeCard) activeCard.classList.add('active');
            
            // Show chat panel, hide empty state
            document.getElementById('noChatSelected').style.display = 'none';
            document.getElementById('chatHeader').style.display = 'flex';
            document.getElementById('chatMessages').style.display = 'block';
            document.getElementById('chatInput').style.display = 'flex';
            
            // Update header
            const contact = allContacts.find(c => c.id == alumniId);
            if (contact) {
                document.getElementById('chatAvatar').textContent = contact.initials;
                document.getElementById('chatName').textContent = contact.full_name;
                document.getElementById('chatStatus').innerHTML = `
                    <span class="status-dot ${contact.is_online ? 'online' : ''}"></span> 
                    ${contact.is_online ? 'Online' : 'Offline'}
                `;
            }
            
            // Load messages
            await loadMessages(alumniId);
            
            // Focus input
            document.getElementById('messageInput').focus();
        }
        
        async function loadMessages(alumniId) {
            const container = document.getElementById('chatMessages');
            container.innerHTML = '<div class="loading-spinner"><i class="fa-solid fa-spinner fa-spin"></i> Loading messages...</div>';
            
            try {
                const response = await fetch(`/admin/messages/${alumniId}`);
                if (!response.ok) throw new Error('Failed to load messages');
                
                const messages = await response.json();
                renderMessages(messages);
                scrollToBottom();
            } catch (error) {
                console.error('Error loading messages:', error);
                container.innerHTML = `
                    <div class="empty-state">
                        <i class="fa-solid fa-exclamation-circle"></i>
                        <h3>Error loading messages</h3>
                        <p>Please try again</p>
                    </div>
                `;
            }
        }
        
        function renderMessages(messages) {
            const container = document.getElementById('chatMessages');
            
            if (!messages || messages.length === 0) {
                container.innerHTML = `
                    <div class="empty-state">
                        <i class="fa-solid fa-message"></i>
                        <h3>No messages yet</h3>
                        <p>Send the first message to start the conversation</p>
                    </div>
                `;
                return;
            }
            
            let html = '';
            let lastDate = null;
            
            messages.forEach(msg => {
                const msgDate = new Date(msg.created_at).toLocaleDateString();
                if (msgDate !== lastDate) {
                    html += `<div class="date-divider"><span>${formatDateDivider(new Date(msg.created_at))}</span></div>`;
                    lastDate = msgDate;
                }
                
                const isSent = msg.sender_type === 'admin';
                html += `
                    <div class="message-group ${isSent ? 'sent' : 'received'}">
                        <div class="message-bubble">
                            <p>${escapeHtml(msg.content)}</p>
                            <span class="msg-time">
                                ${msg.time}
                                ${isSent ? '<i class="fa-solid fa-check-double read-check"></i>' : ''}
                            </span>
                        </div>
                    </div>
                `;
            });
            
            container.innerHTML = html;
        }
        
        function appendMessage(msg) {
            const container = document.getElementById('chatMessages');
            const isSent = msg.sender_type === 'admin';
            
            const messageHtml = `
                <div class="message-group ${isSent ? 'sent' : 'received'}">
                    <div class="message-bubble">
                        <p>${escapeHtml(msg.content)}</p>
                        <span class="msg-time">
                            ${msg.time}
                            ${isSent ? '<i class="fa-solid fa-check-double read-check"></i>' : ''}
                        </span>
                    </div>
                </div>
            `;
            
            // Remove empty state if present
            const emptyState = container.querySelector('.empty-state');
            if (emptyState) emptyState.remove();
            
            container.insertAdjacentHTML('beforeend', messageHtml);
        }
        
        async function sendMessage() {
            const input = document.getElementById('messageInput');
            const content = input.value.trim();
            
            if (!content || !currentChat) return;
            
            // Clear input immediately for better UX
            input.value = '';
            input.focus();
            
            try {
                const response = await fetch('/admin/messages/send', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        receiver_id: currentChat,
                        content: content
                    })
                });
                
                if (!response.ok) throw new Error('Failed to send message');
                
                const data = await response.json();
                
                if (data.success) {
                    // Append the sent message to the chat
                    appendMessage(data.message);
                    scrollToBottom();
                    
                    // Update the contact's last message in the list
                    const contact = allContacts.find(c => c.id == currentChat);
                    if (contact) {
                        contact.last_message = content;
                        contact.last_message_time = 'Just now';
                    }
                    renderContacts(allContacts.filter(c => {
                        if (activeTab === 'unread') return c.unread_count > 0;
                        if (activeTab === 'online') return c.is_online;
                        return true;
                    }));
                }
            } catch (error) {
                console.error('Error sending message:', error);
                // Put the message back in the input if it failed
                input.value = content;
                alert('Failed to send message. Please try again.');
            }
        }
        
        async function markMessagesAsRead(alumniId) {
            // Messages are marked as read server-side when loading them
            // Update the unread count locally
            const contact = allContacts.find(c => c.id == alumniId);
            if (contact) {
                contact.unread_count = 0;
                updateUnreadBadge();
                applyFilter();
            }
        }
        
        function handleKeyPress(event) {
            if (event.key === 'Enter' && !event.shiftKey) {
                event.preventDefault();
                sendMessage();
            }
        }
        
        function scrollToBottom() {
            const container = document.getElementById('chatMessages');
            setTimeout(() => {
                container.scrollTop = container.scrollHeight;
            }, 100);
        }
        
        // ============================================
        // NEW MESSAGE MODAL
        // ============================================
        function openNewMessageModal() {
            document.getElementById('newMessageModal').classList.add('active');
            document.getElementById('alumniSearch').value = '';
            document.getElementById('searchResults').innerHTML = '<p style="color: #9ca3af; text-align: center;">Start typing to search for alumni</p>';
            setTimeout(() => {
                document.getElementById('alumniSearch').focus();
            }, 100);
        }
        
        function closeNewMessageModal() {
            document.getElementById('newMessageModal').classList.remove('active');
        }
        
        async function searchAlumni() {
            const query = document.getElementById('alumniSearch').value.trim();
            
            if (query.length < 2) {
                document.getElementById('searchResults').innerHTML = '<p style="color: #9ca3af; text-align: center;">Type at least 2 characters to search</p>';
                return;
            }
            
            document.getElementById('searchResults').innerHTML = '<div class="loading-spinner"><i class="fa-solid fa-spinner fa-spin"></i> Searching...</div>';
            
            // Debounce the search
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(async () => {
                try {
                    const response = await fetch(`/admin/messages/search/alumni?q=${encodeURIComponent(query)}`);
                    if (!response.ok) throw new Error('Search failed');
                    
                    const alumni = await response.json();
                    
                    if (alumni.length === 0) {
                        document.getElementById('searchResults').innerHTML = '<p style="color: #9ca3af; text-align: center;">No alumni found</p>';
                        return;
                    }
                    
                    document.getElementById('searchResults').innerHTML = alumni.map(a => `
                        <div class="alumni-item" onclick="startNewChat(${a.id})">
                            ${a.avatar 
                                ? `<img src="${a.avatar}" class="contact-avatar-img" alt="${a.full_name}">`
                                : `<div class="alumni-avatar">${a.initials}</div>`
                            }
                            <div class="alumni-info">
                                <div class="name">${escapeHtml(a.full_name)}</div>
                                <div class="details">Batch ${a.batch} | ${a.program || 'N/A'}</div>
                            </div>
                            ${a.is_online ? '<span class="online-dot" title="Online"></span>' : ''}
                        </div>
                    `).join('');
                } catch (error) {
                    console.error('Error searching alumni:', error);
                    document.getElementById('searchResults').innerHTML = '<p style="color: #ef4444; text-align: center;">Error searching. Please try again.</p>';
                }
            }, 300);
        }
        
        function startNewChat(alumniId) {
            closeNewMessageModal();
            
            // Check if contact already exists
            const existingContact = allContacts.find(c => c.id == alumniId);
            if (!existingContact) {
                // Add a placeholder contact
                allContacts.unshift({
                    id: alumniId,
                    full_name: 'Loading...',
                    initials: '??',
                    program: '',
                    batch: '',
                    is_online: false,
                    last_message: null,
                    last_message_time: null,
                    unread_count: 0,
                    avatar: null
                });
                renderContacts(allContacts);
            }
            
            openChat(alumniId);
            
            // Reload conversations to get proper data
            setTimeout(() => loadConversations(), 500);
        }
        
        // Close modal when clicking overlay
        document.getElementById('newMessageModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeNewMessageModal();
            }
        });
        
        // ============================================
        // UTILITY FUNCTIONS
        // ============================================
        function escapeHtml(text) {
            if (!text) return '';
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }
        
        function truncateText(text, maxLength) {
            if (!text) return '';
            return text.length > maxLength ? text.substring(0, maxLength) + '...' : text;
        }
        
        function formatTime(date) {
            return date.toLocaleTimeString('en-US', { 
                hour: 'numeric', 
                minute: '2-digit', 
                hour12: true 
            });
        }
        
        function formatDateDivider(date) {
            const now = new Date();
            const yesterday = new Date(now);
            yesterday.setDate(yesterday.getDate() - 1);
            
            if (date.toDateString() === now.toDateString()) {
                return 'Today';
            } else if (date.toDateString() === yesterday.toDateString()) {
                return 'Yesterday';
            } else {
                return date.toLocaleDateString('en-US', { 
                    month: 'long', 
                    day: 'numeric', 
                    year: 'numeric' 
                });
            }
        }
        
        // ============================================
        // MOBILE MENU TOGGLE
        // ============================================
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
        
        // ============================================
        // INITIALIZATION
        // ============================================
        document.addEventListener('DOMContentLoaded', function() {
            loadConversations();
            initSupabase();
        });
    </script>
</body>
</html>