@php
    $siteName = (string) \App\Models\SystemSetting::get('site_name', config('app.name'));
    $name = $user->fullname ?? $user->username ?? $user->email;
    $title = __('emails.login.title');
    $preheader = __('emails.login.preheader', ['app' => $siteName]);
@endphp

@extends('emails.layouts.base', ['title' => $title, 'preheader' => $preheader, 'unsubscribeUrl' => $unsubscribeUrl])

@section('content')
    <h1 style="margin:0 0 10px;font-size:18px;line-height:1.3;color:#ffffff;">{{ __('emails.login.heading', ['app' => $siteName]) }}</h1>
    <p style="margin:0 0 14px;font-size:14px;line-height:1.7;color:rgba(229,231,235,0.9);">{{ __('emails.login.body', ['name' => $name]) }}</p>

    <div style="margin:18px 0; padding:14px; border:1px solid rgba(255,255,255,0.08); border-radius:14px; background: rgba(255,255,255,0.03);">
        <div style="font-size:12px;color:rgba(229,231,235,0.7); margin-bottom:10px;">{{ __('emails.login.details') }}</div>
        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="font-size:13px; color:rgba(229,231,235,0.92);">
            <tr>
                <td style="padding:6px 0;color:rgba(229,231,235,0.7);">{{ __('emails.login.time') }}</td>
                <td style="padding:6px 0;text-align:right;">{{ $loginAtIso }}</td>
            </tr>
            <tr>
                <td style="padding:6px 0;color:rgba(229,231,235,0.7);">{{ __('emails.login.ip') }}</td>
                <td style="padding:6px 0;text-align:right;">{{ $loginIp }}</td>
            </tr>
            @if(!empty($userAgent))
            <tr>
                <td style="padding:6px 0;color:rgba(229,231,235,0.7);">{{ __('emails.login.device') }}</td>
                <td style="padding:6px 0;text-align:right;">{{ \Illuminate\Support\Str::limit($userAgent, 64) }}</td>
            </tr>
            @endif
        </table>
    </div>

    <p style="margin:0 0 14px;font-size:13px;line-height:1.7;color:rgba(229,231,235,0.8);">{{ __('emails.login.security_tip') }}</p>

    <div style="margin-top:16px;font-size:12px;line-height:1.6;color:rgba(229,231,235,0.7);">
        {{ __('emails.footer.ref', ['id' => $emailLogId]) }}
    </div>
@endsection

