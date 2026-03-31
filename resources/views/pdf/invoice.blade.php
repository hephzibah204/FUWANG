<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: 'Helvetica', 'Arial', sans-serif; color: #333; margin: 0; padding: 40px; }
        .header { display: table; width: 100%; margin-bottom: 40px; }
        .logo { display: table-cell; font-size: 28px; font-weight: 800; color: #d97706; }
        .invoice-info { display: table-cell; text-align: right; }
        .info-label { color: #999; font-size: 12px; text-transform: uppercase; }
        .info-value { font-weight: bold; margin-bottom: 5px; }
        
        .addresses { display: table; width: 100%; margin-bottom: 40px; }
        .address-col { display: table-cell; width: 50%; }
        
        .table { width: 100%; border-collapse: collapse; margin-bottom: 30px; }
        .table th { background: #f8fafc; text-align: left; padding: 12px; border-bottom: 2px solid #eee; font-size: 12px; color: #666; }
        .table td { padding: 12px; border-bottom: 1px solid #eee; font-size: 14px; }
        
        .totals { float: right; width: 300px; }
        .total-row { display: table; width: 100%; margin-bottom: 10px; }
        .total-label { display: table-cell; text-align: right; padding-right: 20px; color: #666; }
        .total-value { display: table-cell; text-align: right; font-weight: bold; width: 100px; }
        .grand-total { border-top: 2px solid #d97706; padding-top: 10px; margin-top: 10px; font-size: 18px; color: #d97706; }
        
        .footer { margin-top: 100px; border-top: 1px solid #eee; padding-top: 20px; font-size: 12px; color: #999; text-align: center; }
    </style>
</head>
<body>
    <div class="header">
        <div class="logo">NEXUS</div>
        <div class="invoice-info">
            <div class="info-label">Invoice Number</div>
            <div class="info-value">{{ $invoice_number }}</div>
            <div class="info-label">Date Issued</div>
            <div class="info-value">{{ $date }}</div>
            <div class="info-label">Due Date</div>
            <div class="info-value" style="color: #ef4444;">{{ $due_date }}</div>
        </div>
    </div>

    <div class="addresses">
        <div class="address-col">
            <div class="info-label">From</div>
            <div style="font-weight: bold;">{{ $sender_name }}</div>
            <div style="font-size: 12px;">{{ $sender_email }}</div>
        </div>
        <div class="address-col">
            <div class="info-label">Bill To</div>
            <div style="font-weight: bold;">{{ $client_name }}</div>
            <div style="font-size: 12px;">{{ $client_email }}</div>
        </div>
    </div>

    <table class="table">
        <thead>
            <tr>
                <th>Description</th>
                <th style="text-align: center;">Qty</th>
                <th style="text-align: right;">Price</th>
                <th style="text-align: right;">Amount</th>
            </tr>
        </thead>
        <tbody>
            @foreach($items as $item)
            <tr>
                <td>{{ $item['desc'] }}</td>
                <td style="text-align: center;">{{ $item['qty'] }}</td>
                <td style="text-align: right;">₦{{ number_format($item['price'], 2) }}</td>
                <td style="text-align: right;">₦{{ number_format($item['qty'] * $item['price'], 2) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="totals">
        <div class="total-row">
            <div class="total-label">Subtotal</div>
            <div class="total-value">₦{{ number_format($subtotal, 2) }}</div>
        </div>
        <div class="total-row">
            <div class="total-label">VAT (7.5%)</div>
            <div class="total-value">₦{{ number_format($tax, 2) }}</div>
        </div>
        <div class="total-row grand-total">
            <div class="total-label">Total</div>
            <div class="total-value">₦{{ number_format($total, 2) }}</div>
        </div>
    </div>

    <div style="clear: both;"></div>

    <div class="footer">
        Please make payments before the due date to avoid service interruption.
        Thank you for your business!
        Fuwa.NG Billing &copy; {{ date('Y') }}
    </div>
</body>
</html>
