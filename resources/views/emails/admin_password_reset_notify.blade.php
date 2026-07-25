<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: 'Poppins', Arial, sans-serif; background: #f3f4f6; margin: 0; padding: 0; }
        .container { max-width: 520px; margin: 40px auto; background: #ffffff; border-radius: 16px; overflow: hidden; box-shadow: 0 4px 24px rgba(0,0,0,0.08); }
        .header { background: #32418C; padding: 32px 24px; text-align: center; }
        .header img { height: 40px; }
        .body { padding: 32px 24px; color: #374151; }
        .body h2 { color: #1F2937; margin: 0 0 12px; font-size: 20px; }
        .body p { font-size: 15px; line-height: 1.6; margin: 0 0 16px; color: #4B5563; }
        .password-box { background: #FEF3C7; border: 1px solid #FBD117; border-radius: 10px; padding: 16px; text-align: center; margin: 20px 0; }
        .password-box code { font-size: 22px; font-weight: 700; color: #32418C; letter-spacing: 2px; background: none; }
        .meta { font-size: 13px; color: #9CA3AF; margin-top: 8px; }
        .warning { background: #FEF2F2; border-left: 4px solid #EF4444; padding: 12px 16px; border-radius: 0 8px 8px 0; margin-top: 20px; }
        .warning p { font-size: 13px; color: #991B1B; margin: 0; }
        .footer { background: #F9FAFB; padding: 20px 24px; text-align: center; border-top: 1px solid #E5E7EB; }
        .footer p { font-size: 12px; color: #9CA3AF; margin: 0; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <img src="{{ asset('assets/logos/LumiNUs_Logo_Landscape_White.png') }}" alt="LumiNUs">
        </div>
        <div class="body">
            <h2>Your Password Has Been Reset</h2>
            <p>Hello <strong>{{ $admin->admin_first_name }}</strong>,</p>
            <p>Your LumiNUs admin account password has been reset by <strong>{{ $resetBy }}</strong>. Use the temporary password below to log in:</p>
            
            <div class="password-box">
                <code>{{ $temporaryPassword }}</code>
            </div>
            <p class="meta">Reset by: {{ $resetBy }}</p>
            
            <div class="warning">
                <p><strong>⚠️ Important:</strong> Please log in and change your password immediately using the <strong>Security</strong> tab in Settings.</p>
            </div>
        </div>
        <div class="footer">
            <p>LumiNUs - NU Lipa Alumni Affairs Office</p>
            <p>This is an automated message. Please do not reply to this email.</p>
        </div>
    </div>
</body>
</html>