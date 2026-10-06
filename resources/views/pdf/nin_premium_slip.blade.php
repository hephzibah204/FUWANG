<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>NIN Premium Slip - {{ $result->reference_id }}</title>
    <style>
        @page { 
            margin: 10mm auto; 
            size: A4 portrait; 
        }
        body { 
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; 
            margin: 0; 
            padding: 0; 
            background: #ffffff; 
            -webkit-print-color-adjust: exact; 
            print-color-adjust: exact; 
        }
        .sheet { 
            width: 500px; 
            margin: 0 auto; 
            padding-top: 5px; 
        }

        .instructions {
            text-align: center;
            margin-bottom: 22px;
            padding: 0 10px;
        }
        .instructions p {
            margin: 3px 0;
            color: #000000;
            font-size: 13.5px;
            font-weight: bold;
            line-height: 1.35;
        }

        .card {
            width: 500px;
            height: 318px;
            position: relative;
            overflow: hidden;
            box-sizing: border-box;
            border: 1px solid #111827;
        }

        .card.front {
            background-color: #ffffff;
        }

        .bg-img {
            position: absolute;
            top: 0;
            left: 0;
            width: 500px;
            height: 318px;
            z-index: 1;
        }

        .card-content {
            position: absolute;
            top: 0;
            left: 0;
            width: 500px;
            height: 318px;
            z-index: 2;
        }

        .photo-container {
            position: absolute;
            top: 61px;
            left: 20px;
            width: 107px;
            height: 152px;
            overflow: hidden;
            background: #f8fafc;
            border-radius: 2px;
        }

        .photo-container img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .photo-placeholder {
            width: 100%;
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #e2e8f0;
            text-align: center;
            padding-top: 45px;
            box-sizing: border-box;
            color: #94a3b8;
            font-size: 11px;
            font-weight: bold;
        }

        .value-surname {
            position: absolute;
            top: 110px;
            left: 151px;
            font-size: 12.5px;
            font-weight: bold;
            text-transform: uppercase;
            color: #000000;
            letter-spacing: 0.3px;
        }

        .value-given-names {
            position: absolute;
            top: 152px;
            left: 151px;
            font-size: 12.5px;
            font-weight: bold;
            text-transform: uppercase;
            color: #000000;
            letter-spacing: 0.3px;
            max-width: 240px;
            line-height: 1.2;
        }

        .value-dob {
            position: absolute;
            top: 194px;
            left: 151px;
            font-size: 11.5px;
            font-weight: bold;
            color: #000000;
        }

        .value-sex {
            position: absolute;
            top: 194px;
            left: 276px;
            font-size: 11.5px;
            font-weight: bold;
            color: #000000;
        }

        .qr-container {
            position: absolute;
            top: 27px;
            right: 20px;
            width: 114px;
            height: 114px;
            text-align: center;
        }

        .qr-container img {
            width: 114px;
            height: 114px;
            display: block;
        }

        .value-issue-date {
            position: absolute;
            top: 206px;
            right: 19px;
            width: 116px;
            font-size: 10px;
            font-weight: bold;
            color: #000000;
            text-align: center;
        }

        .value-nin {
            position: absolute;
            bottom: 11px;
            left: 0;
            width: 500px;
            text-align: center;
            font-size: 28px;
            font-weight: 900;
            letter-spacing: 5px;
            color: #000000;
            font-family: 'Arial Black', 'Helvetica Neue', Helvetica, Arial, sans-serif;
        }

        /* Ghost watermark repetitions for anti-counterfeiting authenticity */
        .ghost-watermark-1 {
            position: absolute;
            bottom: 50px;
            left: 12px;
            transform: rotate(-35deg);
            font-size: 9.5px;
            font-weight: bold;
            color: #16a34a;
            opacity: 0.35;
            letter-spacing: 1px;
        }
        .ghost-watermark-2 {
            position: absolute;
            top: 174px;
            right: 14px;
            transform: rotate(-35deg);
            font-size: 9.5px;
            font-weight: bold;
            color: #16a34a;
            opacity: 0.35;
            letter-spacing: 1px;
        }
        .ghost-watermark-3 {
            position: absolute;
            bottom: 45px;
            right: 12px;
            transform: rotate(-35deg);
            font-size: 9.5px;
            font-weight: bold;
            color: #16a34a;
            opacity: 0.35;
            letter-spacing: 1px;
        }

        .fold-divider { 
            width: 500px; 
            height: 1px; 
            border-top: 1px dashed #4b5563; 
            margin: 0; 
        }

        .card.back {
            background: #ffffff;
            border: 1px solid #000000;
        }

        .back-inner {
            width: 500px;
            height: 318px;
            transform: rotate(180deg);
            padding: 16px 24px;
            box-sizing: border-box;
            text-align: center;
        }

        .back-title {
            font-size: 20px;
            font-weight: bold;
            letter-spacing: 1.5px;
            margin-top: 6px;
            margin-bottom: 3px;
            color: #000000;
        }
        .back-subtitle {
            font-size: 11px;
            font-style: italic;
            font-family: 'Times New Roman', Georgia, serif;
            margin-bottom: 12px;
            color: #000000;
        }
        .back-text {
            font-size: 9.5px;
            line-height: 1.45;
            color: #000000;
            padding: 0 6px;
        }
        .back-text p {
            margin: 5px 0;
        }
        .caution-title {
            font-weight: bold;
            font-size: 12px;
            margin: 11px 0 5px 0 !important;
            letter-spacing: 0.5px;
        }
    </style>
