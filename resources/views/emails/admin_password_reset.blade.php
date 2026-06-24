<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Your Admin Password</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            background-color: #F9FAFB;
            margin: 0;
            padding: 0;
            -webkit-font-smoothing: antialiased;
        }
        .email-wrapper {
            max-width: 600px;
            margin: 0 auto;
            background-color: #FFFFFF;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
        }
        .email-header {
            background: linear-gradient(135deg, #32418C, #253069);
            padding: 40px 30px;
            text-align: center;
        }
        .email-header img {
            height: 50px;
            margin-bottom: 15px;
        }
        .email-header h1 {
            color: #FBD117;
            font-size: 24px;
            margin: 0;
            font-weight: 600;
        }
        .email-body {
            padding: 40px 30px;
            color: #374151;
            line-height: 1.6;
        }
        .email-body h2 {
            color: #32418C;
            font-size: 20px;
            margin-bottom: 20px;
        }
        .email-body p {
            margin-bottom: 20px;
            font-size: 16px;
        }
        .reset-button {
            display: inline-block;
            background-color: #FBD117;
            color: #253069;
            text-decoration: none;
            padding: 14px 32px;
            border-radius: 10px;
            font-weight: 600;
            font-size: 16px;
            margin: 20px 0;
            box-shadow: 0 4px 14px rgba(251, 209, 23, 0.3);
        }
        .reset-link {
            word-break: break-all;
            color: #6B7280;
            font-size: 14px;
            background: #F3F4F6;
            padding: 12px;
            border-radius: 8px;
            display: block;
            margin: 20px 0;
        }
        .warning-box {
            background: #FEF3C7;
            border-left: 4px solid #F59E0B;
            padding: 16px;
            border-radius: 8px;
            margin: 20px 0;
            font-size: 14px;
            color: #92400E;
        }
        .email-footer {
            background: #F9FAFB;
            padding: 20px 30px;
            text-align: center;
            border-top: 1px solid #E5E7EB;
        }
        .email-footer p {
            color: #9CA3AF;
            font-size: 13px;
            margin: 0;
        }
    </style>
</head>
<body style="padding: 40px 20px;">
    <div class="email-wrapper">
        <div class="email-header">
            <h1>🔐 Reset Your Admin Password</h1>
        </div>
        
        <div class="email-body">
            <h2>Hello, {{ $admin->admin_first_name ?? 'Admin' }}!</h2>
            
            <p>We received a request to reset the password for your <strong>LumiNUs Admin</strong> account. Click the button below to create a new password:</p>
            
            <div style="text-align: center;">
                <a href="{{ route('admin.reset-password', ['token' => $token, 'email' => $admin->admin_email]) }}" class="reset-button">
                    Reset My Password
                </a>
            </div>
            
            <p style="font-size: 14px; color: #6B7280;">Or copy and paste this link into your browser:</p>
            <span class="reset-link">
                {{ route('admin.reset-password', ['token' => $token, 'email' => $admin->admin_email]) }}
            </span>
            
            <div class="warning-box">
                <strong>⏰ This link will expire in 1 hour.</strong><br>
                If you didn't request a password reset, you can safely ignore this email. Your password will remain unchanged.
            </div>
        </div>
        
        <div class="email-footer">
            <p>© {{ date('Y') }} LumiNUs • National University • Admin Portal</p>
        </div>
    </div>
</body>
</html>