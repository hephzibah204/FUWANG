<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>NIN Regular Slip - {{ $result->reference_id }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; color: #111; font-size: 12px; }
        .header { border-bottom: 2px solid #64748b; padding-bottom: 10px; margin-bottom: 18px; }
        .brand { font-weight: 800; color: #64748b; font-size: 18px; }
        .meta { font-size: 11px; color: #444; }
        .grid { display: table; width: 100%; border-collapse: collapse; }
        .row { display: table-row; }
        .cell { display: table-cell; padding: 6px 8px; border: 1px solid #e5e7eb; vertical-align: top; }
        .label { font-size: 10px; color: #6b7280; text-transform: uppercase; margin-bottom: 2px; }
        .value { font-size: 12px; color: #111; font-weight: 600; }
        .footer { margin-top: 20px; font-size: 10px; color: #6b7280; border-top: 1px solid #e5e7eb; padding-top: 8px; }
    </style>
</head>
<body>
    <div class="header">
        <div class="brand">{{ \App\Models\SystemSetting::get('site_name', 'G-Soft Verify') }}</div>
        <div class="meta">NIN Regular Slip • Ref: {{ $result->reference_id }} • {{ $result->created_at->format('Y-m-d H:i') }}</div>
    </div>
    <div class="grid">
        <div class="row">
            <div class="cell">
                <div class="label">First Name</div>
                <div class="value">{{ $result->response_data['firstname'] ?? '' }}</div>
            </div>
            <div class="cell">
                <div class="label">Last Name</div>
                <div class="value">{{ $result->response_data['lastname'] ?? $result->response_data['surname'] ?? '' }}</div>
            </div>
            <div class="cell">
                <div class="label">NIN</div>
                <div class="value">{{ $result->response_data['nin'] ?? '—' }}</div>
            </div>
        </div>
        <div class="row">
            <div class="cell">
                <div class="label">Gender</div>
                <div class="value">{{ strtoupper($result->response_data['gender'] ?? '') }}</div>
            </div>
            <div class="cell">
                <div class="label">Date of Birth</div>
                <div class="value">{{ $result->response_data['birthdate'] ?? $result->response_data['dob'] ?? '' }}</div>
            </div>
            <div class="cell">
                <div class="label">Phone</div>
                <div class="value">{{ $result->response_data['telephoneno'] ?? $result->response_data['phone'] ?? '' }}</div>
            </div>
        </div>
    </div>
    <div class="footer">
        Reference {{ $result->reference_id }} • Provider {{ $result->provider_name }}
    </div>
</body>
</html>