</head>
<body>
    @if(request()->boolean('html') || !empty($autoPrint))
        <div class="no-print" style="text-align: center; padding: 12px; background: #f0fdf4; border-bottom: 2px solid #22c55e; margin-bottom: 20px;">
            <span style="font-weight: bold; color: #15803d; margin-right: 15px; font-size: 14px;">NIN Premium Slip Print Preview</span>
            <button onclick="window.print()" style="padding: 8px 20px; background: #16a34a; color: white; border: none; border-radius: 6px; font-weight: bold; cursor: pointer; font-size: 14px; margin-right: 10px;">
                🖨️ Print Slip
            </button>
            <button onclick="window.close()" style="padding: 8px 16px; background: #64748b; color: white; border: none; border-radius: 6px; font-weight: bold; cursor: pointer; font-size: 14px;">
                ✕ Close
            </button>
        </div>
        <style>
            @media print {
                .no-print { display: none !important; }
            }
        </style>
        @if(!empty($autoPrint))
            <script>
                window.addEventListener('load', function() {
                    setTimeout(function() {
                        window.print();
                    }, 400);
                });
            </script>
        @endif
    @endif
    @php
        $bgPath = public_path('assets/images/nin_premium_bg.png');
        $bgData = '';
        if (file_exists($bgPath)) {
            $bgData = 'data:image/png;base64,' . base64_encode(file_get_contents($bgPath));
        }

        $nin = preg_replace('/\D+/', '', (string) ($result->response_data['nin'] ?? '00000000000')) ?: '00000000000';
        $formattedNin = substr($nin, 0, 4) . ' ' . substr($nin, 4, 3) . ' ' . substr($nin, 7);

        $surname = strtoupper($result->response_data['lastname'] ?? $result->response_data['surname'] ?? '');
        $firstname = strtoupper($result->response_data['firstname'] ?? '');
        $middlename = strtoupper($result->response_data['middlename'] ?? '');
        $givenNames = trim($firstname . ($middlename ? ' ' . $middlename : ''));

        $dob = $result->response_data['birthdate'] ?? $result->response_data['dob'] ?? '';
        try {
            if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $dob)) {
                $dob = date('d M Y', strtotime($dob));
            }
        } catch (\Exception $e) {}
        $dobFormatted = strtoupper($dob ?: '—');

        $gender = strtoupper(substr($result->response_data['gender'] ?? '—', 0, 1));

        $issueDate = optional($result->created_at)->format('d M Y') ?? date('d M Y');

        $photo = $result->response_data['photo'] ?? $result->response_data['image'] ?? null;

        $qrData = "NIN:" . $nin . "\nName:" . $firstname . " " . $surname;
        $qrCode = \App\Support\QrCodeDataUri::make($qrData, 120);
    @endphp
    <div class="sheet">
        <!-- Top Instruction Header -->
        <div class="instructions">
            <p>Please find below your new High Resolution NIN Slip.</p>
            <p>You may cut it out of the paper, fold and laminate as desired.</p>
            <p>Please DO NOT allow others to make copies of your NIN Slip.</p>
        </div>

        <!-- Front Card -->
        <div class="card front">
            @if($bgData)
                <img class="bg-img" src="{{ $bgData }}" alt="NIN Slip Background">
            @endif

            <div class="card-content">
                <!-- User Photo -->
                <div class="photo-container">
                    @if($photo)
                        <img src="{{ str_starts_with($photo, 'http') || str_starts_with($photo, 'data:') ? $photo : 'data:image/jpeg;base64,' . $photo }}" alt="Portrait Photo">
                    @else
                        <div class="photo-placeholder">PHOTO</div>
                    @endif
                </div>

                <!-- Surname -->
                <div class="value-surname">{{ $surname }}</div>

                <!-- Given Names -->
                <div class="value-given-names">{{ $givenNames }}</div>

                <!-- Date of Birth -->
                <div class="value-dob">{{ $dobFormatted }}</div>

                <!-- Sex -->
                <div class="value-sex">{{ $gender }}</div>

                <!-- QR Code -->
                <div class="qr-container">
                    @if($qrCode)
                        <img src="{{ $qrCode }}" alt="Verification QR Code">
                    @endif
                </div>

                <!-- Issue Date -->
                <div class="value-issue-date">{{ strtoupper($issueDate) }}</div>

                <!-- Ghost Watermarks -->
                <div class="ghost-watermark-1">{{ $formattedNin }}</div>
                <div class="ghost-watermark-2">{{ $formattedNin }}</div>
                <div class="ghost-watermark-3">{{ $formattedNin }}</div>

                <!-- Bottom Large Formatted NIN -->
                <div class="value-nin">{{ $formattedNin }}</div>
            </div>
        </div>

        <div class="fold-divider"></div>

        <!-- Back Card (180deg inverted for fold-and-laminate) -->
        <div class="card back">
            <div class="back-inner">
                <div class="back-title">DISCLAIMER</div>
                <div class="back-subtitle">Trust, but verify</div>
                <div class="back-text">
                    <p>Kindly ensure each time this ID is presented, that you verify the credentials using a Government-APPROVED verification resource.</p>
                    <p>The details on the front of this NIN Slip must EXACTLY match the verification result.</p>
                    <p class="caution-title">CAUTION!</p>
                    <p>If this NIN was not issued to the person on the front of this document, please DO NOT attempt to scan, photocopy or replicate the personal data contained herein.</p>
                    <p>You are only permitted to scan the barcode for the purpose of identity verification.</p>
                    <p>The FEDERAL GOVERNMENT of NIGERIA assumes no responsibility if you accept any variance in the scan result or do not scan the 2D barcode overleaf.</p>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
