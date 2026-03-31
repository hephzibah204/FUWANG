@php
    $siteName = (string) \App\Models\SystemSetting::get('site_name', config('app.name'));
    $name = $user->fullname ?? $user->username ?? $user->email;
@endphp

{{ __('emails.login.heading', ['app' => $siteName]) }}

{{ __('emails.login.body', ['name' => $name]) }}

{{ __('emails.login.details') }}
- {{ __('emails.login.time') }}: {{ $loginAtIso }}
- {{ __('emails.login.ip') }}: {{ $loginIp }}
@if(!empty($userAgent))
- {{ __('emails.login.device') }}: {{ $userAgent }}
@endif

{{ __('emails.login.security_tip') }}

{{ __('emails.footer.unsubscribe') }}: {{ $unsubscribeUrl }}
{{ __('emails.footer.privacy') }}: {{ url('/privacy') }}
{{ __('emails.footer.ref', ['id' => $emailLogId]) }}

