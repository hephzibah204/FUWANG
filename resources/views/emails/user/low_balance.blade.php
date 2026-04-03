@php
    $siteName = (string) \App\Models\SystemSetting::get('site_name', config('app.name'));
    $name = $user->fullname ?? $user->username ?? $user->email;
    $title = "Low Balance Alert";
    $preheader = "Your account balance is low. Please fund your wallet to avoid service interruption.";
@endphp

@extends('emails.layouts.base', ['title' => $title, 'preheader' => $preheader, 'unsubscribeUrl' => $unsubscribeUrl])

@section('content')
    <h1 style="margin:0 0 10px;font-size:20px;line-height:1.3;color:#ffffff;">Hello {{ $name }},</h1>
    <p style="margin:0 0 14px;font-size:14px;line-height:1.7;color:rgba(229,231,235,0.9);">This is an automated notification to let you know that your account balance on <strong>{{ $siteName }}</strong> has fallen below the recommended threshold.</p>

    <div style="margin:18px 0; padding:20px; border:1px solid rgba(239, 68, 68, 0.2); border-radius:14px; background: rgba(239, 68, 68, 0.05); text-align: center;">
        <div style="font-size:12px;color:rgba(229,231,235,0.7); margin-bottom:4px; text-transform: uppercase; letter-spacing: 1px;">Current Balance</div>
        <div style="font-size:32px; font-weight: 800; color: #ef4444; margin-bottom: 4px;">₦{{ number_format($balance, 2) }}</div>
        <div style="font-size:13px; color:rgba(229,231,235,0.6);">Threshold: ₦{{ number_format($threshold, 2) }}</div>
    </div>

    <p style="margin:0 0 14px;font-size:14px;line-height:1.7;color:rgba(229,231,235,0.9);">To ensure uninterrupted access to our services, please top up your wallet as soon as possible.</p>

    <div style="margin-top:24px; text-align: center;">
        <a href="{{ url('/dashboard') }}" style="display:inline-block;background: linear-gradient(135deg, #3b82f6, #2563eb); color:#fff; text-decoration:none; padding:14px 24px; border-radius:12px; font-weight:700; font-size:15px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);">Fund My Wallet Now</a>
    </div>

    <p style="margin:24px 0 0;font-size:12px;line-height:1.6;color:rgba(229,231,235,0.5); font-style: italic;">If you have already funded your account, please ignore this message. This alert is triggered when your balance drops below the threshold during a transaction.</p>

    <div style="margin-top:16px;font-size:11px;line-height:1.6;color:rgba(229,231,235,0.4); border-top: 1px solid rgba(255,255,255,0.05); pt: 10px;">
        Reference ID: {{ $emailLogId }}
    </div>
@endsection
