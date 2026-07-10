<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>NIN Standard Slip - {{ $result->reference_id }}</title>
    <style>
        @page { margin: 0; }
        body { font-family: 'Helvetica', 'Arial', sans-serif; margin: 0; padding: 0; background: #ffffff; }
        .sheet { width: 500px; margin: 0 auto; }
        .card { width: 500px; height: 300px; position: relative; overflow: hidden; border: 1px solid #111827; background: #ffffff; }
        .watermark {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            opacity: 0.06;
            width: 360px;
            text-align: center;
            font-size: 40px;
            font-weight: 900;
            letter-spacing: 6px;
            color: #008751;
            z-index: 0;
        }
        .header {
            text-align: center;
            padding-top: 10px;
            z-index: 1;
            position: relative;
        }
        .badge { display: inline-block; padding: 3px 8px; background: #111827; color: #ffffff; font-size: 9px; font-weight: 900; letter-spacing: 0.4px; text-transform: uppercase; border-radius: 10px; margin-top: 6px; }
        .country {
            color: #008751;
            font-size: 14px;
            font-weight: bold;
            margin-top: 2px;
            text-transform: uppercase;
        }
        .slip-type {
            font-size: 10px;
            font-weight: bold;
            color: #333;
            margin-top: 1px;
        }
        .main-content {
            padding: 10px 20px;
            z-index: 1;
            position: relative;
        }
        .photo-container {
            float: left;
            width: 85px;
            height: 105px;
            background: #eee;
            border-radius: 4px;
            overflow: hidden;
            border: 1px solid #ccc;
        }
        .photo-container img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .details {
            margin-left: 100px;
        }
        .field {
            margin-bottom: 6px;
        }
        .label {
            font-size: 8px;
            color: #666;
            text-transform: uppercase;
        }
        .value {
            font-size: 11px;
            font-weight: bold;
            color: #000;
        }
        .qr-container {
            position: absolute;
            top: 45px;
            right: 20px;
            text-align: center;
        }
        .qr-code {
            width: 60px;
            height: 60px;
            background: white;
            padding: 2px;
            border: 1px solid #ddd;
        }
        .nga-text {
            font-size: 14px;
            font-weight: bold;
            color: #000;
            margin-bottom: 2px;
        }
        .issue-date {
            font-size: 7px;
            color: #444;
        }
        .nin-container {
            position: absolute;
            bottom: 15px;
            width: 100%;
            text-align: center;
        }
        .nin-label {
            font-size: 9px;
            font-weight: bold;
            margin-bottom: 2px;
        }
        .nin-value {
            font-size: 24px;
            font-weight: 800;
            letter-spacing: 2px;
            color: #000;
        }
        .footer-brand {
            position: absolute;
            bottom: 5px;
            right: 10px;
            font-size: 7px;
            color: #999;
        }
        .divider { width: 500px; height: 2px; background: #111827; }
        .back-inner {
            position: absolute;
            top: 0;
            left: 0;
            width: 500px;
            height: 300px;
            transform: rotate(180deg);
            transform-origin: 50% 50%;
            padding: 18px 22px;
            box-sizing: border-box;
        }
        .back-title { text-align: center; font-size: 30px; font-weight: 900; margin-top: 18px; margin-bottom: 2px; color: #111827; }
        .back-subtitle { text-align: center; font-size: 14px; font-weight: 700; margin-bottom: 16px; color: #111827; }
        .back-text { font-size: 12px; line-height: 1.45; text-align: center; color: #111827; }
        .back-text p { margin: 12px 0; }
        .meta { margin-top: 14px; border-top: 1px solid rgba(17, 24, 39, 0.15); padding-top: 12px; }
        .meta-title { text-align: center; font-size: 11px; font-weight: 900; letter-spacing: 0.4px; text-transform: uppercase; color: #111827; margin-bottom: 8px; }
        .meta-table { width: 100%; border-collapse: collapse; font-size: 9px; color: #111827; }
        .meta-table td { padding: 4px 0; vertical-align: top; }
        .meta-k { width: 36%; font-weight: 900; color: rgba(17, 24, 39, 0.75); text-transform: uppercase; letter-spacing: 0.25px; }
        .meta-v { width: 64%; font-weight: 800; }
        .back-sign { text-align: center; font-size: 14px; font-weight: 900; margin-top: 20px; color: #111827; }
        .clearfix::after {
            content: "";
            clear: both;
            display: table;
        }
    </style>
</head>
<body>
    <div class="sheet">
        <div class="card">
            <div class="watermark">NIGERIA</div>

            <div class="header">
                <div class="country">FEDERAL REPUBLIC OF NIGERIA</div>
                <div class="slip-type">NIN SLIP</div>
                <div class="badge">Standard</div>
            </div>

            <div class="main-content clearfix">
                <div class="photo-container">
                    @php $photo = $result->response_data['photo'] ?? $result->response_data['image'] ?? null; @endphp
                    @if($photo)
                        <img src="{{ str_starts_with($photo, 'http') || str_starts_with($photo, 'data:') ? $photo : 'data:image/jpeg;base64,' . $photo }}">
                    @endif
                </div>

                <div class="details">
                    <div class="field">
                        <div class="label">Surname/Nom</div>
                        <div class="value">{{ strtoupper($result->response_data['lastname'] ?? $result->response_data['surname'] ?? '') }}</div>
                    </div>
                    <div class="field">
                        <div class="label">Given Names/Prénoms</div>
                        <div class="value">{{ strtoupper($result->response_data['firstname'] ?? '') }} {{ strtoupper($result->response_data['middlename'] ?? '') }}</div>
                    </div>
                    <div style="display: table; width: 100%;">
                        <div style="display: table-cell; width: 50%;">
                            <div class="field">
                                <div class="label">Date of Birth</div>
                                <div class="value">{{ $result->response_data['birthdate'] ?? $result->response_data['dob'] ?? '' }}</div>
                            </div>
                        </div>
                        <div style="display: table-cell; width: 50%;">
                            <div class="field">
                                <div class="label">Sex/Sexe</div>
                                <div class="value">{{ strtoupper(substr($result->response_data['gender'] ?? '—', 0, 1)) }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="qr-container">
                <div class="nga-text">NGA</div>
                <div class="nga-text" style="font-size: 10px; font-weight: normal; margin-top: -5px;">00000000000</div>
                <div class="qr-code">
                    @php
                        $qrData = "NIN:" . ($result->response_data['nin'] ?? '') . "\nName:" . ($result->response_data['firstname'] ?? '') . " " . ($result->response_data['lastname'] ?? '');
                        $qrCode = \App\Support\QrCodeDataUri::make($qrData, 60);
                    @endphp
                    @if($qrCode)
                        <img src="{{ $qrCode }}" style="width: 100%; height: 100%;">
                    @endif
                </div>
                <div class="issue-date">ISSUE DATE<br><strong>{{ $result->created_at->format('d M Y') }}</strong></div>
            </div>

            <div class="nin-container">
                <div class="nin-label">National Identification Number (NIN)</div>
                <div class="nin-value">
                    @php
                        $nin = preg_replace('/\D+/', '', (string) ($result->response_data['nin'] ?? '00000000000')) ?: '00000000000';
                        $formattedNin = substr($nin, 0, 4) . ' ' . substr($nin, 4, 3) . ' ' . substr($nin, 7);
                    @endphp
                    {{ $formattedNin }}
                </div>
            </div>

            <div class="footer-brand">
                {{ \App\Models\SystemSetting::get('site_name', 'Fuwa.NG') }} • {{ $result->reference_id }}
            </div>
        </div>

        <div class="divider"></div>

        <div class="card">
            <div class="back-inner">
                <div class="back-title">DISCLAIMER</div>
                <div class="back-subtitle">Trust but verify</div>
                <div class="back-text">
                    <p>Kindly ensure each time this slip is presented, that you verify the credentials using a Government approved verification resource.</p>
                    <p>The details on the front of this NIN slip must exactly match the verification result.</p>
                    <p>If this NIN was not issued to the permitted bearer on the front of this document, please do not accept any receipt.</p>
                </div>
                <div class="meta">
                    <div class="meta-title">Verification Details</div>
                    @php
                        $phone = $result->response_data['telephoneno'] ?? $result->response_data['phone'] ?? null;
                        $address = $result->response_data['address'] ?? $result->response_data['residence'] ?? null;
                        $mode = $result->response_data['_verification_mode'] ?? null;
                    @endphp
                    <table class="meta-table">
                        <tr><td class="meta-k">Reference</td><td class="meta-v">{{ $result->reference_id }}</td></tr>
                        <tr><td class="meta-k">Generated</td><td class="meta-v">{{ $result->created_at->format('d M Y, H:i') }}</td></tr>
                        <tr><td class="meta-k">Provider</td><td class="meta-v">{{ $result->provider_name ?? '—' }}</td></tr>
                        <tr><td class="meta-k">Mode</td><td class="meta-v">{{ $mode ?: '—' }}</td></tr>
                        <tr><td class="meta-k">Phone</td><td class="meta-v">{{ $phone ?: '—' }}</td></tr>
                        <tr><td class="meta-k">Address</td><td class="meta-v">{{ $address ?: '—' }}</td></tr>
                    </table>
                </div>
                <div class="back-sign">{{ \App\Models\SystemSetting::get('site_name', 'Fuwa.NG') }}</div>
            </div>
        </div>
    </div>
</body>
</html>
