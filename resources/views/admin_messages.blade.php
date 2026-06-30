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


        /* ========================================
   ENHANCED MOBILE RESPONSIVE STYLES
   ======================================== */

/* Mobile Back Button for Chat Panel */
.mobile-back-btn {
    display: none;
    width: 36px;
    height: 36px;
    border: none;
    background: var(--gray-100);
    border-radius: var(--radius-lg);
    color: var(--nu-blue);
    cursor: pointer;
    font-size: 1rem;
    align-items: center;
    justify-content: center;
    margin-right: 0.75rem;
    flex-shrink: 0;
    transition: all var(--transition);
}
.mobile-back-btn:hover {
    background: var(--nu-blue);
    color: var(--white);
}

/* Mobile Header Adjustments */
@media (max-width: 1024px) {
    .mobile-back-btn {
        display: flex;
    }
    
    .admin-main {
        padding-top: 4rem; /* Space for mobile menu toggle */
    }
    
    .messages-wrapper {
        flex-direction: column;
        position: relative;
    }
    
    /* Contacts Panel - Full Screen on Mobile When No Chat Selected */
    .contacts-panel {
        width: 100% !important;
        min-width: 100% !important;
        height: 100%;
        border-right: none;
        transition: transform var(--transition-slow);
        position: absolute;
        top: 0;
        left: 0;
        z-index: 1;
    }
    
    /* Hide contacts when chat is active */
    .contacts-panel.chat-active {
        transform: translateX(-100%);
    }
    
    /* Chat Panel - Full Screen on Mobile */
    .chat-panel {
        height: 100%;
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        z-index: 2;
        transform: translateX(100%);
        transition: transform var(--transition-slow);
    }
    
    .chat-panel.chat-active {
        transform: translateX(0);
    }
    
    /* Show empty state centered on mobile */
    .chat-panel .empty-state {
        padding: 2rem;
    }
    
    /* Adjust message bubbles for mobile */
    .message-bubble {
        max-width: 80%;
    }
    
    /* Chat header adjustments */
    .chat-header {
        padding: 0.75rem 1rem;
        gap: 0.5rem;
    }
    
    .chat-user-info {
        gap: 0.5rem;
    }
    
    .chat-user-info .contact-avatar {
        width: 36px;
        height: 36px;
        font-size: 0.85rem;
    }
    
    .user-meta h3 {
        font-size: 0.95rem;
    }
    
    .user-status {
        font-size: 0.7rem;
    }
    
    /* Chat input adjustments */
    .chat-input-container {
        padding: 0.75rem 1rem;
        gap: 0.5rem;
    }
    
    .input-wrapper {
        padding: 0.25rem 0.375rem 0.25rem 1rem;
    }
    
    .input-wrapper input {
        font-size: 0.875rem;
        padding: 0.625rem 0;
    }
    
    .btn-send {
        width: 42px;
        height: 42px;
        font-size: 1rem;
    }
    
    .btn-attach, .btn-emoji {
        font-size: 1.1rem;
        padding: 0.375rem;
    }
    
    /* Hide some chat actions on mobile */
    .chat-actions .btn-icon:first-child {
        display: none;
    }
}

/* Small Mobile Devices */
@media (max-width: 640px) {
    .admin-main {
        padding-top: 3.5rem;
    }
    
    .panel-header {
        padding: 0.875rem 1rem;
    }
    
    .panel-header h2 {
        font-size: 1.1rem;
    }
    
    .search-container {
        padding: 0.5rem 0.75rem;
    }
    
    .search-container input {
        padding: 0.625rem 0.875rem 0.625rem 2.5rem;
        font-size: 0.8125rem;
    }
    
    .filter-tabs {
        padding: 0.5rem 0.75rem;
        gap: 0.375rem;
    }
    
    .tab-btn {
        font-size: 0.7rem;
        padding: 0.5rem 0.375rem;
    }
    
    .contact-card {
        padding: 0.75rem 0.5rem;
        gap: 0.625rem;
    }
    
    .contact-avatar {
        width: 40px;
        height: 40px;
        font-size: 0.95rem;
    }
    
    .contact-name {
        font-size: 0.825rem;
    }
    
    .contact-preview, .contact-batch {
        font-size: 0.7rem;
    }
    
    .chat-messages-area {
        padding: 1rem 0.75rem;
        gap: 0.5rem;
    }
    
    .message-bubble {
        max-width: 85%;
        padding: 0.75rem 0.875rem;
    }
    
    .message-bubble p {
        font-size: 0.85rem;
    }
    
    .msg-time {
        font-size: 0.6rem;
    }
    
    .date-divider span {
        font-size: 0.7rem;
        padding: 0.25rem 0.75rem;
    }
    
    /* Make send button slightly smaller */
    .btn-send {
        width: 38px;
        height: 38px;
    }
    
    /* Modal adjustments */
    .modal {
        width: 95%;
        max-height: 85vh;
        border-radius: var(--radius-xl);
    }
    
    .modal-header {
        padding: 1rem;
    }
    
    .modal-header h3 {
        font-size: 1rem;
    }
    
    .modal-body {
        padding: 1rem;
    }
}

