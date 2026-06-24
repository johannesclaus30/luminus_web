<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome to LumiNUs Admin</title>
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
        .email-header .icon-circle {
            width: 64px;
            height: 64px;
            background: rgba(255,255,255,0.15);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 16px;
            font-size: 28px;
        }
        .email-header h1 {
            color: #FFFFFF;
            font-size: 24px;
            margin: 0 0 8px;
            font-weight: 600;
        }
        .email-header p {
            color: rgba(255,255,255,0.8);
            font-size: 15px;
            margin: 0;
        }
        .email-body {
            padding: 40px 30px;
            color: #374151;
            line-height: 1.7;
        }
        .email-body h2 {
            color: #32418C;
            font-size: 20px;
            margin: 0 0 12px;
        }
        .email-body p {
            margin: 0 0 20px;
            font-size: 16px;
        }
        .email-body strong {
            color: #1F2937;
        }
        
        /* Credentials Box */
        .credentials-box {
            background: #F0F4FF;
            border: 2px solid #32418C;
            border-radius: 12px;
            padding: 24px;
            margin: 24px 0;
        }
        .credentials-box .label {
            font-size: 13px;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: #6B7280;
            font-weight: 600;
            margin-bottom: 4px;
        }
        .credentials-box .value {
            font-size: 18px;
            font-weight: 600;
            color: #1F2937;
            margin-bottom: 16px;
            word-break: break-all;
        }
        .credentials-box .value.password {
            font-family: 'SF Mono', 'Cascadia Code', 'Consolas', monospace;
            background: #FFFFFF;
            padding: 8px 14px;
            border-radius: 8px;
            display: inline-block;
            letter-spacing: 1px;
        }
        .credentials-box .divider {
            border-top: 1px dashed #C7D2FE;
            margin: 16px 0;
        }
        
        /* CTA Button */
        .cta-button {
            display: inline-block;
            background-color: #FBD117;
            color: #253069;
            text-decoration: none;
            padding: 14px 32px;
            border-radius: 10px;
            font-weight: 600;
            font-size: 16px;
            margin: 8px 0 24px;
            box-shadow: 0 4px 14px rgba(251, 209, 23, 0.3);
            text-align: center;
        }
        
        /* Info Box */
        .info-box {
            background: #FEF3C7;
            border-left: 4px solid #F59E0B;
            padding: 16px;
            border-radius: 0 8px 8px 0;
            margin: 24px 0;
            font-size: 14px;
            color: #92400E;
        }
        .info-box strong {
            color: #92400E;
        }
        
        /* Footer */
        .email-footer {
            background: #F9FAFB;
            padding: 24px 30px;
            text-align: center;
            border-top: 1px solid #E5E7EB;
        }
        .email-footer p {
            color: #9CA3AF;
            font-size: 13px;
            margin: 0 0 4px;
        }
        .email-footer .brand {
            font-weight: 600;
            color: #32418C;
        }
    </style>
</head>
<body style="padding: 40px 20px;">
    @php
        $fullName = trim(($admin->admin_first_name ?? '') . ' ' . ($admin->admin_middle_name ?? '') . ' ' . ($admin->admin_last_name ?? ''));
        $firstName = $admin->admin_first_name ?? 'there';
        $roleLabel = match($admin->admin_role) {
            'Executive Director' => 'Executive Director',
            'Academic Director' => 'Academic Director',
            'Coordinator' => 'Coordinator',
            'Assistant Coordinator' => 'Assistant Coordinator',
            default => 'Administrator',
        };
    @endphp

    <div class="email-wrapper">
        <!-- Header -->
        <div class="email-header">
            <div class="icon-circle">🔑</div>
            <h1>Welcome to the Team!</h1>
            <p>You've been invited to manage LumiNUs</p>
        </div>
        
        <!-- Body -->
        <div class="email-body">
            <h2>Hello, {{ $firstName }}!</h2>
            
            <p>
                <strong>{{ $fullName }}</strong>, you have been invited to join the 
                <strong>LumiNUs Admin Portal</strong> as an 
                <strong style="color: #32418C;">{{ $roleLabel }}</strong>.
            </p>
            
            <p>Below are your login credentials. Please keep them secure:</p>
            
            <!-- Credentials -->
            <div class="credentials-box">
                <div class="label">📧 Admin Email</div>
                <div class="value">{{ $admin->admin_email }}</div>
                
                <div class="divider"></div>
                
                <div class="label">🔒 Temporary Password</div>
                <div class="value password">{{ $temporaryPassword }}</div>
            </div>
            
            <!-- Login Button -->
            <div style="text-align: center;">
                <a href="{{ url('/admin/login') }}" class="cta-button">
                    Sign In to Admin Portal →
                </a>
            </div>
            
            <!-- Warning -->
            <div class="info-box">
                <strong>🔐 Important:</strong> For security, please change your password immediately after your first login. 
                You can do this in <em>Settings → Security</em>.
            </div>
            
            <p style="font-size: 14px; color: #6B7280;">
                This invitation was sent by a LumiNUs system administrator. If you believe this was sent in error, 
                please contact the system administrator immediately.
            </p>
        </div>
        
        <!-- Footer -->
        <div class="email-footer">
            <p class="brand">LumiNUs</p>
            <p>National University • Alumni Management System</p>
            <p>© {{ date('Y') }} All rights reserved.</p>
        </div>
    </div>
</body>
</html>