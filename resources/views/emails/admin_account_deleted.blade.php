<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: 'Poppins', Arial, sans-serif; background: #f3f4f6; margin: 0; padding: 0; }
        .container { max-width: 520px; margin: 40px auto; background: #ffffff; border-radius: 16px; overflow: hidden; box-shadow: 0 4px 24px rgba(0,0,0,0.08); }
        .header { background: #1F2937; padding: 32px 24px; text-align: center; }
        .header img { height: 40px; }
        .body { padding: 32px 24px; color: #374151; }
        .body h2 { color: #1F2937; margin: 0 0 12px; font-size: 20px; }
        .body p { font-size: 15px; line-height: 1.6; margin: 0 0 16px; color: #4B5563; }
        .info-box { background: #FEF2F2; border: 1px solid #FECACA; border-radius: 10px; padding: 16px; margin: 20px 0; }
        .info-box p { font-size: 14px; margin: 0; color: #991B1B; }
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
            <h2>Your Admin Account Has Been Removed</h2>
            <p>Hello <strong>{{ $adminName }}</strong>,</p>
            <p>Your LumiNUs admin account <strong>({{ $adminEmail }})</strong> has been permanently deleted by <strong>{{ $deletedBy }}</strong>.</p>
            <p>You no longer have access to the LumiNUs admin dashboard.</p>
            <div class="info-box">
                <p><strong>Note:</strong> If you believe this was a mistake, please contact the NU Lipa Alumni Affairs Office Coordinator immediately.</p>
            </div>
        </div>
        <div class="footer">
            <p>LumiNUs - NU Lipa Alumni Affairs Office</p>
            <p>This is an automated message. Please do not reply to this email.</p>
        </div>
    </div>
</body>
</html>