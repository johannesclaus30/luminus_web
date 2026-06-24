<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password | LumiNUs Admin</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="/css/admin.css">
    <link rel="icon" type="image/png" href="/assets/logos/LumiNUs_Icon.png">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background: #F9FAFB;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
        }
        .auth-container {
            width: 100%;
            max-width: 440px;
        }
        .auth-card {
            background: #FFFFFF;
            border-radius: 1.25rem;
            box-shadow: 0 10px 40px rgba(0,0,0,0.08);
            border: 1px solid #E5E7EB;
            overflow: hidden;
        }
        .auth-header {
            background: linear-gradient(135deg, #32418C, #253069);
            padding: 2.5rem 2rem;
            text-align: center;
        }
        .auth-header h1 {
            color: #FFFFFF;
            font-size: 1.5rem;
            font-weight: 600;
            margin: 0;
        }
        .auth-header p {
            color: rgba(255,255,255,0.8);
            font-size: 0.9375rem;
            margin-top: 0.5rem;
        }
        .auth-body {
            padding: 2rem;
        }
        .form-group {
            margin-bottom: 1.25rem;
        }
        .form-label {
            display: block;
            font-size: 0.9375rem;
            font-weight: 600;
            color: #374151;
            margin-bottom: 0.5rem;
        }
        .form-control {
            width: 100%;
            padding: 0.875rem 1rem;
            font-size: 1rem;
            font-family: 'Poppins', sans-serif;
            border: 2px solid #E5E7EB;
            border-radius: 0.75rem;
            transition: all 0.2s;
        }
        .form-control:focus {
            outline: none;
            border-color: #32418C;
            box-shadow: 0 0 0 3px rgba(50,65,140,0.1);
        }
        .btn-primary {
            width: 100%;
            padding: 0.875rem;
            background: #FBD117;
            color: #253069;
            border: none;
            border-radius: 0.75rem;
            font-weight: 600;
            font-size: 1rem;
            cursor: pointer;
            font-family: 'Poppins', sans-serif;
            transition: all 0.2s;
            box-shadow: 0 4px 14px rgba(251,209,23,0.3);
        }
        .btn-primary:hover {
            background: #e6bd0d;
            transform: translateY(-1px);
            box-shadow: 0 6px 20px rgba(251,209,23,0.35);
        }
        .alert {
            padding: 1rem;
            border-radius: 0.75rem;
            margin-bottom: 1.5rem;
            font-size: 0.9375rem;
            display: flex;
            align-items: flex-start;
            gap: 0.75rem;
            border: 1px solid;
        }
        .alert-error {
            background: #FEE2E2;
            color: #991B1B;
            border-color: #EF4444;
        }
        .alert i {
            margin-top: 0.125rem;
        }
        .password-hint {
            font-size: 0.8125rem;
            color: #6B7280;
            margin-top: 0.375rem;
        }
        .password-input-wrapper {
            position: relative;
        }
        .password-input-wrapper .form-control {
            padding-right: 3rem;
        }
        .password-toggle {
            position: absolute;
            right: 0.75rem;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: #9CA3AF;
            cursor: pointer;
            padding: 0.5rem;
            font-size: 1rem;
        }
        .password-toggle:hover {
            color: #32418C;
        }
    </style>
</head>
<body>
    <div class="auth-container">
        <div class="auth-card">
            <div class="auth-header">
                <h1>🔒 Reset Password</h1>
                <p>Create a new password for your admin account</p>
            </div>
            <div class="auth-body">
                @if(session('error'))
                    <div class="alert alert-error">
                        <i class="fa-solid fa-circle-exclamation"></i>
                        <span>{{ session('error') }}</span>
                    </div>
                @endif

                @if($errors->any())
                    <div class="alert alert-error">
                        <i class="fa-solid fa-circle-exclamation"></i>
                        <span>{{ $errors->first() }}</span>
                    </div>
                @endif

                <form method="POST" action="{{ route('admin.reset-password.process') }}">
                    @csrf
                    <input type="hidden" name="token" value="{{ $token }}">
                    <input type="hidden" name="email" value="{{ $email }}">

                    <div class="form-group">
                        <label class="form-label" for="password">New Password</label>
                        <div class="password-input-wrapper">
                            <input type="password" name="password" id="password" class="form-control" placeholder="Min. 8 characters" required minlength="8" autofocus>
                            <button type="button" class="password-toggle" onclick="togglePassword(this)" tabindex="-1">
                                <i class="fa-solid fa-eye"></i>
                            </button>
                        </div>
                        <p class="password-hint">Must be at least 8 characters long.</p>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="password_confirmation">Confirm New Password</label>
                        <div class="password-input-wrapper">
                            <input type="password" name="password_confirmation" id="password_confirmation" class="form-control" placeholder="Re-enter your password" required minlength="8">
                            <button type="button" class="password-toggle" onclick="togglePassword(this)" tabindex="-1">
                                <i class="fa-solid fa-eye"></i>
                            </button>
                        </div>
                    </div>

                    <button type="submit" class="btn-primary">
                        <i class="fa-solid fa-lock"></i> Reset Password
                    </button>
                </form>
            </div>
        </div>
    </div>

    <script>
        function togglePassword(button) {
            const input = button.parentElement.querySelector('input');
            const icon = button.querySelector('i');
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        }
    </script>
</body>
</html>