<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>NIN Premium Slip - {{ $result->reference_id }}</title>
    <style>
        @page { margin: 20px 0 0 0; }
        body { font-family: 'Helvetica', 'Arial', sans-serif; margin: 0; padding: 0; background: #ffffff; }
        .sheet { width: 500px; margin: 0 auto; padding-top: 10px; }

        .instructions {
            text-align: center;
            margin-bottom: 25px;
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            padding: 0 10px;
        }
        .instructions p {
            margin: 3px 0;
            color: #000000;
            font-size: 14px;
            font-weight: bold;
            line-height: 1.3;
        }

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
            border: 1px solid #d1d5db;
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

        .divider { width: 500px; height: 1px; background: #ffffff; }

        .back {
            background: #ffffff;
            border: 1px solid #000000;
        }

        .back-inner {
            position: absolute;
            top: 0;
            left: 0;
            width: 500px;
            height: 318px;
            transform: rotate(180deg);
            transform-origin: 50% 50%;
            padding: 14px 20px;
            box-sizing: border-box;
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
        }

        .back-title {
            text-align: center;
            font-size: 20px;
            font-weight: bold;
            letter-spacing: 1.5px;
            margin-top: 10px;
            margin-bottom: 2px;
            color: #000000;
        }
        .back-subtitle {
            text-align: center;
            font-size: 10px;
            font-style: italic;
            margin-bottom: 14px;
            color: #000000;
        }
        .back-text {
            font-size: 9px;
            line-height: 1.4;
            text-align: center;
            color: #000000;
            padding: 0 10px;
        }
        .back-text p {
            margin: 6px 0;
        }
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
        <!-- Top Instruction Header -->
        <div class="instructions">
            <p>Please find below your new High Resolution NIN Slip.</p>
            <p>You may cut it out of the paper, fold and laminate as desired.</p>
            <p>Please DO NOT allow others to make copies of your NIN Slip.</p>
        </div>

        <!-- Front Card -->
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

        <!-- Back Card (Upside Down / 180 degrees rotated) -->
        <div class="card back">
            <div class="back-inner">
                <div class="back-title">DISCLAIMER</div>
                <div class="back-subtitle">Trust, but verify</div>
                <div class="back-text">
                    <p>Kindly ensure each time this ID is presented, that you verify the credentials using a Government-APPROVED verification resource.</p>
                    <p>The details on the front of this NIN Slip must EXACTLY match the verification result.</p>
                    <p style="font-weight: bold; font-size: 11px; margin: 12px 0 6px 0;">CAUTION!</p>
                    <p>If this NIN was not issued to the person on the front of this document, please DO NOT attempt to scan, photocopy or replicate the personal data contained herein.</p>
                    <p>You are only permitted to scan the barcode for the purpose of identity verification.</p>
                    <p>The FEDERAL GOVERNMENT of NIGERIA assumes no responsibility if you accept any variance in the scan result or do not scan the 2D barcode overleaf.</p>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
