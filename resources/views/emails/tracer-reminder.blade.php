<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tracer Survey Reminder</title>
</head>
<body style="font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; line-height: 1.6; color: #333; margin: 0; padding: 0; background-color: #f4f7fa;">
    <div style="max-width: 600px; margin: 0 auto; background-color: #ffffff; border-radius: 12px; overflow: hidden; box-shadow: 0 2px 12px rgba(0,0,0,0.1);">
        <!-- Header -->
        <div style="background: linear-gradient(135deg, #1f2b67 0%, #32418C 100%); padding: 30px 40px; text-align: center;">
            <h1 style="color: #ffffff; margin: 0; font-size: 24px; font-weight: 600;">📋 Alumni Tracer Reminder</h1>
        </div>
        
        <!-- Body -->
        <div style="padding: 30px 40px;">
            <p style="font-size: 16px; margin-bottom: 20px;">Dear <strong>{{ $alumniName }}</strong>,</p>
            
            <p style="font-size: 15px; margin-bottom: 20px; color: #555;">
                We noticed that you haven't completed the <strong>{{ $formTitle }}</strong> yet. Your participation is vital in helping us improve our programs and track the success of our alumni community.
            </p>
            
            <p style="font-size: 15px; margin-bottom: 20px; color: #555;">
                The survey takes only about <strong>10-15 minutes</strong> to complete, and your responses will greatly contribute to enhancing our academic offerings and alumni services.
            </p>
            
            <!-- CTA Button -->
            <div style="text-align: center; margin: 30px 0;">
                <a href="{{ $tracerUrl }}" style="background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%); color: #ffffff; padding: 14px 36px; border-radius: 8px; text-decoration: none; font-size: 16px; font-weight: 600; display: inline-block; box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);">
                    Complete Survey Now →
                </a>
            </div>
            
            <p style="font-size: 14px; color: #777; margin-bottom: 10px;">
                If you have already completed the survey, please disregard this message. We sincerely thank you for your participation!
            </p>
        </div>
        
        <!-- Footer -->
        <div style="background-color: #f8f9fc; padding: 20px 40px; border-top: 1px solid #e5e7eb;">
            <p style="font-size: 13px; color: #888; margin: 0; text-align: center;">
                This email was sent by the LumiNUs Alumni Office. If you have any questions, please contact our support team.
            </p>
        </div>
    </div>
</body>
</html>