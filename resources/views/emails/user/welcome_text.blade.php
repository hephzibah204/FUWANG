@php
    $siteName = (string) \App\Models\SystemSetting::get('site_name', config('app.name'));
    $name = $user->fullname ?? $user->username ?? $user->email;
@endphp

{{ __('emails.welcome.heading', ['name' => $name]) }}

{{ __('emails.welcome.body', ['app' => $siteName]) }}

{{ __('emails.welcome.quick_start') }}
- {{ __('emails.welcome.step_fund') }}
- {{ __('emails.welcome.step_verify') }}
- {{ __('emails.welcome.step_history') }}

{{ __('emails.welcome.cta') }}: {{ url('/dashboard') }}

{{ __('emails.footer.unsubscribe') }}: {{ $unsubscribeUrl }}
{{ __('emails.footer.privacy') }}: {{ url('/privacy') }}
{{ __('emails.footer.ref', ['id' => $emailLogId]) }}

