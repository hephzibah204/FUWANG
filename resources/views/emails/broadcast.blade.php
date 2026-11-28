<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $subject }}</title>
</head>
<body style="margin: 0; padding: 24px; background: #f1f5f9;">
    <div style="font-family: Arial, sans-serif; background: #0f172a; color: #f8fafc; padding: 30px; border-radius: 12px; max-width: 600px; margin: 0 auto;">
        <div style="border-bottom: 1px solid rgba(255,255,255,0.1); padding-bottom: 15px; margin-bottom: 20px;">
            <span style="background: #2dc4af; color: #0f172a; padding: 4px 10px; border-radius: 20px; font-size: 11px; font-weight: bold; text-transform: uppercase;">Fuwa.NG Announcement</span>
            <h2 style="color: #ffffff; margin: 12px 0 0; font-size: 20px;">{{ $subject }}</h2>
        </div>

        <div style="color: #e2e8f0; font-size: 14px; line-height: 1.65;">
            {!! $contentHtml !!}
        </div>

        <div style="text-align: center; margin: 28px 0 6px;">
            <a href="{{ url('/dashboard') }}" style="background: #2dc4af; color: #0f172a; padding: 11px 26px; text-decoration: none; border-radius: 25px; font-weight: bold; font-size: 13px; display: inline-block;">View Dashboard</a>
        </div>

        <hr style="border: none; border-top: 1px solid rgba(255,255,255,0.1); margin: 25px 0;">
        <p style="color: #64748b; font-size: 12px; text-align: center; margin: 0;">Thank you for using Fuwa.NG.</p>
    </div>
</body>
</html>
