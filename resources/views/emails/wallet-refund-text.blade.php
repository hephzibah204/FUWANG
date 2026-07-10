Hello {{ $user->fullname ?? 'there' }},

NGN {{ number_format($amount, 2) }} has been credited to your wallet.

Details: {{ $reasonSummary }}
Reference: {{ $referenceId }}

If you did not expect this message, contact support.
