<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Admin')</title>

    <link rel="stylesheet" href="/css/admin_dashboard.css">
    <link rel="stylesheet" href="/css/admin.css">
    <link rel="stylesheet" href="/css/perks.css">
    <link rel="icon" type="image/png" href="/assets/logos/LumiNUs_Icon.png">
</head>
<body>

<nav class="nav-main">
    <img class="nav-logo" src="/assets/logos/LumiNUs_Logo_Landscape.png" alt="LumiNUs Logo">
</nav>

<!-- PAGE CONTENT -->
@yield('content')

</body>
</html>