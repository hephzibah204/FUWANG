@php
    $delta = (float) $amountDelta;
    $direction = $delta >= 0 ? 'credited' : 'debited';
@endphp
Hello {{ $user->fullname ?? 'there' }},

NGN {{ number_format(abs($delta), 2) }} has been {{ $direction }} {{ $delta >= 0 ? 'to' : 'from' }} your wallet.

Transaction type: {{ $tx->order_type }}
Status: {{ strtoupper((string) $tx->status) }}
@if($tx->transaction_id)
Reference: {{ $tx->transaction_id }}
@endif
Balance before: NGN {{ number_format((float) $tx->balance_before, 2) }}
Balance after: NGN {{ number_format((float) $tx->balance_after, 2) }}
Date: {{ $tx->created_at?->format('M d, Y H:i') }}

If you did not authorize this transaction, contact support immediately.

