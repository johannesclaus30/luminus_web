<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Events | LumiNUs Admin</title>

    <link rel="stylesheet" href="/css/admin.css">
    <link rel="stylesheet" href="/css/events.css">
    <link rel="icon" type="image/png" href="/assets/logos/LumiNUs_Icon.png">
</head>
<body>
    
    <nav class="nav-main">
        <img class="nav-logo" src="/assets/logos/LumiNUs_Logo_Landscape_White.png" alt="LumiNUs Logo">
    </nav>

    <div class="layout-wrapper">
        <div class="admin-menu">
            <div>
                <p class="text-titles">Admin Menu</p>
                <a href="{{ url('admin/dashboard') }}" class="admin-menu-buttons">Admin Dashboard</a>
                <a href="{{ url('admin/directory') }}" class="admin-menu-buttons">Alumni Directory</a>
                <a href="{{ route('announcements.index') }}" class="admin-menu-buttons">Announcement Editor</a>
                <a href="{{ route('events.index') }}" class="admin-menu-current">Event Organizer</a>
                <a href="{{ route('perks.index') }}" class="admin-menu-buttons">Perks and Discounts</a>
                <a href="{{ url('admin/alumni_tracer') }}" class="admin-menu-buttons">NU Alumni Tracer</a>
                <a href="{{ url('admin/messages') }}" class="admin-menu-buttons">Messages</a>
                <a href="{{ url('admin/settings') }}" class="admin-menu-buttons">Settings</a>
            </div>

            <a href="{{ route('admin.logout') }}" class="admin-menu-signout">Sign Out</a>
        </div>

        <div class="events-panel admin-scrollable">
            <div class="events-panel-header">
                <div class="add-events-container">
                    <a href="{{ route('events.create') }}" class="add-events-button">Add New Event</a>
                    <a id="archiveToggleBtn" href="{{ route('events.archived') }}" class="add-events-button" style="margin-left:8px; background-color:#818181; color:#ffffff; width:auto;">Archived Events</a>
                </div>
                <div class="pagination-container">
                    {{ $events->links() }}
                </div>
            </div>
            
            <div class="events-list-wrapper">
                @forelse ($events as $event)
                    <div class="events-container">
                        <div class="events-title-description">
                            <div style="display: flex; align-items: center; gap: 10px;">
                                <p class="events-title-text" style="margin: 0;">{{ $event->title }}</p>
                                <i>
                                    <span class="status-badge {{ $event->status == 'Active' ? 'badge-active' : 'badge-archived' }}">
                                    {{ $event->status }}
                                </span>
                                </i>
                                
                            </div>

                            <p class="events-description-text">
                                <strong>Type:</strong> {{ $event->event_type }} <br>
                                <strong>Platform:</strong> {{ $event->platform ?: 'N/A' }} <br>
                                <strong>Platform URL:</strong> {{ $event->platform_url ?: 'N/A' }} <br>
                                <strong>Venue:</strong>
                                @if ($event->venue)
                                    {{ $event->venue->name }}<br>
                                    {{ $event->venue->address }}<br>
                                    <small>{{ $event->venue->latitude }}, {{ $event->venue->longitude }}</small>
                                @else
                                    N/A
                                @endif
                                <br>
                                <strong>Date:</strong> {{ $event->start_date ? $event->start_date->format('F d, Y') : 'N/A' }} <br>
                                <strong>Uploaded by:</strong>
                                @if ($event->admin)
                                    {{ $event->admin->AdminFirstName }} {{ $event->admin->AdminLastName }}
                                @endif
                                (Admin ID: {{ $event->admin_id }})
                            </p>
                            </div>
                        
                        <div class="events-image-container">
                            <p class="events-image-text">Attachments:</p>
                            <div class="attachment-preview-wrapper" style="display: flex; flex-wrap: wrap; gap: 10px; align-items: flex-start;">
                                @foreach($event->images as $image)
                                    <img src="{{ $image->image_url }}" 
                                         alt="Event Image" 
                                         style="width: 80px; height: 80px; object-fit: cover; border-radius: 5px; cursor: pointer;"
                                         onclick="openModal(this.src)">
                                @endforeach
                            </div>
                        </div>

                        <div class="events-tools">
                            <div class="events-tools-analytics">
                                <span>Capacity</span>
                                <p>👥 {{ $event->max_capacity }} Max</p>
                            </div>

                            @if ($event->status === 'Archived')
                                <form action="{{ route('events.restore', $event) }}" method="POST" onsubmit="return confirm('Restore this event?');">
                                    @csrf
                                    @method('PUT')
                                    <button type="submit" class="events-edit-archive-btn edit-btn">Restore</button>
                                </form>
                            @else
                                <a href="{{ route('events.edit', $event) }}" class="events-edit-archive-btn edit-btn">Edit</a>
                                <a href="#" class="events-edit-archive-btn manage-btn">Attendees</a>
                                
                                <form action="{{ route('events.destroy', $event) }}" method="POST" onsubmit="return confirm('Archive this event?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="events-edit-archive-btn archive-btn" style="width: 100%; border: none; cursor: pointer;">Archive</button>
                                </form>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="events-container" style="justify-content: center;">
                        <p class="events-description-text">No events have been scheduled yet. Time to plan something big!</p>
                    </div>
                @endforelse

                <div class="pagination-container bottom-pagination">
                    {{ $events->links() }}
                </div>
            </div>
        </div>
    </div>

    <div id="imageModal" class="custom-modal" style="display:none;" onclick="closeModal()">
        <span class="close-modal">&times;</span>
        <img class="modal-content" id="enlargedImage">
    </div>

    <script>
        function openModal(src) {
            document.getElementById('imageModal').style.display = "block";
            document.getElementById('enlargedImage').src = src;
        }
        function closeModal() {
            document.getElementById('imageModal').style.display = "none";
        }

        document.addEventListener('DOMContentLoaded', function () {
            const btn = document.getElementById('archiveToggleBtn');
            if (!btn) return;

            const archivedPath = new URL(btn.href).pathname.replace(/\/$/, '');
            const currentPath = window.location.pathname.replace(/\/$/, '');

            if (currentPath === archivedPath) {
                btn.textContent = 'View Active Events';
                btn.href = '{{ route('events.index') }}';
            }
        });
    </script>
</body>
</html>