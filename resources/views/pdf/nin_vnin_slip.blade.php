<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>vNIN Slip - {{ $result->reference_id }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; color: #0f172a; font-size: 12px; }
        .header { border-bottom: 2px solid #7c3aed; padding-bottom: 10px; margin-bottom: 18px; }
        .brand { font-weight: 900; color: #7c3aed; font-size: 18px; }
        .meta { font-size: 11px; color: #475569; }
        .grid { display: table; width: 100%; border-collapse: collapse; }
        .row { display: table-row; }
        .cell { display: table-cell; padding: 6px 8px; border: 1px solid #e2e8f0; vertical-align: top; }
        .label { font-size: 10px; color: #64748b; text-transform: uppercase; margin-bottom: 2px; }
        .value { font-size: 12px; color: #0f172a; font-weight: 700; }
        .footer { margin-top: 20px; font-size: 10px; color: #64748b; border-top: 1px solid #e2e8f0; padding-top: 8px; }
    </style>
</head>
<body>
    <div class="header">
        <div class="brand">{{ \App\Models\SystemSetting::get('site_name', 'G-Soft Verify') }}</div>
        <div class="meta">vNIN Slip • Ref: {{ $result->reference_id }} • {{ $result->created_at->format('Y-m-d H:i') }}</div>
    </div>
    <div class="grid">
        <div class="row">
            <div class="cell">
                <div class="label">Name</div>
                <div class="value">
                    {{ trim(implode(' ', array_filter([
                        $result->response_data['firstname'] ?? null,
                        $result->response_data['middlename'] ?? null,
                        $result->response_data['lastname'] ?? ($result->response_data['surname'] ?? null),
                    ]))) }}
                </div>
            </div>
            <div class="cell">
                <div class="label">NIN</div>
                <div class="value">{{ $result->response_data['nin'] ?? '—' }}</div>
            </div>
            <div class="cell">
                <div class="label">vNIN</div>
                <div class="value">{{ $result->response_data['vnin'] ?? $result->response_data['vNin'] ?? '—' }}</div>
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
