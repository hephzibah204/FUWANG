<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: 'Helvetica', 'Arial', sans-serif; color: #333; margin: 0; padding: 20px; }
        .header { border-bottom: 2px solid #4f46e5; padding-bottom: 10px; margin-bottom: 20px; }
        .shipment-id { font-size: 24px; font-weight: bold; color: #4f46e5; }
        .row { display: flex; margin-bottom: 20px; }
        .col { flex: 1; padding: 10px; border: 1px solid #eee; border-radius: 8px; margin-right: 10px; }
        .col:last-child { margin-right: 0; }
        .label { font-size: 10px; color: #666; text-transform: uppercase; margin-bottom: 5px; }
        .value { font-size: 14px; font-weight: bold; }
        .barcode { background: #000; height: 60px; width: 100%; margin: 20px 0; display: flex; align-items: center; justify-content: center; color: #fff; font-family: monospace; }
        .footer { margin-top: 30px; font-size: 10px; text-align: center; color: #999; border-top: 1px solid #eee; pt: 10px; }
    </style>
</head>
<body>
    <div class="header">
        <div style="float: right; text-align: right;">
            <div style="font-size: 18px; font-weight: bold;">Fuwa.NG Logistics</div>
            <div>Official Waybill</div>
        </div>
        <div class="shipment-id">{{ $reference }}</div>
    </div>

    <div style="display: table; width: 100%; border-collapse: separate; border-spacing: 10px;">
        <div style="display: table-row;">
            <div style="display: table-cell; border: 1px solid #eee; padding: 15px; border-radius: 8px; width: 50%;">
                <div class="label">Sender Details</div>
                <div class="value">{{ $sender_name }}</div>
                <div style="font-size: 12px; margin-top: 5px;">{{ $sender_address }}</div>
            </div>
            <div style="display: table-cell; border: 1px solid #eee; padding: 15px; border-radius: 8px; width: 50%;">
                <div class="label">Recipient Details</div>
                <div class="value">{{ $recipient_name }}</div>
                <div style="font-size: 12px; margin-top: 5px;">{{ $recipient_address }}</div>
            </div>
        </div>
    </div>

    <div style="display: table; width: 100%; border-collapse: separate; border-spacing: 10px; margin-top: 20px;">
        <div style="display: table-row;">
            <div style="display: table-cell; border: 1px solid #eee; padding: 15px; border-radius: 8px;">
                <div class="label">Description</div>
                <div class="value">{{ $description }}</div>
            </div>
            <div style="display: table-cell; border: 1px solid #eee; padding: 15px; border-radius: 8px;">
                <div class="label">Weight</div>
                <div class="value">{{ $weight }} KG</div>
            </div>
            <div style="display: table-cell; border: 1px solid #eee; padding: 15px; border-radius: 8px;">
                <div class="label">Service</div>
                <div class="value" style="text-transform: uppercase;">{{ $delivery_type }}</div>
            </div>
        </div>
    </div>

    <div class="barcode">
        * {{ $reference }} *
    </div>

    <div style="text-align: center; margin-top: 20px;">
        <div class="label">Date Created</div>
        <div class="value">{{ $date }}</div>
    </div>

    <div class="footer">
        This document serves as an official proof of shipment. Please present this waybill at the point of collection or delivery.
        Fuwa.NG Logistics &copy; {{ date('Y') }}
    </div>
</body>
</html>
