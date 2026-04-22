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
            </div>

            <a href="{{ route('admin.logout') }}" class="admin-menu-signout">Sign Out</a>
        </div>
        <div class="directory-container admin-scrollable">
            
            <div class="search-container">
                <input type="text" placeholder="Search alumni..." class="search-bar">
                {{-- <button class="search-button">Search</button> --}}
            </div>
            <div class="user-container">
                @foreach($admins as $admin)
                    <div class="user-box">
                        <img src="/assets/CLAUS_JOHANNES_PHOTO.jpg" alt="{{ $admin->admin_first_name }}" class="user-photo">
                        
                        <div class="primary-info">
                            <h1 class="name">
                                {{ $admin->admin_first_name }} {{ $admin->admin_last_name }}
                            </h1>
                            <p class="program">{{ $admin->admin_role }}</p> 
                        </div>
                        
                        <p class="email">{{ $admin->admin_email }}</p>
                        
                        <div class="tools-container">
                            <a href="#" class="tools-button" title="Message"><i class="fa-solid fa-comment-dots"></i></a>
                            <a href="#" class="tools-button" title="View Profile"><i class="fa-solid fa-eye"></i></a>
                            <a href="#" class="tools-button" title="Edit Info"><i class="fa-solid fa-circle-info"></i></a>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

</body>
</html>