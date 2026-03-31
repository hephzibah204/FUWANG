@php
    $siteName = (string) \App\Models\SystemSetting::get('site_name', config('app.name'));
    $name = $user->fullname ?? $user->username ?? $user->email;
    $title = __('emails.welcome.title');
    $preheader = __('emails.welcome.preheader', ['app' => $siteName]);
@endphp

@extends('emails.layouts.base', ['title' => $title, 'preheader' => $preheader, 'unsubscribeUrl' => $unsubscribeUrl])

@section('content')
    <h1 style="margin:0 0 10px;font-size:20px;line-height:1.3;color:#ffffff;">{{ __('emails.welcome.heading', ['name' => $name]) }}</h1>
    <p style="margin:0 0 14px;font-size:14px;line-height:1.7;color:rgba(229,231,235,0.9);">{{ __('emails.welcome.body', ['app' => $siteName]) }}</p>

    <div style="margin:18px 0; padding:14px; border:1px solid rgba(255,255,255,0.08); border-radius:14px; background: rgba(255,255,255,0.03);">
        <div style="font-size:12px;color:rgba(229,231,235,0.7); margin-bottom:8px;">{{ __('emails.welcome.quick_start') }}</div>
        <ul style="margin:0; padding-left:18px; font-size:13px; line-height:1.8; color:rgba(229,231,235,0.9);">
            <li>{{ __('emails.welcome.step_fund') }}</li>
            <li>{{ __('emails.welcome.step_verify') }}</li>
            <li>{{ __('emails.welcome.step_history') }}</li>
        </ul>
    </div>

    <div style="margin-top:18px;">
        <a href="{{ url('/dashboard') }}" style="display:inline-block;background: linear-gradient(135deg, #3b82f6, #2563eb); color:#fff; text-decoration:none; padding:12px 16px; border-radius:12px; font-weight:700; font-size:14px;">{{ __('emails.welcome.cta') }}</a>
    </div>

    <div style="margin-top:16px;font-size:12px;line-height:1.6;color:rgba(229,231,235,0.7);">
        {{ __('emails.footer.ref', ['id' => $emailLogId]) }}
    </div>
@endsection

