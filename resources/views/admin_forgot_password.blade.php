<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password | LumiNUs Admin</title>
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
        .auth-header .logo {
            height: 40px;
            margin-bottom: 1rem;
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
        .auth-body .subtitle {
            color: #6B7280;
            font-size: 0.9375rem;
            margin-bottom: 1.5rem;
            text-align: center;
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
        .back-link {
            display: block;
            text-align: center;
            margin-top: 1.5rem;
            color: #32418C;
            text-decoration: none;
            font-weight: 500;
            font-size: 0.9375rem;
            transition: color 0.2s;
        }
        .back-link:hover {
            color: #253069;
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
        .alert-success {
            background: #D1FAE5;
            color: #065F46;
            border-color: #10B981;
        }
        .alert-error {
            background: #FEE2E2;
            color: #991B1B;
            border-color: #EF4444;
        }
        .alert i {
            margin-top: 0.125rem;
        }
    </style>
</head>
<body>
    <div class="auth-container">
        <div class="auth-card">
            <div class="auth-header">
                <h1>🔐 Forgot Password</h1>
                <p>LumiNUs Admin Portal</p>
            </div>
            <div class="auth-body">
                @if(session('status'))
                    <div class="alert alert-success">
                        <i class="fa-solid fa-circle-check"></i>
                        <span>{{ session('status') }}</span>
                    </div>
                @endif

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

                <p class="subtitle">Enter your admin email address and we'll send you a link to reset your password.</p>

                <form method="POST" action="{{ route('admin.send-reset-link') }}">
                    @csrf
                    <div class="form-group">
                        <label class="form-label" for="admin_email">Admin Email</label>
                        <input type="email" name="admin_email" id="admin_email" class="form-control" placeholder="admin@example.com" value="{{ old('admin_email') }}" required autofocus>
                    </div>

                    <button type="submit" class="btn-primary">
                        <i class="fa-solid fa-paper-plane"></i> Send Reset Link
                    </button>
                </form>

                <a href="{{ route('admin.login') }}" class="back-link">
                    <i class="fa-solid fa-arrow-left"></i> Back to Sign In
                </a>
            </div>
        </div>
    </div>
</body>
</html>