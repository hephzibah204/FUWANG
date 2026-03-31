<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name') }}</title>
</head>
<body style="margin:0;padding:0;background:#0b1220;">
    <div style="max-width:640px;margin:0 auto;padding:24px;">
        <div style="background:#0f172a;border:1px solid rgba(255,255,255,0.08);border-radius:16px;padding:24px;">
            <div style="color:#e2e8f0;font-family:Arial,Helvetica,sans-serif;font-size:14px;line-height:1.6;">
                {!! $html !!}
            </div>
        </div>
        <div style="color:#94a3b8;font-family:Arial,Helvetica,sans-serif;font-size:12px;margin-top:14px;text-align:center;">
            {{ \App\Models\SystemSetting::get('site_name', config('app.name')) }}
        </div>
    </div>
</body>
</html>
