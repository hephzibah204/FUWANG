<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>E2E Payment Harness</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" />
    <style>
        body { font-family: system-ui, -apple-system, Segoe UI, Roboto, sans-serif; background: #0b1020; color: #fff; padding: 24px; }
        .card { max-width: 560px; margin: 0 auto; background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.08); border-radius: 14px; padding: 18px; }
        label { display: block; font-size: 0.85rem; color: rgba(255,255,255,0.7); margin-bottom: 8px; }
        input { width: 100%; padding: 12px 14px; border-radius: 12px; border: 1px solid rgba(255,255,255,0.12); background: rgba(255,255,255,0.04); color: #fff; }
        button { width: 100%; margin-top: 14px; padding: 12px 14px; border-radius: 12px; border: 0; background: #3b82f6; color: #fff; font-weight: 700; cursor: pointer; }
    </style>
</head>
<body>
    <div class="card">
        <h1 style="font-size:1.1rem; margin:0 0 12px;">E2E Payment Harness</h1>
        <label>Amount</label>
        <input id="amount" type="number" value="1500" min="50">
        <button id="open" type="button">Open Payment Modal</button>
    </div>

    <script>
        window.authUserEmail = 'e2e.user@example.com';
        window.authUserName = 'E2E User';
    </script>

    <script src="https://js.paystack.co/v1/inline.js" defer></script>
    <script src="https://checkout.flutterwave.com/v3.js" defer></script>
    <script src="https://sdk.monnify.com/plugin/monnify.js" defer></script>
    <script src="/assets/nexus/js/payment-modal.js"></script>
    <script>
        document.getElementById('open').addEventListener('click', () => {
            const amount = document.getElementById('amount').value;
            window.openPayModal('Wallet Funding', amount, 'E2E Modal Test');
        });
    </script>
</body>
</html>

