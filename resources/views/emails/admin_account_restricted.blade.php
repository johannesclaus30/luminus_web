<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: 'Poppins', Arial, sans-serif; background: #f3f4f6; margin: 0; padding: 0; }
        .container { max-width: 520px; margin: 40px auto; background: #ffffff; border-radius: 16px; overflow: hidden; box-shadow: 0 4px 24px rgba(0,0,0,0.08); }
        .header { padding: 32px 24px; text-align: center; }
        .header.restricted { background: #EF4444; }
        .header.restored { background: #10B981; }
        .header img { height: 40px; }
        .body { padding: 32px 24px; color: #374151; }
        .body h2 { color: #1F2937; margin: 0 0 12px; font-size: 20px; }
        .body p { font-size: 15px; line-height: 1.6; margin: 0 0 16px; color: #4B5563; }
        .status-badge { display: inline-block; padding: 6px 16px; border-radius: 20px; font-size: 13px; font-weight: 600; margin: 16px 0; }
        .status-badge.restricted { background: #FEE2E2; color: #991B1B; }
        .status-badge.restored { background: #D1FAE5; color: #065F46; }
        .info-box { background: #F9FAFB; border: 1px solid #E5E7EB; border-radius: 10px; padding: 16px; margin: 20px 0; }
        .info-box p { font-size: 14px; margin: 0; }
        .info-box strong { color: #1F2937; }
        .footer { background: #F9FAFB; padding: 20px 24px; text-align: center; border-top: 1px solid #E5E7EB; }
        .footer p { font-size: 12px; color: #9CA3AF; margin: 0; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header {{ $isRestricted ? 'restricted' : 'restored' }}">
            <img src="{{ asset('assets/logos/LumiNUs_Logo_Landscape_White.png') }}" alt="LumiNUs">
        </div>
        <div class="body">
            @if($isRestricted)
                <h2>Your Account Has Been Restricted</h2>
                <p>Hello <strong>{{ $admin->admin_first_name }}</strong>,</p>
                <p>Your LumiNUs admin account has been <strong>temporarily restricted</strong> by {{ $updatedBy }}. You will not be able to access the admin dashboard until your account is restored.</p>
                <div class="status-badge restricted">🔒 Account Restricted</div>
                <div class="info-box">
                    <p><strong>What to do:</strong> Contact the NU Lipa Alumni Affairs Office Coordinator for more information or to request account restoration.</p>
                </div>
            @else
                <h2>Your Account Has Been Restored</h2>
                <p>Hello <strong>{{ $admin->admin_first_name }}</strong>,</p>
                <p>Your LumiNUs admin account has been <strong>restored</strong> by {{ $updatedBy }}. You can now access the admin dashboard again.</p>
                <div class="status-badge restored">✅ Account Restored</div>
                <div class="info-box">
                    <p><strong>Welcome back!</strong> You may now log in using your existing credentials.</p>
                </div>
            @endif
        </div>
        <div class="footer">
            <p>LumiNUs - NU Lipa Alumni Affairs Office</p>
            <p>This is an automated message. Please do not reply to this email.</p>
        </div>
    </div>
</body>
</html>