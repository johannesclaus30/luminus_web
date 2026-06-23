<!DOCTYPE html>
<html>
<head>
    <title>Test Email</title>
</head>
<body style="font-family: 'Poppins', Arial, sans-serif; line-height: 1.6; color: #333; background-color: #f9fafb; padding: 20px;">
    <div style="max-width: 600px; margin: 0 auto; background: #ffffff; padding: 30px; border-radius: 12px; box-shadow: 0 4px 6px rgba(0,0,0,0.05);">
        <h2 style="color: #2563eb; margin-top: 0;">Hello, {{ $alumnus->first_name }}!</h2>
        <p>This is a test email sent from the <strong>LumiNUs Admin Panel</strong>.</p>
        <p>If you are receiving this email, it means the mailer configuration is working perfectly!</p>
        
        <hr style="border: 0; border-top: 1px solid #eee; margin: 20px 0;">
        
        <p style="font-size: 0.9em; color: #64748b;">
            <strong>Sent to:</strong> {{ $alumnus->email }}<br>
            <strong>Time:</strong> {{ now()->format('F j, Y, g:i a') }}
        </p>
    </div>
</body>
</html>