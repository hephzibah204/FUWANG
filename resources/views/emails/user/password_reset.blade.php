@php
    $siteName = (string) \App\Models\SystemSetting::get('site_name', config('app.name'));
    $name = $user->fullname ?? $user->username ?? $user->email;
@endphp

@extends('emails.layouts.base', ['title' => $title, 'preheader' => $preheader])

@section('content')
    <h1 style="margin:0 0 10px;font-size:20px;line-height:1.3;color:#ffffff;">{{ __('emails.password_reset.heading') }}</h1>
    <p style="margin:0 0 14px;font-size:14px;line-height:1.7;color:rgba(229,231,235,0.9);">{{ __('emails.password_reset.body', ['name' => $name, 'app' => $siteName]) }}</p>

    <div style="margin:18px 0;">
        <a href="{{ $resetUrl }}" style="display:inline-block;background: linear-gradient(135deg, #3b82f6, #2563eb); color:#fff; text-decoration:none; padding:12px 16px; border-radius:12px; font-weight:700; font-size:14px;">{{ __('emails.password_reset.cta') }}</a>
    </div>

    <p style="margin:0 0 14px;font-size:13px;line-height:1.7;color:rgba(229,231,235,0.8);">{{ __('emails.password_reset.expire', ['minutes' => $expireMinutes]) }}</p>
    <p style="margin:0 0 14px;font-size:13px;line-height:1.7;color:rgba(229,231,235,0.8);">{{ __('emails.password_reset.ignore') }}</p>

    <div style="margin-top:16px;font-size:12px;line-height:1.6;color:rgba(229,231,235,0.7);">
        {{ __('emails.footer.ref', ['id' => $emailLogId]) }}
    </div>
@endsection

