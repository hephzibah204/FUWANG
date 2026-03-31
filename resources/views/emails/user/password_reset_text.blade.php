@php
    $siteName = (string) \App\Models\SystemSetting::get('site_name', config('app.name'));
    $name = $user->fullname ?? $user->username ?? $user->email;
@endphp

{{ __('emails.password_reset.heading') }}

{{ __('emails.password_reset.body', ['name' => $name, 'app' => $siteName]) }}

{{ __('emails.password_reset.cta') }}: {{ $resetUrl }}
{{ __('emails.password_reset.expire', ['minutes' => $expireMinutes]) }}

{{ __('emails.password_reset.ignore') }}

{{ __('emails.footer.privacy') }}: {{ url('/privacy') }}
{{ __('emails.footer.ref', ['id' => $emailLogId]) }}

