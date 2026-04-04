<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Events</title>

    <link rel="stylesheet" href="/css/admin.css">
    <link rel="stylesheet" href="/css/perks.css">
    <link rel="stylesheet" href="/css/announcements.css">
    <link rel="icon" type="image/png" href="/assets/logos/LumiNUs_Icon.png">

</head>
<body>
    
    <nav class="nav-main">
        <img class="nav-logo" src="/assets/logos/LumiNUs_Logo_Landscape.png" alt="LumiNUs Logo">
    </nav>

    <div class="layout-wrapper">
        <div class="admin-menu">
            <div>
                <p class="text-titles">Admin Menu</p>

                <a href="dashboard" class="admin-menu-buttons">Admin Dashboard</a>
                <a href="directory" class="admin-menu-buttons">Alumni Directory</a>
                <a href="announcements" class="admin-menu-current">Announcement Editor</a>
                <a href="events" class="admin-menu-buttons">Event Organizer</a>
                <a href="perks" class="admin-menu-buttons">Perks and Discounts</a>
                <a href="alumni_tracer" class="admin-menu-buttons">NU Alumni Tracer</a>
                <a href="messages" class="admin-menu-buttons">Messages</a>
                <a href="settings" class="admin-menu-buttons">Settings</a>
                <a href="testing" class="admin-menu-buttons">Users Testing</a>
            </div>

            <a href="login" class="admin-menu-signout">Sign Out</a>
        </div>
        <div class="perks-panel admin-scrollable">
            <div class="perks-panel-header">
                <div class="add-perks-container">
                    <a href="{{ route('announcements.create') }}" class="add-perks-button">Add New Announcement</a>
                </div>
                <div class="pagination-container">
                    {{ $announcements->links() }}
                </div>
            </div>
            
            <div>
                @forelse ($announcements as $announcement)
                    <div class="perks-container">
                        <div class="perks-title-description">
                            <p class="perks-title-text">
                                {{ $announcement->AnnouncementTitle }}
                            </p>

                            <p class="perks-description-text">
                                {{ $announcement->AnnouncementDescription }}
                            </p>

                            <p class="perks-description-text">
                                {{ \Carbon\Carbon::parse($announcement->DatePosted)->format('F d, Y') }}
                            </p>
                        </div>
                        
                        <div class="perks-image-container">
                            <p class="perks-image-text">Attachments:</p>

                            @if ($announcement->images->count() > 0)
                                @foreach ($announcement->images as $image)
                                    <img
                                        src="{{ asset('storage/' . $image->ImagePath) }}"
                                        alt="Announcement Image"
                                        class="perk-image"
                                    >
                                @endforeach
                            @else
                                <img
                                    src="{{ asset('assets/FINAL-NULIPA.jpg') }}"
                                    alt="No attachment available"
                                    class="perk-image"
                                >
                            @endif
                        </div>

                        <!-- RIGHT COLUMN -->
                        <div class="perks-tools">
                            <div class="perks-tools-analytics">
                                <span>Analytics</span>
                                <p>👁 No Data Yet</p>
                            </div>

                            <a href="#" class="perk-edit-archive-btn edit-btn">Edit</a>
                            <a href="#" class="perk-edit-archive-btn archive-btn">Archive</a>
                        </div>
                    </div>
                @empty
                    <p class="perks-description-text">No announcements available.</p>
                @endforelse

                <div class="pagination-container bottom-pagination">
                    {{ $announcements->links() }}
                </div>
            </div>
        </div>
    </div>

</body>
</html>