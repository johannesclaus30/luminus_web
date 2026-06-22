<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #f4f7fa; margin: 0; padding: 20px; }
        .container { max-width: 600px; margin: 0 auto; background: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
        .header { background-color: #003366; color: #ffffff; padding: 24px; text-align: center; } /* NU Blue */
        .header h1 { margin: 0; font-size: 24px; }
        .content { padding: 24px; color: #333333; line-height: 1.6; }
        .content h2 { color: #003366; }
        .credentials-box { background-color: #f8f9fa; border: 1px dashed #003366; padding: 15px; border-radius: 4px; font-size: 16px; margin: 20px 0; }
        .credentials-box strong { color: #003366; display: block; margin-bottom: 8px; }
        .btn { display: inline-block; background-color: #003366; color: #ffffff !important; text-decoration: none; padding: 12px 24px; border-radius: 4px; font-weight: bold; margin-top: 20px; }
        .footer { padding: 16px 24px; background-color: #f1f1f1; text-align: center; font-size: 12px; color: #666; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Welcome to LumiNUs!</h1>
        </div>
        <div class="content">
            <h2>Hello {{ $alumni->first_name }},</h2>
            <p>Congratulations! Your official LumiNUs Alumni account has been successfully created. You are now part of the National University Lipa alumni network.</p>
            
            <p>Here are your temporary login credentials:</p>
            
            <div class="credentials-box">
                <strong>Login Email:</strong> {{ $alumni->email }}<br>
                <strong>Temporary Password:</strong> password123
            </div>
            
            <p>Please use these credentials to log in to the alumni portal. For security purposes, we highly recommend changing your password immediately after your first login.</p>
            
            <center><a href="{{ url('/login') }}" class="btn">Log in to LumiNUs</a></center>
            
            <p style="margin-top: 30px;">If you have any questions or need assistance, please don't hesitate to reach out to the alumni office.</p>
            
            <p>Warm regards,<br><strong>The LumiNUs Admin Team</strong><br>National University Lipa</p>
        </div>
        <div class="footer">
            &copy; {{ date('Y') }} National University Lipa. All rights reserved.
        </div>
    </div>
</body>
</html>