/* Very Small Devices */
@media (max-width: 380px) {
    .panel-header {
        padding: 0.75rem;
    }
    
    .filter-tabs {
        padding: 0.375rem 0.5rem;
        gap: 0.25rem;
    }
    
    .tab-btn {
        font-size: 0.65rem;
        padding: 0.4rem 0.25rem;
    }
    
    .contact-card {
        padding: 0.625rem 0.375rem;
    }
    
    .contact-avatar {
        width: 36px;
        height: 36px;
        font-size: 0.85rem;
    }
    
    .message-bubble {
        max-width: 90%;
    }
}

/* Touch-friendly improvements */
@media (hover: none) and (pointer: coarse) {
    /* Increase tap targets for touch devices */
    .contact-card {
        min-height: 56px;
    }
    
    .btn-icon, .btn-send, .btn-attach, .btn-emoji {
        min-width: 44px;
        min-height: 44px;
    }
    
    .tab-btn {
        min-height: 36px;
    }
    
    /* Add active state for touch feedback */
    .contact-card:active {
        background: var(--gray-100);
    }
    
    .btn-send:active {
        transform: scale(0.9);
    }
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
                            <button class="mobile-back-btn" onclick="showContactsOnMobile()" title="Back to conversations">
                                <i class="fa-solid fa-arrow-left"></i>
                            </button>
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
    let supabaseClient;
    let searchTimeout;
    let supabaseRealtimeChannel;
    let pollingInterval;
    let lastMessageId = 0;
    let isDecrypting = false;
    let conversationsLoaded = false; // Track if conversations have loaded
    
    // ============================================
    // SUPABASE INITIALIZATION
    // ============================================
    function initSupabase() {
        const supabaseUrl = '{{ env("SUPABASE_URL") }}';
        const supabaseKey = '{{ env("SUPABASE_KEY") }}';
        
        if (!supabaseUrl || !supabaseKey) {
            console.warn('Supabase credentials not configured - falling back to polling');
            startPolling();
            return;
        }
        
        supabaseClient = window.supabase.createClient(supabaseUrl, supabaseKey);
        
        supabaseRealtimeChannel = supabaseClient
            .channel('admin-messages-' + adminId)
            .on('postgres_changes', {
                event: 'INSERT',
                schema: 'public',
                table: 'messages',
                filter: `receiver_id=eq.${adminId}`,
            }, (payload) => {
                console.log('📨 New incoming message:', payload.new);
                handleIncomingMessage(payload.new);
            })
            .on('postgres_changes', {
                event: 'INSERT',
                schema: 'public',
                table: 'messages',
                filter: `sender_id=eq.${adminId}`,
            }, (payload) => {
                console.log('📤 Message sent (from another session):', payload.new);
                handleOutgoingMessageFromOtherSession(payload.new);
            })
            .subscribe((status) => {
                if (status === 'SUBSCRIBED') {
                    console.log('✅ Supabase Realtime connected');
                } else if (status === 'CHANNEL_ERROR') {
                    console.error('❌ Supabase Realtime error - falling back to polling');
                    startPolling();
                }
            });
    }
    
    // ============================================
    // MESSAGE HANDLERS
    // ============================================
    async function handleIncomingMessage(message) {
        // 🔧 FIXED: Check both ID AND type
        if (message.receiver_id != adminId || message.receiver_type !== 'admin') return;
        
        await loadConversations();
        
        // 🔧 FIXED: Match by ID AND type
        if (currentChat && message.sender_id == currentChat.id && message.sender_type === currentChat.type) {
            const existingMsg = document.querySelector(`[data-msg-id="${message.id}"]`);
            if (existingMsg) {
                console.log('⚠️ Duplicate message prevented:', message.id);
                return;
            }
            
            const decryptedContent = await decryptContent(
                message.content, 
                message.sender_type, 
                message.receiver_type
            );
            
            const decryptedMessage = {
                id: message.id,
                content: decryptedContent,
                sender_id: message.sender_id,
                sender_type: message.sender_type,
                receiver_id: message.receiver_id,
                receiver_type: message.receiver_type,
                is_read: message.is_read,
                created_at: message.created_at,
                time: formatTime(new Date(message.created_at)),
                attachments: []
            };
            
            appendMessage(decryptedMessage);
            scrollToBottom();
            await markMessagesAsRead(currentChat.id, currentChat.type);
            lastMessageId = Math.max(lastMessageId, message.id);
        }
    }
    
    function handleOutgoingMessageFromOtherSession(message) {
        // 🔧 FIXED: Check both ID AND type
        if (message.sender_id != adminId || message.sender_type !== 'admin') return;
        
        loadConversations();
        
        // 🔧 FIXED: Match by ID AND type
        if (currentChat && message.receiver_id == currentChat.id && message.receiver_type === currentChat.type) {
            const existingMsg = document.querySelector(`[data-msg-id="${message.id}"]`);
            if (existingMsg) {
                console.log('⚠️ Duplicate outgoing message prevented:', message.id);
                return;
            }
            
            appendMessage({
                id: message.id,
                content: message.content,
                sender_id: message.sender_id,
                sender_type: message.sender_type,
                receiver_id: message.receiver_id,
                receiver_type: message.receiver_type,
                is_read: message.is_read,
                created_at: message.created_at,
                time: formatTime(new Date(message.created_at)),
                attachments: []
            });
            scrollToBottom();
            lastMessageId = Math.max(lastMessageId, message.id);
        }
    }
    
    async function decryptContent(content, senderType, receiverType) {
        if (!content || (typeof content === 'string' && !content.startsWith('enc:') && !content.startsWith('U2FsdGVkX1'))) {
            return content || '';
        }
        
        if (isDecrypting) {
            console.log('⏳ Decryption already in progress...');
            return '[Decrypting...]';
        }
        
        isDecrypting = true;
        
        try {
            const response = await fetch('/admin/messages/decrypt', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    content: content,
                    sender_type: senderType,
                    receiver_type: receiverType
                })
            });
            
            if (!response.ok) {
                console.error('Decryption failed with status:', response.status);
                return '[Encrypted message]';
            }
            
            const data = await response.json();
            return data.decrypted || content;
        } catch (error) {
            console.error('Decryption error:', error);
            return '[Error decrypting message]';
        } finally {
            isDecrypting = false;
        }
    }
    
    // ============================================
    // POLLING FALLBACK
    // ============================================
    function startPolling() {
        console.log('🔄 Starting message polling (every 2 seconds)...');
        if (pollingInterval) clearInterval(pollingInterval);
        pollingInterval = setInterval(checkForNewMessages, 2000);
    }
    
    async function checkForNewMessages() {
        if (!currentChat) return;
        
        try {
            const response = await fetch(`/admin/messages/${currentChat.type}/${currentChat.id}`);
            if (!response.ok) return;
            
            const messages = await response.json();
            if (messages && messages.length > 0) {
                let hasNewMessages = false;
                
                messages.forEach(msg => {
                    if (msg.id > lastMessageId && msg.sender_id != adminId) {
                        const existingMsg = document.querySelector(`[data-msg-id="${msg.id}"]`);
                        if (!existingMsg) {
                            appendMessage(msg);
                            hasNewMessages = true;
                        }
                        lastMessageId = Math.max(lastMessageId, msg.id);
                    }
                });
                
                if (hasNewMessages) {
                    scrollToBottom();
                    loadConversations();
                }
            }
        } catch (error) {
            console.error('Polling error:', error);
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
            conversationsLoaded = true;
            applyFilter();
            updateUnreadBadge();
            
            // Dispatch event when conversations are loaded
            document.dispatchEvent(new CustomEvent('conversationsLoaded'));
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
            const query = document.getElementById('searchContacts')?.value?.toLowerCase() || '';
            contactsList.innerHTML = `
                <div class="empty-state">
                    <i class="fa-solid fa-user-group"></i>
                    <h3>${query ? 'No matches found' : 'No conversations yet'}</h3>
                    <p>${query ? 'Try a different search term' : 'Start a new message to connect with alumni'}</p>
                </div>
            `;
            return;
        }
        
        contactsList.innerHTML = contacts.map(contact => `
            <div class="contact-card ${currentChat?.id == contact.id && currentChat?.type === contact.type ? 'active' : ''} ${contact.unread_count > 0 ? 'unread' : ''}" 
                onclick="openChat(${contact.id}, '${contact.type || 'alumni'}')">
                ${contact.avatar 
                    ? `<img src="${contact.avatar}" class="contact-avatar-img" alt="${escapeHtml(contact.full_name)}">`
                    : `<div class="contact-avatar">${contact.initials || '??'}</div>`
                }
                <div class="contact-details">
                    <div class="contact-top">
                        <span class="contact-name">
                            ${escapeHtml(contact.full_name)}
                            ${contact.type === 'admin' ? '<span style="font-size: 0.6rem; background: var(--nu-gold); color: var(--nu-blue-dark); padding: 1px 6px; border-radius: 8px; margin-left: 6px; font-weight: 600; vertical-align: middle;">ADMIN</span>' : ''}
                        </span>
                        <span class="contact-time">${contact.last_message_time || ''}</span>
                    </div>
                    <div class="contact-bottom">
                        <span class="contact-batch">${contact.type === 'admin' ? 'Admin Staff' : `Batch ${contact.batch || 'N/A'} | ${contact.program || 'N/A'}`}</span>
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
        
        // If query is empty, just show filtered conversations as before
        if (!query || query.length < 2) {
            let filtered = allContacts;
            
            switch(activeTab) {
                case 'unread':
                    filtered = filtered.filter(c => c.unread_count > 0);
                    break;
                case 'online':
                    filtered = filtered.filter(c => c.is_online);
                    break;
            }
            
            renderContacts(filtered);
            return;
        }
        
        // Show loading state
        document.getElementById('contactsList').innerHTML = `
            <div class="loading-spinner">
                <i class="fa-solid fa-spinner fa-spin"></i> Searching all alumni...
            </div>
        `;
        
        // Search ALL alumni via API
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(async () => {
            try {
                const response = await fetch(`/admin/messages/search/alumni?q=${encodeURIComponent(query)}`);
                const data = await response.json();
                
                if (!response.ok) {
                    document.getElementById('contactsList').innerHTML = `
                        <div class="empty-state">
                            <i class="fa-solid fa-exclamation-circle"></i>
                            <h3>Search failed</h3>
                            <p>Please try again</p>
                        </div>
                    `;
                    return;
                }
                
                if (data.length === 0) {
                    document.getElementById('contactsList').innerHTML = `
                        <div class="empty-state">
                            <i class="fa-solid fa-user-group"></i>
                            <h3>No results found</h3>
                            <p>No alumni or admins match "${query}"</p>
                        </div>
                    `;
                    return;
                }
                
                // Convert search results to contact format
                const searchResults = data.map(result => ({
                    id: result.id,
                    type: result.type,
                    full_name: result.full_name,
                    initials: result.initials,
                    program: result.program,
                    batch: result.batch,
                    is_online: result.is_online,
                    last_message: null,
                    last_message_time: null,
                    unread_count: 0,
                    avatar: result.avatar
                }));
                
                // Apply tab filters to search results
                let filtered = searchResults;
                switch(activeTab) {
                    case 'unread':
                        // Can't filter search results by unread
                        filtered = [];
                        break;
                    case 'online':
                        filtered = filtered.filter(c => c.is_online);
                        break;
                }
                
                renderContacts(filtered);
                
            } catch (error) {
                console.error('Search error:', error);
                document.getElementById('contactsList').innerHTML = `
                    <div class="empty-state">
                        <i class="fa-solid fa-exclamation-circle"></i>
                        <h3>Search error</h3>
                        <p>Please try again</p>
                    </div>
                `;
            }
        }, 300);
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
    async function openChat(contactId, type = 'alumni') {
        lastMessageId = 0;
        currentChat = { id: contactId, type: type };
        
        // Update contact list active state
        document.querySelectorAll('.contact-card').forEach(card => card.classList.remove('active'));
        
        // Find the active card - check both regular and search result cards
        const activeCard = document.querySelector(`.contact-card[onclick="openChat(${contactId}, '${type}')"]`);
        if (activeCard) activeCard.classList.add('active');
        
        // Show chat panel, hide empty state
        document.getElementById('noChatSelected').style.display = 'none';
        document.getElementById('chatHeader').style.display = 'flex';
        document.getElementById('chatMessages').style.display = 'block';
        document.getElementById('chatInput').style.display = 'flex';
        
        // Update header - check both allContacts and search results
        let contact = allContacts.find(c => c.id == contactId && c.type === type);
        
        // If not found in allContacts, it's a new contact from search
        if (!contact) {
            // Create a temporary contact object from the card data
            const cardElement = document.querySelector(`.contact-card[onclick="openChat(${contactId}, '${type}')"]`);
            if (cardElement) {
                const nameEl = cardElement.querySelector('.contact-name');
                const batchEl = cardElement.querySelector('.contact-batch');
                const avatarEl = cardElement.querySelector('.contact-avatar');
                const avatarImgEl = cardElement.querySelector('.contact-avatar-img');
                
                contact = {
                    id: contactId,
                    type: type,
                    full_name: nameEl ? nameEl.textContent.trim() : (type === 'admin' ? 'Admin' : 'Alumni'),
                    initials: avatarEl ? avatarEl.textContent.trim() : '??',
                    program: '',
                    batch: batchEl ? batchEl.textContent.trim().replace('Batch ', '') : '-',
                    is_online: false,
                    avatar: avatarImgEl ? avatarImgEl.src : null
                };
            }
        }
        
        if (contact) {
            const chatAvatar = document.getElementById('chatAvatar');
            
            if (contact.avatar) {
                chatAvatar.innerHTML = `<img src="${contact.avatar}" alt="${escapeHtml(contact.full_name)}" style="width: 100%; height: 100%; border-radius: 50%; object-fit: cover;">`;
            } else {
                chatAvatar.textContent = contact.initials;
                chatAvatar.style.background = 'linear-gradient(135deg, var(--nu-blue), var(--nu-blue-light))';
                chatAvatar.style.color = 'var(--nu-gold)';
            }
            
            document.getElementById('chatName').innerHTML = `${escapeHtml(contact.full_name)} ${contact.type === 'admin' ? '<span class="admin-badge" style="font-size: 0.65rem; background: var(--nu-gold); color: var(--nu-blue-dark); padding: 2px 8px; border-radius: 12px; margin-left: 8px; font-weight: 600;">ADMIN</span>' : ''}`;
            document.getElementById('chatStatus').innerHTML = `
                <span class="status-dot ${contact.is_online ? 'online' : ''}"></span> 
                ${contact.is_online ? 'Online' : 'Offline'}
            `;
        }
        
        // Load messages
        await loadMessages(contactId, type);
        
        // Focus input
        document.getElementById('messageInput').focus();
        // Show chat panel on mobile
        showChatOnMobile();
    }

    // ============================================
    // MOBILE CHAT VIEW TOGGLE
    // ============================================
    function showChatOnMobile() {
        if (window.innerWidth <= 1024) {
            document.querySelector('.contacts-panel').classList.add('chat-active');
            document.querySelector('.chat-panel').classList.add('chat-active');
        }
    }

    function showContactsOnMobile() {
        if (window.innerWidth <= 1024) {
            document.querySelector('.contacts-panel').classList.remove('chat-active');
            document.querySelector('.chat-panel').classList.remove('chat-active');
        }
    }
    
    async function loadMessages(contactId, type = 'alumni') {
        const container = document.getElementById('chatMessages');
        container.innerHTML = '<div class="loading-spinner"><i class="fa-solid fa-spinner fa-spin"></i> Loading messages...</div>';
        
        try {
            const response = await fetch(`/admin/messages/${type}/${contactId}`);
            if (!response.ok) throw new Error('Failed to load messages');
            
            const messages = await response.json();
            
            if (messages && messages.length > 0) {
                lastMessageId = Math.max(...messages.map(m => m.id));
            }
            
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
            
            const isSent = msg.sender_id == adminId;
            html += `
                <div class="message-group ${isSent ? 'sent' : 'received'}" data-msg-id="${msg.id}">
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
        
        if (msg.id && !msg.id.toString().startsWith('temp-')) {
            const existingMsg = document.querySelector(`[data-msg-id="${msg.id}"]`);
            if (existingMsg) {
                console.log('⚠️ Duplicate message prevented in appendMessage:', msg.id);
                return;
            }
        }
        
        const isSent = msg.sender_id == adminId;
        
        const messageHtml = `
            <div class="message-group ${isSent ? 'sent' : 'received'}" ${msg.id ? `data-msg-id="${msg.id}"` : ''}>
                <div class="message-bubble">
                    <p>${escapeHtml(msg.content)}</p>
                    <span class="msg-time">
                        ${msg.time}
                        ${isSent ? '<i class="fa-solid fa-check-double read-check"></i>' : ''}
                    </span>
                </div>
            </div>
        `;
        
        const emptyState = container.querySelector('.empty-state');
        if (emptyState) emptyState.remove();
        
        container.insertAdjacentHTML('beforeend', messageHtml);
    }
    
    async function sendMessage() {
        const input = document.getElementById('messageInput');
        const content = input.value.trim();
        
        if (!content || !currentChat) return;
        
        input.value = '';
        input.focus();
        
        const tempId = 'temp-' + Date.now();
        const tempMessage = {
            id: tempId,
            content: content,
            sender_id: adminId,
            sender_type: 'admin',
            is_read: false,
            created_at: new Date().toISOString(),
            time: formatTime(new Date()),
            attachments: []
        };
        
        appendMessage(tempMessage);
        scrollToBottom();
        
        try {
            const response = await fetch('/admin/messages/send', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    receiver_id: currentChat.id,
                    receiver_type: currentChat.type,
                    content: content
                })
            });
            
            if (!response.ok) throw new Error('Failed to send message');
            
            const data = await response.json();
            
            if (data.success) {
                const tempElement = document.querySelector(`[data-msg-id="${tempId}"]`);
                if (tempElement) tempElement.remove();
                
                appendMessage(data.message);
                scrollToBottom();
                
                lastMessageId = Math.max(lastMessageId, data.message.id);
                
                const contact = allContacts.find(c => c.id == currentChat.id && c.type === currentChat.type);
                if (contact) {
                    contact.last_message = content;
                    contact.last_message_time = 'Just now';
                }
                applyFilter();
            }
        } catch (error) {
            console.error('Error sending message:', error);
            const tempElement = document.querySelector(`[data-msg-id="${tempId}"]`);
            if (tempElement) tempElement.remove();
            
            input.value = content;
            alert('Failed to send message. Please try again.');
        }
    }
    
    async function markMessagesAsRead(contactId, contactType = 'alumni') {
        const contact = allContacts.find(c => c.id == contactId && c.type === contactType);
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
    // DIRECTORY REDIRECT HANDLER
    // ============================================
    function handleDirectoryRedirect() {
        const openChatData = sessionStorage.getItem('openChat');
        if (!openChatData) return;
        
        try {
            const chatData = JSON.parse(openChatData);
            sessionStorage.removeItem('openChat');
            
            console.log('📋 Directory redirect detected for:', chatData.name);
            
            // Function to attempt opening the chat
            const tryOpenChat = () => {
                if (allContacts.length > 0) {
                    console.log('✅ Conversations loaded, opening chat for:', chatData.name);
                    
                    // Check if contact exists in conversations
                    const existingContact = allContacts.find(
                        c => c.id == chatData.id && c.type === 'alumni'
                    );
                    
                    if (existingContact) {
                        // Open existing conversation
                        openChat(chatData.id, 'alumni');
                    } else {
                        // Create a placeholder and open chat
                        const initials = chatData.name
                            .split(' ')
                            .map(n => n.charAt(0))
                            .join('')
                            .toUpperCase();
                        
                        allContacts.unshift({
                            id: chatData.id,
                            type: 'alumni',
                            full_name: chatData.name,
                            initials: initials || '??',
                            program: '',
                            batch: '-',
                            is_online: false,
                            last_message: null,
                            last_message_time: null,
                            unread_count: 0,
                            avatar: null
                        });
                        
                        renderContacts(allContacts);
                        openChat(chatData.id, 'alumni');
                        
                        // Refresh conversations in background
                        setTimeout(() => loadConversations(), 1500);
                    }
                    return true; // Successfully handled
                }
                return false; // Conversations not loaded yet
            };
            
            // Try immediately
            if (tryOpenChat()) return;
            
            // If conversations aren't loaded yet, wait for them
            let attempts = 0;
            const maxAttempts = 25; // 5 seconds (25 * 200ms)
            
            const checkInterval = setInterval(() => {
                attempts++;
                
                if (tryOpenChat() || attempts >= maxAttempts) {
                    clearInterval(checkInterval);
                    
                    if (attempts >= maxAttempts) {
                        console.warn('⚠️ Timed out waiting for conversations to load');
                        // Force try one more time
                        tryOpenChat();
                    }
                }
            }, 200);
            
        } catch (error) {
            console.error('❌ Error handling directory redirect:', error);
            sessionStorage.removeItem('openChat');
        }
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
        
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(async () => {
            try {
                const response = await fetch(`/admin/messages/search/alumni?q=${encodeURIComponent(query)}`);
                const data = await response.json();
                
                if (!response.ok) {
                    const errorMsg = data.error || 'Unknown error';
                    const errorDetails = data.file ? ` (${data.file}:${data.line})` : '';
                    document.getElementById('searchResults').innerHTML = 
                        `<p style="color: #ef4444; text-align: center;">Error: ${errorMsg}${errorDetails}</p>`;
                    return;
                }
                
                if (data.length === 0) {
                    document.getElementById('searchResults').innerHTML = '<p style="color: #9ca3af; text-align: center;">No alumni found</p>';
                    return;
                }
                
                document.getElementById('searchResults').innerHTML = data.map(a => `
                    <div class="alumni-item" onclick="startNewChat(${a.id}, '${a.type}')">
                        ${a.avatar 
                            ? `<img src="${a.avatar}" class="contact-avatar-img" alt="${escapeHtml(a.full_name)}">`
                            : `<div class="alumni-avatar">${a.initials}</div>`
                        }
                        <div class="alumni-info">
                            <div class="name">${escapeHtml(a.full_name)}</div>
                            <div class="details">${a.type === 'admin' ? 'Admin Staff' : `Batch ${a.batch} | ${a.program || 'N/A'}`}</div>
                        </div>
                        ${a.is_online ? '<span class="online-dot" title="Online"></span>' : ''}
                    </div>
                `).join('');
            } catch (error) {
                console.error('Error searching:', error);
                document.getElementById('searchResults').innerHTML = 
                    `<p style="color: #ef4444; text-align: center;">Error searching. Please try again.</p>`;
            }
        }, 300);
    }
    
    async function startNewChat(alumniId, type = 'alumni') {
        closeNewMessageModal();
        
        // Check if contact already exists
        const existingContact = allContacts.find(c => c.id == alumniId && c.type === type);
        
        if (existingContact) {
            // Open existing conversation
            openChat(alumniId, type);
            return;
        }
        
        // Fetch alumni info directly from the server
        try {
            const response = await fetch(`/admin/messages/${type}/${alumniId}/info`);
            if (response.ok) {
                const data = await response.json();
                
                // Add to contacts with real data
                allContacts.unshift({
                    id: data.id,
                    type: data.type,
                    full_name: data.full_name,
                    initials: data.initials,
                    program: data.program || '',
                    batch: data.batch || '-',
                    is_online: data.is_online || false,
                    last_message: null,
                    last_message_time: null,
                    unread_count: 0,
                    avatar: data.avatar || null
                });
                
                renderContacts(allContacts);
                openChat(data.id, data.type);
            } else {
                // Fallback: create placeholder
                allContacts.unshift({
                    id: alumniId,
                    type: type,
                    full_name: type === 'admin' ? 'Admin Staff' : 'Alumni #' + alumniId,
                    initials: type === 'admin' ? 'AD' : 'AU',
                    program: type === 'admin' ? 'Admin' : '',
                    batch: '-',
                    is_online: false,
                    last_message: null,
                    last_message_time: null,
                    unread_count: 0,
                    avatar: null
                });
                
                renderContacts(allContacts);
                openChat(alumniId, type);
            }
        } catch (error) {
            console.error('Error fetching alumni info:', error);
            // Create fallback placeholder
            allContacts.unshift({
                id: alumniId,
                type: type,
                full_name: type === 'admin' ? 'Admin Staff' : 'Alumni #' + alumniId,
                initials: type === 'admin' ? 'AD' : 'AU',
                program: type === 'admin' ? 'Admin' : '',
                batch: '-',
                is_online: false,
                last_message: null,
                last_message_time: null,
                unread_count: 0,
                avatar: null
            });
            
            renderContacts(allContacts);
            openChat(alumniId, type);
        }
    }
    
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
    
    // ============================================
    // EVENT LISTENERS
    // ============================================
    document.addEventListener('DOMContentLoaded', function() {
        loadConversations();
        initSupabase();
        
        // Check for URL parameter to auto-open chat
        handleUrlChatRedirect();
    });

    // Handle chat redirect from URL parameter
    async function handleUrlChatRedirect() {
        const urlParams = new URLSearchParams(window.location.search);
        const chatId = urlParams.get('chat');
        
        if (!chatId) {
            console.log('📋 No chat parameter in URL');
            return;
        }
        
        console.log('📋 Found chat parameter:', chatId);
        
        // Clean up URL immediately
        if (window.history && window.history.replaceState) {
            const newUrl = window.location.pathname;
            window.history.replaceState({}, document.title, newUrl);
        }
        
        // Wait for conversations to load (max 3 seconds)
        let attempts = 0;
        const maxAttempts = 30;
        
        while (attempts < maxAttempts) {
            if (allContacts.length > 0 || attempts > 10) {
                console.log('✅ Checking for contact:', chatId);
                
                // Try to find the contact in existing conversations
                const existingContact = allContacts.find(c => c.id == chatId && c.type === 'alumni');
                
                if (existingContact) {
                    console.log('✅ Found existing contact, opening chat');
                    openChat(chatId, 'alumni');
                    return;
                }
                
                // Fetch alumni info directly from the server
                console.log('⚠️ Contact not in conversations, fetching alumni info...');
                try {
                    const response = await fetch(`/admin/messages/alumni/${chatId}/info`);
                    
                    if (response.ok) {
                        const alumni = await response.json();
                        console.log('✅ Found alumni:', alumni.full_name);
                        
                        allContacts.unshift({
                            id: alumni.id,
                            type: 'alumni',
                            full_name: alumni.full_name,
                            initials: alumni.initials,
                            program: alumni.program || '',
                            batch: alumni.batch || '-',
                            is_online: alumni.is_online || false,
                            last_message: null,
                            last_message_time: null,
                            unread_count: 0,
                            avatar: alumni.avatar || null
                        });
                        
                        renderContacts(allContacts);
                        openChat(alumni.id, 'alumni');
                    } else {
                        console.log('⚠️ Failed to fetch alumni, creating placeholder');
                        createPlaceholder(chatId);
                    }
                } catch (error) {
                    console.error('❌ Error fetching alumni:', error);
                    createPlaceholder(chatId);
                }
                
                return;
            }
            
            await new Promise(resolve => setTimeout(resolve, 100));
            attempts++;
        }
        
        console.warn('⚠️ Timed out, creating placeholder');
        createPlaceholder(chatId);
        
        function createPlaceholder(id) {
            allContacts.unshift({
                id: parseInt(id),
                type: 'alumni',
                full_name: 'Alumni #' + id,
                initials: 'AU',
                program: '',
                batch: '-',
                is_online: false,
                last_message: null,
                last_message_time: null,
                unread_count: 0,
                avatar: null
            });
            
            renderContacts(allContacts);
            openChat(id, 'alumni');
        }
    }
    
    // Close sidebar when clicking on a nav item (mobile)
    document.querySelectorAll('.nav-item').forEach(item => {
        item.addEventListener('click', function() {
            if (window.innerWidth <= 1024) {
                toggleMobileMenu();
            }
        });
    });
    
    // Close modal when clicking overlay
    document.getElementById('newMessageModal').addEventListener('click', function(e) {
        if (e.target === this) {
            closeNewMessageModal();
        }
    });
    
    // Handle window resize
    let resizeTimer;
    window.addEventListener('resize', function() {
        clearTimeout(resizeTimer);
        resizeTimer = setTimeout(function() {
            if (window.innerWidth > 1024) {
                // Reset mobile classes on desktop
                document.getElementById('adminSidebar')?.classList.remove('mobile-open');
                document.getElementById('mobileOverlay')?.classList.remove('active');
                document.querySelector('.contacts-panel')?.classList.remove('chat-active');
                document.querySelector('.chat-panel')?.classList.remove('chat-active');
                document.body.style.overflow = '';
            } else {
                // On mobile, show appropriate view
                if (currentChat) {
                    showChatOnMobile();
                } else {
                    showContactsOnMobile();
                }
            }
        }, 250);
    });
    
    // Cleanup on page unload
    window.addEventListener('beforeunload', function() {
        if (supabaseRealtimeChannel) {
            supabaseClient.removeChannel(supabaseRealtimeChannel);
        }
        if (pollingInterval) {
            clearInterval(pollingInterval);
        }
    });
</script>

</body>
</html>