<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>NIN Premium Slip - {{ $result->reference_id }}</title>
    <style>
        @page { margin: 0; }
        body { font-family: 'Helvetica', 'Arial', sans-serif; margin: 0; padding: 0; background: #ffffff; }
        .sheet { width: 500px; margin: 0 auto; }

        .card {
            width: 500px;
            height: 318px;
            position: relative;
            overflow: hidden;
            box-sizing: border-box;
        }

        .front {
            background-size: 500px 318px;
            background-repeat: no-repeat;
            background-position: center;
        }

        .photo-container {
            position: absolute;
            top: 84px;
            left: 22px;
            width: 110px;
            height: 136px;
            overflow: hidden;
            border-radius: 4px;
        }

        .photo-container img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .value-surname {
            position: absolute;
            top: 108px;
            left: 154px;
            font-size: 13px;
            font-weight: bold;
            text-transform: uppercase;
            color: #000000;
        }

        .value-given-names {
            position: absolute;
            top: 148px;
            left: 154px;
            font-size: 13px;
            font-weight: bold;
            text-transform: uppercase;
            color: #000000;
        }

        .value-dob {
            position: absolute;
            top: 188px;
            left: 154px;
            font-size: 13px;
            font-weight: bold;
            color: #000000;
        }

        .value-sex {
            position: absolute;
            top: 188px;
            left: 282px;
            font-size: 13px;
            font-weight: bold;
            color: #000000;
        }

        .qr-container {
            position: absolute;
            top: 22px;
            right: 22px;
            width: 90px;
            height: 90px;
        }

        .qr-container img {
            width: 100%;
            height: 100%;
        }

        .value-issue-date {
            position: absolute;
            top: 202px;
            right: 22px;
            font-size: 10px;
            font-weight: bold;
            color: #000000;
            text-align: right;
            width: 100px;
        }

        .value-nin {
            position: absolute;
            bottom: 12px;
            left: 0;
            width: 500px;
            text-align: center;
            font-size: 32px;
            font-weight: bold;
            letter-spacing: 5px;
            color: #000000;
        }

        .divider { width: 500px; height: 2px; background: #ffffff; }

        .back { background: #ffffff; border: 1px solid #111827; }

        .back-inner {
            position: absolute;
            top: 0;
            left: 0;
            width: 500px;
            height: 318px;
            transform: rotate(180deg);
            transform-origin: 50% 50%;
            padding: 18px 22px;
            box-sizing: border-box;
        }

        .back-title { text-align: center; font-size: 15px; font-weight: bold; margin-top: 12px; margin-bottom: 2px; color: #111827; }
        .back-subtitle { text-align: center; font-size: 10px; font-weight: bold; margin-bottom: 12px; color: #111827; }
        .back-text { font-size: 10px; line-height: 1.4; text-align: center; color: #111827; }
        .back-text p { margin: 8px 0; }
        .meta { margin-top: 20px; border-top: 1px solid rgba(17, 24, 39, 0.1); padding-top: 10px; text-align: center; font-size: 9px; font-weight: bold; color: rgba(17, 24, 39, 0.6); }
    </style>
</head>
<body>
    @php
        $bgPath = public_path('assets/images/nin_premium_bg.png');
        $bgData = '';
        if (file_exists($bgPath)) {
            $bgData = 'data:image/png;base64,' . base64_encode(file_get_contents($bgPath));
        }
    @endphp
    <div class="sheet">
        <div class="card front" style="background-image: url('{{ $bgData }}');">
            <!-- Photo -->
            <div class="photo-container">
                @php $photo = $result->response_data['photo'] ?? $result->response_data['image'] ?? null; @endphp
                @if($photo)
                    <img src="{{ str_starts_with($photo, 'http') || str_starts_with($photo, 'data:') ? $photo : 'data:image/jpeg;base64,' . $photo }}">
                @endif
            </div>

            <!-- Surname -->
            <div class="value-surname">
                {{ strtoupper($result->response_data['lastname'] ?? $result->response_data['surname'] ?? '') }}
            </div>

            <!-- Given Names -->
            <div class="value-given-names">
                {{ strtoupper($result->response_data['firstname'] ?? '') }} {{ strtoupper($result->response_data['middlename'] ?? '') }}
            </div>

            <!-- Date of Birth -->
            <div class="value-dob">
                @php
                    $dob = $result->response_data['birthdate'] ?? $result->response_data['dob'] ?? '';
                    // Try to format to dd MMM yyyy or similar if match standard pattern
                    try {
                        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $dob)) {
                            $dob = date('d M Y', strtotime($dob));
                        }
                    } catch (\Exception $e) {}
                @endphp
                {{ strtoupper($dob) }}
            </div>

            <!-- Sex -->
            <div class="value-sex">
                {{ strtoupper(substr($result->response_data['gender'] ?? '—', 0, 1)) }}
            </div>

            <!-- QR Code -->
            <div class="qr-container">
                @php
                    $qrData = "NIN:" . ($result->response_data['nin'] ?? '') . "\nName:" . ($result->response_data['firstname'] ?? '') . " " . ($result->response_data['lastname'] ?? '');
                    $qrCode = \App\Support\QrCodeDataUri::make($qrData, 90);
                @endphp
                @if($qrCode)
                    <img src="{{ $qrCode }}">
                @endif
            </div>

            <!-- Issue Date -->
            <div class="value-issue-date">
                {{ strtoupper($result->created_at->format('d M Y')) }}
            </div>

            <!-- NIN -->
            <div class="value-nin">
                @php
                    $nin = preg_replace('/\D+/', '', (string) ($result->response_data['nin'] ?? '00000000000')) ?: '00000000000';
                    $formattedNin = substr($nin, 0, 4) . ' ' . substr($nin, 4, 3) . ' ' . substr($nin, 7);
                @endphp
                {{ $formattedNin }}
            </div>
        </div>

        <div class="divider"></div>

        <div class="card back">
            <div class="back-inner">
                <div class="back-title">PROPERTY OF THE FEDERAL REPUBLIC OF NIGERIA</div>
                <div class="back-subtitle">National Identity Management Commission (NIMC)</div>
                <div class="back-text">
                    <p>This card is the property of the Federal Government of Nigeria. It must be produced on demand by authorized persons.</p>
                    <p>If found, please return to the nearest National Identity Management Commission (NIMC) office or the nearest Police Station.</p>
                    <p style="margin-top: 24px; font-weight: bold; font-size: 8px;">NIMC Head Office: 11 Sokode Crescent, Wuse Zone 5, Abuja.<br>Website: www.nimc.gov.ng | Email: info@nimc.gov.ng</p>
                </div>
                <div class="meta">
                    Card ID: {{ $result->reference_id }} | Generated: {{ $result->created_at->format('d/m/Y') }}
                </div>
            </div>
        </div>
    </div>
</body>
</html>
