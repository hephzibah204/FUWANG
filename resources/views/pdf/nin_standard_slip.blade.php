<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>NIN Standard Slip - {{ $result->reference_id }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; color: #111; font-size: 12px; }
        .header { display: flex; justify-content: space-between; align-items: center; border-bottom: 2px solid #3b82f6; padding-bottom: 10px; margin-bottom: 18px; }
        .brand { font-weight: 800; color: #3b82f6; font-size: 18px; }
        .meta { font-size: 11px; color: #444; }
        .grid { display: table; width: 100%; border-collapse: collapse; }
        .row { display: table-row; }
        .cell { display: table-cell; padding: 6px 8px; border: 1px solid #e5e7eb; vertical-align: top; }
        .label { font-size: 10px; color: #6b7280; text-transform: uppercase; margin-bottom: 2px; }
        .value { font-size: 12px; color: #111; font-weight: 600; }
        .section { margin-bottom: 12px; }
        .section-title { font-weight: 700; font-size: 12px; color: #1f2937; border-bottom: 1px solid #e5e7eb; padding-bottom: 6px; margin-bottom: 8px; }
        .qr { width: 72px; height: 72px; border: 1px solid #e5e7eb; text-align: center; line-height: 72px; font-size: 10px; color: #6b7280; }
        .footer { margin-top: 20px; font-size: 10px; color: #6b7280; border-top: 1px solid #e5e7eb; padding-top: 8px; }
        .photo { width: 90px; height: 110px; object-fit: cover; border: 1px solid #e5e7eb; }
        .two-col { display: table; width: 100%; }
        .two-col .left, .two-col .right { display: table-cell; width: 50%; vertical-align: top; }
        .badge { display: inline-block; padding: 3px 8px; border-radius: 12px; background: #dcfce7; color: #166534; font-size: 10px; font-weight: 700; }
    </style>
    </head>
<body>
    <div class="header">
        <div>
            <div class="brand">{{ \App\Models\SystemSetting::get('site_name', 'G-Soft Verify') }}</div>
            <div class="meta">NIN Standard Slip • Ref: {{ $result->reference_id }} • {{ $result->created_at->format('Y-m-d H:i') }}</div>
        </div>
        <div class="qr">QR CODE</div>
    </div>
    <div class="two-col">
        <div class="left">
            <div class="section">
                <div class="section-title">Subject</div>
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
                            <div class="label">Gender</div>
                            <div class="value">{{ strtoupper($result->response_data['gender'] ?? '')) }}</div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="cell">
                            <div class="label">Date of Birth</div>
                            <div class="value">{{ $result->response_data['birthdate'] ?? $result->response_data['dob'] ?? '' }}</div>
                        </div>
                        <div class="cell">
                            <div class="label">Phone</div>
                            <div class="value">{{ $result->response_data['telephoneno'] ?? $result->response_data['phone'] ?? '' }}</div>
                        </div>
                        <div class="cell">
                            <div class="label">NIN</div>
                            <div class="value">{{ $result->response_data['nin'] ?? '—' }}</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="section">
                <div class="section-title">Origin & Address</div>
                <div class="grid">
                    <div class="row">
                        <div class="cell">
                            <div class="label">State of Origin</div>
                            <div class="value">{{ $result->response_data['self_origin_state'] ?? $result->response_data['state'] ?? '' }}</div>
                        </div>
                        <div class="cell">
                            <div class="label">LGA of Origin</div>
                            <div class="value">{{ $result->response_data['self_origin_lga'] ?? $result->response_data['lga'] ?? '' }}</div>
                        </div>
                        <div class="cell">
                            <div class="label">Nationality</div>
                            <div class="value">{{ $result->response_data['nationality'] ?? '' }}</div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="cell">
                            <div class="label">Residence State</div>
                            <div class="value">{{ $result->response_data['residence_state'] ?? '' }}</div>
                        </div>
                        <div class="cell">
                            <div class="label">Residence LGA</div>
                            <div class="value">{{ $result->response_data['residence_lga'] ?? '' }}</div>
                        </div>
                        <div class="cell">
                            <div class="label">Marital Status</div>
                            <div class="value">{{ $result->response_data['maritalstatus'] ?? '' }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="right" style="text-align:center;">
            @php $photo = $result->response_data['photo'] ?? $result->response_data['image'] ?? null; @endphp
            @if($photo)
                <img class="photo" src="{{ str_starts_with($photo, 'http') || str_starts_with($photo, 'data:') ? $photo : 'data:image/jpeg;base64,' . $photo }}">
            @else
                <div class="photo" style="line-height:110px; color:#9ca3af;">No Photo</div>
            @endif
            <div style="margin-top:8px;"><span class="badge">VERIFIED</span></div>
        </div>
    </div>
    <div class="footer">
        This slip summarizes identity details verified against official sources via {{ $result->provider_name }}. Reference {{ $result->reference_id }}.
    </div>
</body>
</html>
