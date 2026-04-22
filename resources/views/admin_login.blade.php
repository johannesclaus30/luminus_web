<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LumiNUs | Administrator</title>

    <link rel="stylesheet" href="/css/admin_login.css">

</head>
{{-- <body background="/assets/admin_login_background.jpg"> --}}
<body>
    
<div class="layout-wrapper">

    <div class="building-wrapper">
        <img class="building-img" src="/assets/NULipa_Building.jpg" alt="NULipa Building">
    </div>

    <div class="login-container">
        <div class="login-container-content">

            <img class="luminus-logo" src="/assets/logos/LumiNUs_Logo_Landscape_White.png" alt="LumiNUs Logo">
            <p class="subtitle">Admin Account</p>

            <form id="admin-login-form" method="POST" action="{{ route('admin.login.attempt') }}">
                @csrf
                <label>Email</label>
                <input class="login-textfield" type="email" name="admin_email" value="{{ old('admin_email') }}" placeholder="Enter Your Admin Email Address" required>

                <label>Password</label>
                <input class="login-textfield" type="password" name="password" placeholder="Enter Your Password" required>

                <button type="submit" style="display:none;" aria-hidden="true" tabindex="-1"></button>
                <a href="#" class="login-btn" onclick="event.preventDefault(); document.getElementById('admin-login-form').submit();">Sign In</a>
            </form>
            

        </div>
    </div>

</div>

</body>
</html>