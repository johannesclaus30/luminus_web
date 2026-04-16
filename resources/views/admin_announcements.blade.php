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
        <img class="nav-logo" src="/assets/logos/LumiNUs_Logo_Landscape_White.png" alt="LumiNUs Logo">
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

                            {{-- A simplified grid that contains both images and videos --}}
                            <div class="attachment-preview-wrapper" style="display: flex; flex-wrap: wrap; gap: 10px; align-items: flex-start;">
                                
                                {{-- Iterate through all attachments from the hasMany relation --}}
                                @forelse ($announcement->images as $attachment)
                                    {{-- File Type Detection logic: get the extension --}}
                                    @php
                                        $path = $attachment->ImagePath;
                                        $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));
                                        
                                        // Define allowed extensions for broader compatibility
                                        $imageExtensions = ['jpg', 'jpeg', 'png', 'gif', 'svg', 'webp'];
                                        $videoExtensions = ['mp4', 'webm', 'ogg'];
                                    @endphp

                                    {{-- 1. Display as an Image if it is an image --}}
                                    @if (in_array($extension, $imageExtensions))
                                        <img
                                            src="{{ asset('storage/' . $path) }}"
                                            alt="Announcement"
                                            class="perk-image" {{-- The JS looks for this class --}}
                                            style="width: 80px; height: 80px; object-fit: cover; border-radius: 5px; border: 1px solid #eee;"
                                        >
                                    
                                    {{-- 2. Display as a Video if it is a video --}}
                                    @elseif (in_array($extension, $videoExtensions))
                                        <div class="video-wrapper">
                                            <video 
                                                style="max-width: 150px; max-height: 200px; width: auto; border-radius: 5px; background: #000; border: 1px solid #ddd;" 
                                                controls>
                                                <source src="{{ asset('storage/' . $path) }}" type="video/{{ $extension }}">
                                                Your browser does not support the video tag.
                                            </video>
                                        </div>
                                    @endif

                                @empty
                                    {{-- Default fallback if NO attachments exist in the list --}}
                                    {{-- <img
                                        src="{{ asset('assets/FINAL-NULIPA.jpg') }}"
                                        alt="No attachment available"
                                        class="perk-image"
                                        style="width: 80px; height: 80px; object-fit: cover; opacity: 0.5;"
                                    > --}}
                                @endforelse
                            </div>
                        </div>

                        <!-- RIGHT COLUMN -->
                        <div class="perks-tools">
                            <div class="perks-tools-analytics">
                                <span>Analytics</span>
                                <p>👁 No Data Yet</p>
                            </div>

                            <a href="{{ route('announcements.edit', $announcement->Announcement_ID) }}" class="perk-edit-archive-btn edit-btn">Edit</a>
                            <a href="#" class="perk-edit-archive-btn archive-btn">Archive</a>
                        </div>
                    </div>
                @empty
                    <div class="announcements-container" style="justify-content: center;">
                        <p class="announcements-description-text">No announcements yet!</p>
                    </div>
                @endforelse

                <div class="pagination-container bottom-pagination">
                    {{ $announcements->links() }}
                </div>
            </div>
        </div>
    </div>

<div id="imageModal" class="custom-modal" onclick="closeModal()">
    <span class="close-modal">&times;</span>
    <img class="modal-content" id="enlargedImage">
</div>

<script>
        function openModal(src) {
        const modal = document.getElementById("imageModal");
        const modalImg = document.getElementById("enlargedImage");
        modal.style.display = "flex";
        modalImg.src = src;
    }

    function closeModal() {
        document.getElementById("imageModal").style.display = "none";
    }

    // Attach click event to all perk images
    document.addEventListener("DOMContentLoaded", function() {
        const images = document.querySelectorAll(".perk-image");
        images.forEach(img => {
            img.style.cursor = "zoom-in"; // Change cursor to show it's clickable
            img.onclick = function() {
                openModal(this.src);
            };
        });
    });
</script>

</body>
</html>