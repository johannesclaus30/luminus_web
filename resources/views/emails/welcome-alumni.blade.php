<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome to LumiNUs!</title>
    <!-- Importing Poppins Font -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
    
    <style>
        body { 
            font-family: 'Poppins', 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; 
            background-color: #f4f6f9; 
            margin: 0; 
            padding: 40px 20px; 
        }
        .container { 
            max-width: 600px; 
            margin: 0 auto; 
            background: #ffffff; 
            border-radius: 12px; 
            overflow: hidden; 
            box-shadow: 0 4px 15px rgba(50, 65, 140, 0.1); 
        }
        /* NU Blue Header with THICKER Yellow Bottom Accent */
        .header { 
            background-color: #32418c; 
            padding: 40px 24px; 
            text-align: center;
            border-bottom: 8px solid #fbd117; /* Thickened yellow border */
        } 
        .header img {
            max-height: 85px; /* Slightly larger since it's the standalone star */
            width: auto;
            display: inline-block;
        }
        .content { 
            padding: 40px 30px; 
            color: #333333; 
            line-height: 1.6; 
        }
        /* Welcome Text Title Moved Outside Header */
        .welcome-title {
            color: #32418c;
            font-size: 26px;
            font-weight: 700;
            margin-top: 0;
            margin-bottom: 25px;
        }
        .content h2 { 
            color: #32418c; 
            margin-top: 0;
            font-size: 20px;
            font-weight: 600;
        }
        /* Highlighted Credentials Box */
        .credentials-box { 
            background-color: #f8fafc; 
            border-left: 4px solid #32418c;
            padding: 20px; 
            border-radius: 6px; 
            font-size: 15px; 
            margin: 25px 0; 
        }
        .credentials-box table {
            width: 100%;
        }
        .credentials-box td {
            padding: 4px 0;
            vertical-align: top;
        }
        .label {
            color: #32418c;
            font-weight: 600;
            width: 150px;
        }
        .value {
            color: #4a5568;
            font-family: monospace;
            font-size: 15px;
            font-weight: bold;
        }
        /* Brand Button */
        .btn-container {
            text-align: center;
            margin: 30px 0;
        }
        .btn { 
            display: inline-block; 
            background-color: #32418c; 
            color: #ffffff !important; 
            text-decoration: none; 
            padding: 14px 32px; 
            border-radius: 6px; 
            font-weight: 600; 
            font-size: 16px;
            letter-spacing: 0.5px;
            box-shadow: 0 4px 6px rgba(50, 65, 140, 0.2);
        }
        .footer { 
            padding: 24px; 
            background-color: #f8fafc; 
            text-align: center; 
            font-size: 13px; 
            color: #718096;
            border-top: 1px solid #e2e8f0;
        }
        .footer strong {
            color: #32418c;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header: ONLY the Logo lives here -->
        <div class="header">
            <img src="{{ isset($message) ? $message->embed(public_path('assets/Email_LogoHeader.png')) : asset('assets/Email_LogoHeader.png') }}" alt="LumiNUs National University Lipa">
        </div>
        
        <!-- Body Content -->
        <div class="content">
            <!-- Welcome title moved outside the header -->
            <h1 class="welcome-title">Welcome to LumiNUs!</h1>

            <h2>Hello {{ $alumni->first_name }},</h2>
            <p>Congratulations! Your official <strong>LumiNUs Alumni</strong> account has been successfully created. You are now officially connected to the National University Lipa alumni network.</p>
            
            <p>Here are your temporary login credentials:</p>
            
            <div class="credentials-box">
                <table>
                    <tr>
                        <td class="label">Login Email:</td>
                        <td class="value">{{ $alumni->email }}</td>
                    </tr>
                    <tr>
                        <td class="label">Temporary Password:</td>
                        <td class="value">password123</td>
                    </tr>
                </table>
            </div>
            
            <p>Please use these credentials to log in to the portal. For your account security, we highly recommend changing your password immediately after your first login.</p>
            
            <div class="btn-container">
                <a href="{{ url('/login') }}" class="btn">Log in to LumiNUs</a>
            </div>
            
            <p style="margin-top: 35px; border-top: 1px solid #edf2f7; padding-top: 20px;">
                If you have any questions or require assistance setting up your profile, please reach out to the Alumni Affairs Office.
            </p>
            
            <p style="margin-bottom: 0;">
                <br>Warm regards,<br><br>
                <strong>LumiNUs</strong><br>
                <span style="color: #32418c; font-weight: 600;">NU Lipa</span>
            </p>
        </div>
        
        <!-- Footer -->
        <div class="footer">
            &copy; {{ date('Y') }} <strong>NU Lipa Alumni Affairs Office</strong>. All rights reserved.
        </div>
    </div>
</body>
</html>