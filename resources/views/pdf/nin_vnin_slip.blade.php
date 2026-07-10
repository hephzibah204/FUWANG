<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>vNIN Slip - {{ $result->reference_id }}</title>
    <style>
        @page { margin: 0; }
        body { font-family: 'Helvetica', 'Arial', sans-serif; margin: 0; padding: 20px; background: #f3f4f6; }
        .card { 
            width: 500px; 
            height: 300px; 
            background: #fdf2f8; /* Very light pink/purple for vNIN */
            border-radius: 10px; 
            box-shadow: 0 4px 6px rgba(0,0,0,0.1); 
            margin: 0 auto; 
            position: relative; 
            overflow: hidden;
            border: 1px solid #7c3aed;
        }
        .watermark {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            opacity: 0.05;
            width: 360px;
            text-align: center;
            font-size: 40px;
            font-weight: 900;
            letter-spacing: 6px;
            color: #7c3aed;
            z-index: 0;
        }
        .header {
            text-align: center;
            padding-top: 10px;
            z-index: 1;
            position: relative;
        }
        .country {
            color: #7c3aed;
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
        .vnin-badge {
            display: inline-block;
            background: #7c3aed;
            color: white;
            padding: 2px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: bold;
            margin-top: 4px;
        }
        .footer-brand {
            position: absolute;
            bottom: 5px;
            right: 10px;
            font-size: 7px;
            color: #999;
        }
        .clearfix::after {
            content: "";
            clear: both;
            display: table;
        }
    </style>
</head>
<body>
    <div class="card">
        <div class="watermark">NIGERIA</div>
        <div class="header">
            <div class="country">FEDERAL REPUBLIC OF NIGERIA</div>
            <div class="slip-type">vNIN SLIP</div>
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
                    <div class="label">Full Name</div>
                    <div class="value">
                        {{ trim(implode(' ', array_filter([
                            $result->response_data['firstname'] ?? null,
                            $result->response_data['middlename'] ?? null,
                            $result->response_data['lastname'] ?? ($result->response_data['surname'] ?? null),
                        ]))) }}
                    </div>
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
                <div class="field">
                    <div class="label">vNIN</div>
                    <div class="vnin-badge">{{ $result->response_data['vnin'] ?? $result->response_data['vNin'] ?? '—' }}</div>
                </div>
            </div>
        </div>

        <div class="qr-container">
            <div class="nga-text">NGA</div>
            <div class="qr-code">
                @php
                    $qrData = "vNIN:" . ($result->response_data['vnin'] ?? '') . "\nName:" . ($result->response_data['firstname'] ?? '') . " " . ($result->response_data['lastname'] ?? '');
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
                    $nin = (string)($result->response_data['nin'] ?? '00000000000');
                    $formattedNin = substr($nin, 0, 4) . ' ' . substr($nin, 4, 3) . ' ' . substr($nin, 7);
                @endphp
                {{ $formattedNin }}
            </div>
        </div>

        <div class="footer-brand">
            {{ \App\Models\SystemSetting::get('site_name', 'Fuwa.NG') }} • {{ $result->reference_id }}
        </div>
    </div>
</body>
</html>
