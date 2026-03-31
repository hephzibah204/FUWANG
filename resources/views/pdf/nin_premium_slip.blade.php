<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>NIN Premium Slip - {{ $result->reference_id }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; color: #0f172a; font-size: 12px; }
        .header { display: flex; justify-content: space-between; align-items: center; border-bottom: 3px solid #0891b2; padding-bottom: 12px; margin-bottom: 16px; }
        .brand { font-weight: 900; color: #0891b2; font-size: 20px; letter-spacing: 0.5px; }
        .meta { font-size: 11px; color: #334155; }
        .qr { width: 86px; height: 86px; border: 1px dashed #94a3b8; text-align: center; line-height: 86px; font-size: 10px; color: #64748b; }
        .section { margin-bottom: 14px; }
        .title { font-weight: 800; font-size: 13px; color: #0e7490; border-bottom: 1px solid #e2e8f0; padding-bottom: 6px; margin-bottom: 8px; }
        .grid { display: table; width: 100%; border-collapse: collapse; }
        .row { display: table-row; }
        .cell { display: table-cell; padding: 7px 9px; border: 1px solid #e2e8f0; vertical-align: top; }
        .label { font-size: 10px; color: #64748b; text-transform: uppercase; }
        .value { font-size: 12px; color: #0f172a; font-weight: 700; }
        .photo { width: 110px; height: 130px; object-fit: cover; border: 2px solid #0891b2; }
        .pill { display: inline-block; padding: 4px 10px; border-radius: 999px; background: #dcfce7; color: #166534; font-weight: 800; font-size: 10px; }
        .fine { font-size: 10px; color: #475569; }
    </style>
</head>
<body>
    <div class="header">
        <div>
            <div class="brand">{{ \App\Models\SystemSetting::get('site_name', 'G-Soft Verify') }}</div>
            <div class="meta">NIN Premium Slip • Reference {{ $result->reference_id }} • {{ $result->created_at->format('Y-m-d H:i') }}</div>
        </div>
        <div class="qr">SECURE QR</div>
    </div>
    <div class="section">
        <table style="width:100%;">
            <tr>
                <td style="width:70%; vertical-align:top;">
                    <div class="title">Identity Overview</div>
                    <div class="grid">
                        <div class="row">
                            <div class="cell">
                                <div class="label">First Name</div>
                                <div class="value">{{ $result->response_data['firstname'] ?? '' }}</div>
                            </div>
                            <div class="cell">
                                <div class="label">Middle Name</div>
                                <div class="value">{{ $result->response_data['middlename'] ?? '' }}</div>
                            </div>
                            <div class="cell">
                                <div class="label">Last Name</div>
                                <div class="value">{{ $result->response_data['lastname'] ?? $result->response_data['surname'] ?? '' }}</div>
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
                                <div class="label">NIN</div>
                                <div class="value">{{ $result->response_data['nin'] ?? '—' }}</div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="cell">
                                <div class="label">Phone</div>
                                <div class="value">{{ $result->response_data['telephoneno'] ?? $result->response_data['phone'] ?? '' }}</div>
                            </div>
                            <div class="cell">
                                <div class="label">State of Origin</div>
                                <div class="value">{{ $result->response_data['self_origin_state'] ?? $result->response_data['state'] ?? '' }}</div>
                            </div>
                            <div class="cell">
                                <div class="label">LGA of Origin</div>
                                <div class="value">{{ $result->response_data['self_origin_lga'] ?? $result->response_data['lga'] ?? '' }}</div>
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
                                <div class="label">Nationality</div>
                                <div class="value">{{ $result->response_data['nationality'] ?? '' }}</div>
                            </div>
                        </div>
                    </div>
                </td>
                <td style="width:30%; text-align:center; vertical-align:top;">
                    @php $photo = $result->response_data['photo'] ?? $result->response_data['image'] ?? null; @endphp
                    @if($photo)
                        <img class="photo" src="{{ str_starts_with($photo, 'http') || str_starts_with($photo, 'data:') ? $photo : 'data:image/jpeg;base64,' . $photo }}">
                    @else
                        <div class="photo" style="line-height:130px; color:#94a3b8; border-style:dashed;">No Photo</div>
                    @endif
                    <div style="margin-top:8px;"><span class="pill">VERIFIED</span></div>
                </td>
            </tr>
        </table>
    </div>
    <div class="section">
        <div class="title">Notes</div>
        <div class="fine">This premium slip presents expanded fields and enhanced formatting suitable for official reviews. Data is sourced via {{ $result->provider_name }} and tied to Reference {{ $result->reference_id }}.</div>
    </div>
    <div class="section fine" style="border-top:1px solid #e2e8f0; padding-top:8px;">
        &copy; {{ date('Y') }} {{ \App\Models\SystemSetting::get('site_name', 'G-Soft Verify') }}. For verification, scan the QR or visit our portal and enter the reference ID.
    </div>
</body>
</html>
