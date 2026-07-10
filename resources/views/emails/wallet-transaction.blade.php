@php
    $site = \App\Models\SystemSetting::get('site_name', config('app.name'));
    $delta = (float) $amountDelta;
    $direction = $delta >= 0 ? 'credited' : 'debited';
@endphp
<p>Hello {{ $user->fullname ?? 'there' }},</p>
<p>
    <strong>₦{{ number_format(abs($delta), 2) }}</strong> has been <strong>{{ $direction }}</strong> {{ $delta >= 0 ? 'to' : 'from' }} your {{ $site }} wallet.
</p>

<p><strong>Transaction type:</strong> {{ e((string) $tx->order_type) }}</p>
<p><strong>Status:</strong> {{ strtoupper(e((string) $tx->status)) }}</p>
@if($tx->transaction_id)
    <p><strong>Reference:</strong> <code>{{ e((string) $tx->transaction_id) }}</code></p>
@endif
<p><strong>Balance before:</strong> ₦{{ number_format((float) $tx->balance_before, 2) }}</p>
<p><strong>Balance after:</strong> ₦{{ number_format((float) $tx->balance_after, 2) }}</p>
<p><strong>Date:</strong> {{ $tx->created_at?->format('M d, Y H:i') }}</p>

<p>If you did not authorize this transaction, contact support immediately.</p>
<p style="color:#666;font-size:12px;">This is an automated notification.</p>

