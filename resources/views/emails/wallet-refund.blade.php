@php
    $site = \App\Models\SystemSetting::get('site_name', config('app.name'));
@endphp
<p>Hello {{ $user->fullname ?? 'there' }},</p>
<p><strong>₦{{ number_format($amount, 2) }}</strong> has been credited to your {{ $site }} wallet.</p>
<p><strong>Details:</strong> {{ e($reasonSummary) }}</p>
<p><strong>Reference:</strong> <code>{{ e($referenceId) }}</code></p>
<p>If you did not expect this message, contact support.</p>
<p style="color:#666;font-size:12px;">This is an automated notification.</p>
