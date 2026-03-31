@php
    $siteName = (string) \App\Models\SystemSetting::get('site_name', config('app.name'));
    $logoUrl = (string) \App\Models\SystemSetting::get('site_logo_url', '');
    $contactEmail = (string) \App\Models\SystemSetting::get('contact_email', config('mail.from.address'));
    $contactAddress = (string) \App\Models\SystemSetting::get('contact_address', '');
    $privacyUrl = url('/privacy');
    $homeUrl = url('/');
    $title = $title ?? $siteName;
    $preheader = $preheader ?? '';
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="x-apple-disable-message-reformatting">
    <title>{{ $title }}</title>
</head>
<body style="margin:0;padding:0;background:#0b1220;color:#e5e7eb;font-family:Arial,Helvetica,sans-serif;">
    <div style="display:none;max-height:0;overflow:hidden;opacity:0;color:transparent;">{{ $preheader }}</div>

    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#0b1220; padding: 24px 0;">
        <tr>
            <td align="center" style="padding: 0 12px;">
                <table role="presentation" width="600" cellpadding="0" cellspacing="0" style="width:100%;max-width:600px;background:#0f172a;border:1px solid rgba(255,255,255,0.08);border-radius:16px;overflow:hidden;">
                    <tr>
                        <td style="padding: 20px 22px; background: linear-gradient(135deg, rgba(59,130,246,0.18), rgba(139,92,246,0.12));">
                            <a href="{{ $homeUrl }}" style="text-decoration:none; display:flex; align-items:center; gap:10px;">
                                @if($logoUrl)
                                    <img src="{{ $logoUrl }}" alt="{{ $siteName }}" width="36" height="36" style="display:block;border-radius:8px;">
                                @else
                                    <div style="width:36px;height:36px;border-radius:8px;background:rgba(255,255,255,0.12);"></div>
                                @endif
                                <span style="font-size:16px;font-weight:700;color:#ffffff;letter-spacing:0.5px;">{{ $siteName }}</span>
                            </a>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding: 22px;">
                            @yield('content')
                        </td>
                    </tr>

                    <tr>
                        <td style="padding: 18px 22px; border-top:1px solid rgba(255,255,255,0.06); background: rgba(255,255,255,0.02);">
                            <div style="font-size:12px;line-height:1.6;color:rgba(229,231,235,0.75);">
                                <div>{{ __('emails.footer.reason') }}</div>
                                @if(!empty($contactAddress))
                                    <div style="margin-top:6px;">{{ $contactAddress }}</div>
                                @endif
                                @if(!empty($contactEmail))
                                    <div style="margin-top:6px;">{{ __('emails.footer.support') }}: <a href="mailto:{{ $contactEmail }}" style="color:#93c5fd;">{{ $contactEmail }}</a></div>
                                @endif
                                <div style="margin-top:10px;">
                                    <a href="{{ $privacyUrl }}" style="color:#93c5fd;">{{ __('emails.footer.privacy') }}</a>
                                    @if(!empty($unsubscribeUrl))
                                        <span style="padding:0 8px;">·</span>
                                        <a href="{{ $unsubscribeUrl }}" style="color:#93c5fd;">{{ __('emails.footer.unsubscribe') }}</a>
                                    @endif
                                </div>
                            </div>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
