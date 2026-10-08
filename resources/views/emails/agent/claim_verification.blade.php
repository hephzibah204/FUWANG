@extends('emails.layouts.base', [
    'title' => 'Agent Profile Claim Verification — Fuwa.NG',
    'preheader' => 'Your 6-digit OTP code to claim your pre-approved agent profile is ' . $otp
])

@section('content')
    <div style="color: #f8fafc;">
        <div style="border-bottom: 1px solid rgba(255, 255, 255, 0.1); padding-bottom: 15px; margin-bottom: 20px;">
            <span style="background: rgba(245, 158, 11, 0.2); color: #fbbf24; border: 1px solid rgba(245, 158, 11, 0.4); padding: 4px 12px; border-radius: 20px; font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; display: inline-block;">
                Station License Verification
            </span>
            <h2 style="color: #ffffff; margin: 12px 0 6px; font-size: 22px; font-weight: 700;">
                Verify Your Pre-Approved Profile
            </h2>
        </div>

        <p style="color: #cbd5e1; font-size: 15px; line-height: 1.6; margin: 0 0 16px;">
            Hello <strong>{{ $agent->full_name }}</strong>,
        </p>

        <p style="color: #94a3b8; font-size: 14px; line-height: 1.6; margin: 0 0 20px;">
            A request was made to claim pre-approved station license <strong style="color: #fbbf24;">{{ $agent->agent_code }}</strong> on the Fuwa.NG Identity & Enrollment Platform. Use the one-time verification code below to authorize this claim:
        </p>

        <!-- OTP Highlight Card -->
        <div style="background: rgba(245, 158, 11, 0.08); border: 1px solid rgba(245, 158, 11, 0.35); padding: 25px 20px; border-radius: 12px; text-align: center; margin: 25px 0;">
            <div style="font-size: 12px; color: #fbbf24; text-transform: uppercase; letter-spacing: 1.5px; font-weight: 700; margin-bottom: 8px;">
                Your 6-Digit Claim Verification Code
            </div>
            <div style="font-size: 38px; font-weight: 800; letter-spacing: 8px; color: #ffffff; font-family: 'Courier New', Courier, monospace; text-shadow: 0 0 10px rgba(251, 191, 36, 0.3);">
                {{ $otp }}
            </div>
            <div style="margin-top: 10px; font-size: 12px; color: #94a3b8;">
                Valid for <strong style="color: #e2e8f0;">15 minutes</strong> from dispatch
            </div>
        </div>

        <!-- Security Notice -->
        <div style="background: rgba(255, 255, 255, 0.03); border: 1px solid rgba(255, 255, 255, 0.08); padding: 15px 18px; border-radius: 8px; margin: 20px 0; color: #94a3b8; font-size: 13px; line-height: 1.6;">
            <strong style="color: #cbd5e1;">Security Tip:</strong> Never share this code with anyone. Fuwa.NG representatives will never ask you for your verification code. If you did not initiate this registration, please contact our support desk immediately.
        </div>

        <p style="color: #64748b; font-size: 12px; text-align: center; margin: 25px 0 0;">
            NIMC Accredited Partner Platform &bull; Fuwa.NG Agency Network
        </p>
    </div>
@endsection
