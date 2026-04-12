<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Events</title>

    <link rel="stylesheet" href="/css/admin_dashboard.css">
    <link rel="stylesheet" href="/css/admin.css">
    <link rel="stylesheet" href="/css/perks.css">
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
                <a href="announcements" class="admin-menu-buttons">Announcement Editor</a>
                <a href="events" class="admin-menu-buttons">Event Organizer</a>
                <a href="perks" class="admin-menu-current">Perks and Discounts</a>
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
                    <a href="{{ route('perks.create') }}" class="add-perks-button">Add New Perks</a>
                </div>
                <div class="pagination-container">
                    {{ $perks->links() }}
                </div>
            </div>
            
            <div>
                @forelse ($perks as $perk)
                    <div class="perks-container">
                        <div class="perks-title-description">
                            <p class="perks-title-text">{{ $perk->PerkTitle }}</p>
                            <p class="perks-description-text">{{ $perk->PerkDescription }}</p>
                            <p class="perks-description-text">Valid until {{ \Carbon\Carbon::parse($perk->PerkValidity)->format('F d, Y') }}</p>
                        </div>
                        <div class="perks-image-container">
                            <p class="perks-image-text">Attachments:</p>

                            @if (!empty($perk->PerkImage))
                                <img
                                    src="{{ asset('storage/' . $perk->PerkImage) }}"
                                    alt="Perk Image"
                                    class="perk-image"
                                    onclick="openModal(this.src)"
                                >
                            @else
                                <img
                                    src="{{ asset('assets/FINAL-NULIPA.jpg') }}"
                                    alt="No attachment available"
                                    class="perk-image"
                                    onclick="openModal(this.src)"
                                >
                            @endif
                        </div>
                        
                        <div class="perks-tools">
                            <div class="perks-tools-analytics">
                                <span>Analytics</span>
                                <p>👁 No Data Yet</p>
                            </div>
                            <a href="{{ route('perks.edit', $perk->id) }}" class="perk-edit-archive-btn edit-btn">Edit</a>
                            <a href="" class="perk-edit-archive-btn archive-btn">Archive</a>
                        </div>
                    </div>
                @empty
                    <div class="perks-container" style="justify-content: center;">
                        <p class="perks-description-text">No perks available.</p>
                    </div>
                @endforelse

                <div class="pagination-container bottom-pagination">
                    {{ $perks->links() }}
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

        // Close on 'Esc' key
        document.addEventListener('keydown', function(event) {
            if (event.key === "Escape") {
                closeModal();
            }
        });
    </script>

</body>
</html>