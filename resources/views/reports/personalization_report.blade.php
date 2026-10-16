@php
    $d = $result->response_data ?? [];
    if (!is_array($d)) $d = [];
    $status = strtolower((string)($result->status ?? 'pending'));
    $isSuccess = in_array($status, ['successful', 'success', 'cleared', 'active', 'completed']);
    $isPending = in_array($status, ['pending', 'waiting_for_review', 'processing', 'in_progress', 'queued']);
    $isFailed = in_array($status, ['failed', 'rejected', 'error']);

    $userData = $d['user_data'] ?? $d['data']['user_data'] ?? $d['data'] ?? [];
    if (!is_array($userData)) $userData = [];

    $nin = $d['nin'] ?? $userData['nin'] ?? $d['data']['nin'] ?? null;
    $firstname = $userData['firstname'] ?? $userData['first_name'] ?? $d['firstname'] ?? $d['first_name'] ?? null;
    $lastname = $userData['lastname'] ?? $userData['last_name'] ?? $userData['surname'] ?? $d['lastname'] ?? $d['surname'] ?? null;
    $middlename = $userData['middlename'] ?? $userData['middle_name'] ?? $d['middlename'] ?? null;
    $dob = $userData['birthdate'] ?? $userData['dob'] ?? $d['birthdate'] ?? $d['dob'] ?? null;
    $gender = $userData['gender'] ?? $userData['sex'] ?? $d['gender'] ?? null;
    $photo = $userData['photo'] ?? $userData['image'] ?? $userData['photo_base64'] ?? $userData['photoBase64'] ?? $d['photo'] ?? $d['image'] ?? null;
    $pdfBase64 = $d['pdf_base64'] ?? $userData['pdf_base64'] ?? $d['data']['pdf_base64'] ?? null;

    $applicantName = ($firstname || $lastname) ? trim("$lastname $firstname $middlename") : ($result->user?->fullname ?? 'Applicant');
    $apiMessage = $d['message'] ?? ($isSuccess ? 'Identity record successfully personalized and activated.' : ($isPending ? 'Personalization ticket accepted for processing.' : 'Personalization processing could not be completed.'));

    // Clean vendor names
    $cleanApiMessage = preg_replace('/robosttech|dataverify|vuvaa/i', 'Gateway', (string) $apiMessage);

    // Format NIN with spaces
    $rawNin = preg_replace('/\D+/', '', (string)($nin ?? ''));
    $formattedNin = (strlen($rawNin) === 11)
        ? (substr($rawNin, 0, 4) . ' ' . substr($rawNin, 4, 3) . ' ' . substr($rawNin, 7))
        : ($nin ?: 'Pending Verification');
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NIMC Identity Personalization &amp; NIN Slip - {{ $result->reference_id }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@500;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    
    <style>
        :root {
            --primary: #8b5cf6;
            --primary-dark: #7c3aed;
            --primary-light: #ede9fe;
            --success: #059669;
            --warning: #d97706;
            --danger: #dc2626;
            --slate-900: #0f172a;
            --slate-800: #1e293b;
            --slate-700: #334155;
            --slate-600: #475569;
            --slate-100: #f1f5f9;
            --slate-50: #f8fafc;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: #0b1120;
            color: #1e293b;
            line-height: 1.5;
            padding: 24px 16px;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .action-bar {
            width: 100%;
            max-width: 840px;
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            margin-bottom: 20px;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 18px;
            font-size: 13.5px;
            font-weight: 600;
            border-radius: 8px;
            text-decoration: none;
            cursor: pointer;
            border: 1px solid transparent;
            transition: all 0.2s ease;
        }

        .btn-back {
            background: rgba(255, 255, 255, 0.08);
            color: #e2e8f0;
            border-color: rgba(255, 255, 255, 0.15);
        }
        .btn-back:hover {
            background: rgba(255, 255, 255, 0.15);
            color: #fff;
        }

        .btn-print {
            background: #2563eb;
            color: #fff;
        }
        .btn-print:hover {
            background: #1d4ed8;
        }

        .btn-pdf {
            background: #059669;
            color: #fff;
        }
        .btn-pdf:hover {
            background: #047857;
        }

        .btn-premium {
            background: #7c3aed;
            color: #fff;
        }
        .btn-premium:hover {
            background: #6d28d9;
        }

        .btn-warning {
            background: #d97706;
            color: #fff;
        }
        .btn-warning:hover {
            background: #b45309;
        }

        /* Certificate Container */
        .cert-card {
            width: 100%;
            max-width: 840px;
            background: #ffffff;
            border-radius: 16px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.4);
            position: relative;
            overflow: hidden;
            border: 1px solid rgba(226, 232, 240, 0.8);
            margin-bottom: 30px;
        }

        .cert-top-bar {
            height: 6px;
            background: linear-gradient(90deg, #8b5cf6 0%, #10b981 50%, #8b5cf6 100%);
        }

        .cert-inner {
            padding: 36px 40px;
        }

        /* Header */
        .cert-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-bottom: 2px solid #e2e8f0;
            padding-bottom: 24px;
            margin-bottom: 28px;
            gap: 20px;
        }

        .cert-brand {
            display: flex;
            align-items: center;
            gap: 16px;
        }

        .brand-logo-circle {
            width: 54px;
            height: 54px;
            border-radius: 12px;
            background: linear-gradient(135deg, #8b5cf6, #7c3aed);
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            box-shadow: 0 4px 10px rgba(139, 92, 246, 0.3);
        }

        .cert-title h1 {
            font-size: 20px;
            font-weight: 800;
            color: #0f172a;
            letter-spacing: -0.02em;
            margin-bottom: 2px;
        }

        .cert-title p {
            font-size: 13px;
            color: #64748b;
            font-weight: 500;
        }

        .cert-ref-badge {
            text-align: right;
        }

        .cert-ref-badge .ref-label {
            font-size: 11px;
            text-transform: uppercase;
            font-weight: 700;
            color: #94a3b8;
            letter-spacing: 0.05em;
        }

        .cert-ref-badge .ref-val {
            font-family: 'JetBrains Mono', monospace;
            font-size: 14px;
            font-weight: 700;
            color: #8b5cf6;
            display: block;
        }

        /* Status Hero */
        .status-hero {
            border-radius: 12px;
            padding: 22px 24px;
            margin-bottom: 28px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
        }

        .status-hero.success {
            background: linear-gradient(135deg, #f5f3ff 0%, #ecfdf5 100%);
            border: 1px solid #a7f3d0;
        }

        .status-hero.pending {
            background: linear-gradient(135deg, #fffbeb 0%, #fef3c7 100%);
            border: 1px solid #fde68a;
        }

        .status-hero.failed {
            background: linear-gradient(135deg, #fef2f2 0%, #fee2e2 100%);
            border: 1px solid #fecaca;
        }

        .status-hero-text h2 {
            font-size: 18px;
            font-weight: 800;
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 4px;
        }

        .status-hero.success .status-hero-text h2 { color: #065f46; }
        .status-hero.pending .status-hero-text h2 { color: #92400e; }
        .status-hero.failed .status-hero-text h2 { color: #991b1b; }

        .status-hero-text p {
            font-size: 13.5px;
            color: #475569;
            margin: 0;
        }

        .status-tag {
            padding: 8px 16px;
            border-radius: 999px;
            font-size: 12px;
            font-weight: 800;
            letter-spacing: 0.04em;
            text-transform: uppercase;
            white-space: nowrap;
        }

        .status-tag.success { background: #059669; color: #ffffff; }
        .status-tag.pending { background: #d97706; color: #ffffff; }
        .status-tag.failed { background: #dc2626; color: #ffffff; }

        /* Pending Alert Box */
        .pending-box {
            background: #fffbeb;
            border: 1px solid #fde68a;
            border-radius: 12px;
            padding: 24px;
            margin-bottom: 28px;
            text-align: center;
        }
        .pending-spinner {
            font-size: 36px;
            color: #d97706;
            margin-bottom: 12px;
        }

        /* Printable Slip Section */
        .slip-preview-card {
            background: #ffffff;
            border: 2px solid #008751;
            border-radius: 12px;
            box-shadow: 0 10px 25px rgba(0, 135, 81, 0.12);
            padding: 24px;
            margin-bottom: 28px;
            position: relative;
            overflow: hidden;
        }

        .slip-preview-header {
            text-align: center;
            border-bottom: 2px solid #008751;
            padding-bottom: 14px;
            margin-bottom: 20px;
        }

        .slip-country {
            font-size: 15px;
            font-weight: 800;
            color: #008751;
            letter-spacing: 0.05em;
            text-transform: uppercase;
        }

        .slip-subtitle {
            font-size: 11px;
            font-weight: 700;
            color: #475569;
            letter-spacing: 0.08em;
            text-transform: uppercase;
        }

        .slip-grid {
            display: flex;
            gap: 20px;
            align-items: flex-start;
        }

        .slip-photo {
            width: 105px;
            height: 125px;
            border-radius: 6px;
            border: 1px solid #cbd5e1;
            background: #f8fafc;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .slip-photo img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .slip-fields {
            flex: 1;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px 18px;
        }

        .slip-field-item label {
            display: block;
            font-size: 10px;
            text-transform: uppercase;
            color: #64748b;
            font-weight: 700;
            margin-bottom: 2px;
        }

        .slip-field-item span {
            font-size: 13.5px;
            font-weight: 700;
            color: #0f172a;
        }

        .slip-nin-highlight {
            grid-column: span 2;
            background: #ecfdf5;
            border: 1px solid #a7f3d0;
            border-radius: 8px;
            padding: 10px 16px;
            margin-top: 6px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .slip-nin-highlight .nin-label {
            font-size: 11px;
            font-weight: 800;
            text-transform: uppercase;
            color: #065f46;
            letter-spacing: 0.04em;
        }

        .slip-nin-highlight .nin-digits {
            font-family: 'JetBrains Mono', monospace;
            font-size: 22px;
            font-weight: 800;
            letter-spacing: 2px;
            color: #047857;
        }

        .slip-actions-strip {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            align-items: center;
            justify-content: flex-end;
            margin-top: 18px;
            padding-top: 16px;
            border-top: 1px dashed #cbd5e1;
        }

        /* Tracking Spotlight */
        .spotlight-box {
            background: linear-gradient(135deg, #6d28d9, #5b21b6);
            color: #ffffff;
            border-radius: 12px;
            padding: 24px;
            margin-bottom: 28px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 16px;
            box-shadow: 0 8px 20px rgba(109, 40, 217, 0.25);
        }

        .spotlight-info .spotlight-title {
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: #ddd6fe;
            font-weight: 700;
            margin-bottom: 4px;
        }

        .spotlight-info .tracking-number {
            font-family: 'JetBrains Mono', monospace;
            font-size: 26px;
            font-weight: 800;
            letter-spacing: 0.05em;
            color: #ffffff;
        }

        .spotlight-info .spotlight-sub {
            font-size: 12.5px;
            color: #ede9fe;
            margin-top: 4px;
        }

        .btn-copy-id {
            background: #ffffff;
            color: #5b21b6;
            font-weight: 700;
            padding: 10px 18px;
            border-radius: 8px;
            font-size: 13.5px;
            border: none;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            transition: all 0.2s ease;
        }

        .btn-copy-id:hover {
            background: #f1f5f9;
            transform: translateY(-1px);
        }

        /* Details Table */
        .data-section-title {
            font-size: 14px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            color: #64748b;
            margin-bottom: 12px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .details-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 28px;
            border-radius: 10px;
            overflow: hidden;
            border: 1px solid #e2e8f0;
        }

        .details-table tr {
            border-bottom: 1px solid #f1f5f9;
        }
        .details-table tr:last-child {
            border-bottom: none;
        }

        .details-table th,
        .details-table td {
            padding: 12px 18px;
            font-size: 13.5px;
            text-align: left;
        }

        .details-table th {
            width: 35%;
            background: #f8fafc;
            color: #64748b;
            font-weight: 600;
        }

        .details-table td {
            background: #ffffff;
            color: #0f172a;
            font-weight: 600;
        }

        .details-table td code {
            font-family: 'JetBrains Mono', monospace;
            background: #f1f5f9;
            color: #0f172a;
            padding: 2px 6px;
            border-radius: 4px;
            font-size: 13px;
        }

        /* Note Box */
        .note-box {
            background: #f8fafc;
            border-left: 4px solid #8b5cf6;
            padding: 14px 18px;
            border-radius: 0 8px 8px 0;
            margin-bottom: 28px;
        }

        .note-box .note-label {
            font-size: 11.5px;
            text-transform: uppercase;
            font-weight: 700;
            color: #8b5cf6;
            margin-bottom: 4px;
            display: block;
        }

        .note-box .note-content {
            font-size: 13px;
            color: #334155;
            font-weight: 500;
            line-height: 1.5;
        }

        /* Footer */
        .cert-footer {
            border-top: 1px dashed #cbd5e1;
            padding-top: 24px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 20px;
        }

        .seal-group {
            display: flex;
            align-items: center;
            gap: 14px;
        }

        .stamp-badge {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            border: 2px dashed #8b5cf6;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            color: #8b5cf6;
            font-size: 9px;
            font-weight: 800;
            text-transform: uppercase;
            transform: rotate(-10deg);
        }
        .stamp-badge i {
            font-size: 14px;
            margin-bottom: 2px;
        }

        .seal-text {
            font-size: 11.5px;
            color: #64748b;
            line-height: 1.4;
            max-width: 320px;
        }

        .qr-box {
            text-align: right;
        }

        .qr-placeholder {
            display: inline-block;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            padding: 8px 12px;
            border-radius: 8px;
            font-family: 'JetBrains Mono', monospace;
            font-size: 11px;
            font-weight: 700;
            color: #475569;
            letter-spacing: 0.05em;
        }

        .cert-disclaimer {
            margin-top: 24px;
            text-align: center;
            font-size: 11px;
            color: #94a3b8;
            line-height: 1.4;
        }

        @media print {
            body { background: #ffffff !important; padding: 0 !important; }
            .action-bar, .slip-actions-strip, .btn-copy-id { display: none !important; }
            .cert-card { box-shadow: none !important; border: 1px solid #cbd5e1 !important; max-width: 100% !important; border-radius: 0 !important; }
            .spotlight-box { box-shadow: none !important; background: #6d28d9 !important; -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; }
            .slip-preview-card { box-shadow: none !important; border: 2px solid #008751 !important; -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; }
        }
    </style>
</head>
<body>

    <!-- On-Screen Navigation & Actions Bar -->
    <div class="action-bar">
        <a href="{{ route('services.personalization') }}" class="btn btn-back">
            <i class="fa-solid fa-arrow-left"></i> Back to Personalization
        </a>
        <div style="display: flex; gap: 8px; flex-wrap: wrap;">
            @if($isSuccess)
                <a href="{{ route('services.nin.slip', ['id' => $result->id, 'type' => 'regular_slip', 'print' => 1]) }}" target="_blank" class="btn btn-pdf">
                    <i class="fa-solid fa-print"></i> Print Regular Slip
                </a>
                <a href="{{ route('services.nin.slip', ['id' => $result->id, 'type' => 'premium_slip', 'print' => 1]) }}" target="_blank" class="btn btn-premium">
                    <i class="fa-solid fa-id-card"></i> Print Premium ID Slip
                </a>
                <a href="{{ route('services.nin.slip', ['id' => $result->id, 'type' => 'regular_slip', 'download' => 1]) }}" class="btn btn-print">
                    <i class="fa-solid fa-file-pdf"></i> Download PDF Slip
                </a>
            @elseif($isPending)
                <button type="button" class="btn btn-warning" id="checkStatusBtn" onclick="checkLiveStatus()">
                    <i class="fa-solid fa-arrows-rotate"></i> Check Gateway Status
                </button>
            @endif
            <button type="button" class="btn btn-back" onclick="window.print()">
                <i class="fa-solid fa-print"></i> Print Full Certificate
            </button>
        </div>
    </div>

    <!-- Official Certificate Card -->
    <div class="cert-card">
        <div class="cert-top-bar"></div>
        
        <div class="cert-inner">
            <!-- Header -->
            <div class="cert-header">
                <div class="cert-brand">
                    <div class="brand-logo-circle">
                        <i class="fa-solid fa-user-gear"></i>
                    </div>
                    <div class="cert-title">
                        <h1>{{ \App\Models\SystemSetting::get('site_name', 'Fuwa.NG') }} Identity Services</h1>
                        <p>NIMC Identity Personalization Certificate &amp; Verification Report</p>
                    </div>
                </div>
                <div class="cert-ref-badge">
                    <span class="ref-label">Reference ID</span>
                    <span class="ref-val">{{ $result->reference_id }}</span>
                </div>
            </div>

            <!-- Status Banner -->
            <div class="status-hero {{ $isSuccess ? 'success' : ($isPending ? 'pending' : 'failed') }}">
                <div class="status-hero-text">
                    @if($isSuccess)
                        <h2><i class="fa-solid fa-circle-check"></i> Identity Personalization Completed</h2>
                        <p>{{ $cleanApiMessage }} Profile configuration and National Identification Number are active on NIMC records.</p>
                    @elseif($isPending)
                        <h2><i class="fa-solid fa-spinner fa-spin"></i> Personalization In Progress</h2>
                        <p id="pendingText">Your personalization request was accepted by the provider and is currently being processed by the NIMC gateway. Auto-checking status...</p>
                    @else
                        <h2><i class="fa-solid fa-circle-xmark"></i> Personalization Failed</h2>
                        <p>{{ $cleanApiMessage }} (Per provider terms, tickets are billed on submission).</p>
                    @endif
                </div>
                <span class="status-tag {{ $isSuccess ? 'success' : ($isPending ? 'pending' : 'failed') }}">
                    {{ $isSuccess ? 'Completed / Active' : ($isPending ? 'Processing' : 'Failed') }}
                </span>
            </div>

            @if($isPending)
                <!-- Live Polling Progress Box -->
                <div class="pending-box">
                    <div class="pending-spinner">
                        <i class="fa-solid fa-circle-notch fa-spin"></i>
                    </div>
                    <h3 style="font-size: 16px; font-weight: 800; color: #92400e; margin-bottom: 6px;">Processing Identity Personalization</h3>
                    <p style="font-size: 13px; color: #78350f; max-width: 520px; margin: 0 auto 16px;">
                        The verification gateway has accepted your ticket and is generating your verified identity record and printable NIN slip. This page is live-polling and will automatically show your NIN slip as soon as completed.
                    </p>
                    <button type="button" class="btn btn-warning" onclick="checkLiveStatus()">
                        <i class="fa-solid fa-rotate mr-1"></i> Refresh Status Now
                    </button>
                </div>
            @endif

            @if($isSuccess)
                <!-- OFFICIAL PRINTABLE NIN SLIP RESULT (Expected Deliverable) -->
                <div class="data-section-title">
                    <i class="fa-solid fa-id-card-clip text-success"></i> Official Printable NIN Slip Result
                </div>

                <div class="slip-preview-card">
                    <div class="slip-preview-header">
                        <div class="slip-country">FEDERAL REPUBLIC OF NIGERIA</div>
                        <div class="slip-subtitle">NATIONAL IDENTITY MANAGEMENT COMMISSION • REGULAR NIN SLIP</div>
                    </div>

                    <div class="slip-grid">
                        <div class="slip-photo">
                            @if($photo)
                                <img src="{{ str_starts_with($photo, 'http') || str_starts_with($photo, 'data:') ? $photo : 'data:image/jpeg;base64,' . $photo }}" alt="NIN Photo">
                            @else
                                <i class="fa-solid fa-user" style="font-size: 48px; color: #cbd5e1;"></i>
                            @endif
                        </div>

                        <div class="slip-fields">
                            <div class="slip-field-item">
                                <label>Surname / Nom</label>
                                <span>{{ strtoupper($lastname ?: ($result->user?->fullname ? explode(' ', $result->user->fullname)[0] : '—')) }}</span>
                            </div>
                            <div class="slip-field-item">
                                <label>Given Names / Prénoms</label>
                                <span>{{ strtoupper(trim(($firstname ?: '') . ' ' . ($middlename ?: '')) ?: '—') }}</span>
                            </div>
                            <div class="slip-field-item">
                                <label>Date of Birth</label>
                                <span>{{ strtoupper($dob ?: '—') }}</span>
                            </div>
                            <div class="slip-field-item">
                                <label>Sex / Sexe</label>
                                <span>{{ $gender && $gender !== '—' ? strtoupper(substr($gender, 0, 1)) : '—' }}</span>
                            </div>
                            <div class="slip-field-item">
                                <label>Tracking ID</label>
                                <span style="font-family: 'JetBrains Mono', monospace;">{{ $result->identifier }}</span>
                            </div>
                            <div class="slip-field-item">
                                <label>Issue Date</label>
                                <span>{{ strtoupper($result->created_at->format('d M Y')) }}</span>
                            </div>

                            <div class="slip-nin-highlight">
                                <span class="nin-label"><i class="fa-solid fa-fingerprint mr-1"></i> National Identification Number (NIN)</span>
                                <span class="nin-digits">{{ $formattedNin }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="slip-actions-strip">
                        <a href="{{ route('services.nin.slip', ['id' => $result->id, 'type' => 'regular_slip', 'print' => 1]) }}" target="_blank" class="btn btn-pdf">
                            <i class="fa-solid fa-print"></i> Print Regular Slip
                        </a>
                        <a href="{{ route('services.nin.slip', ['id' => $result->id, 'type' => 'premium_slip', 'print' => 1]) }}" target="_blank" class="btn btn-premium">
                            <i class="fa-solid fa-id-card"></i> Print Premium ID Plastic Slip
                        </a>
                        <a href="{{ route('services.nin.slip', ['id' => $result->id, 'type' => 'regular_slip', 'download' => 1]) }}" class="btn btn-print">
                            <i class="fa-solid fa-download"></i> Download PDF Slip
                        </a>
                    </div>
                </div>
            @endif

            <!-- Tracking ID Spotlight Box -->
            <div class="spotlight-box">
                <div class="spotlight-info">
                    <div class="spotlight-title">Personalized NIMC Tracking ID</div>
                    <div class="tracking-number" id="spotlightTrackingId">{{ $result->identifier }}</div>
                    <div class="spotlight-sub">
                        @if($isSuccess)
                            Identity profile personalized. Use your Tracking ID or NIN above for official verifications.
                        @elseif($isPending)
                            Ticket accepted for processing. Status is being live-polled against the NIMC database.
                        @else
                            Personalization ticket recorded. Tracking ID: {{ $result->identifier }}.
                        @endif
                    </div>
                </div>
                <button type="button" class="btn-copy-id" onclick="copyTrackingId('{{ $result->identifier }}')">
                    <i class="fa-solid fa-copy"></i> Copy Tracking ID
                </button>
            </div>

            <!-- Application Details Grid -->
            <div class="data-section-title">
                <i class="fa-solid fa-list-check"></i> Personalization Record Details
            </div>
            <table class="details-table">
                <tbody>
                    <tr>
                        <th>Applicant Full Name</th>
                        <td>{{ $applicantName }}</td>
                    </tr>
                    @if($nin)
                        <tr>
                            <th>Generated / Active NIN</th>
                            <td><strong style="color: #059669; font-family: 'JetBrains Mono', monospace; font-size: 15px;">{{ $formattedNin }}</strong></td>
                        </tr>
                    @endif
                    <tr>
                        <th>NIMC Tracking ID</th>
                        <td><code>{{ $result->identifier }}</code></td>
                    </tr>
                    <tr>
                        <th>Service Type</th>
                        <td>NIMC Identity Personalization</td>
                    </tr>
                    <tr>
                        <th>System Reference</th>
                        <td><code>{{ $result->reference_id }}</code></td>
                    </tr>
                    <tr>
                        <th>Status</th>
                        <td>
                            @if($isSuccess)
                                <span style="color: #059669; font-weight: 700;"><i class="fa-solid fa-circle-check mr-1"></i> Active / Completed</span>
                            @elseif($isPending)
                                <span style="color: #d97706; font-weight: 700;"><i class="fa-solid fa-spinner fa-spin mr-1"></i> Processing via NIMC Gateway</span>
                            @else
                                <span style="color: #dc2626; font-weight: 700;"><i class="fa-solid fa-circle-xmark mr-1"></i> Failed</span>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <th>Timestamp</th>
                        <td>{{ $result->created_at->format('F d, Y - h:i A') }}</td>
                    </tr>
                </tbody>
            </table>

            <!-- Notes Box -->
            <div class="note-box">
                <span class="note-label"><i class="fa-solid fa-circle-info"></i> Gateway Confirmation:</span>
                <p class="note-content">
                    {{ $cleanApiMessage }} Personalization was processed through the official identity verification gateway.
                </p>
            </div>

            <!-- Certificate Verification Footer -->
            <div class="cert-footer">
                <div class="seal-group">
                    <div class="stamp-badge">
                        <i class="fa-solid fa-stamp"></i>
                        <span>VERIFIED</span>
                    </div>
                    <div class="seal-text">
                        <strong>Official Document Authenticity</strong><br>
                        Issued by {{ \App\Models\SystemSetting::get('site_name', 'Fuwa.NG') }} Identity Gateway. Validated against official national identity databases.
                    </div>
                </div>
                <div class="qr-box">
                    <span class="qr-placeholder">SECURE HASH: {{ strtoupper(substr(hash('sha256', $result->reference_id . $result->identifier), 0, 16)) }}</span>
                </div>
            </div>

            <!-- Footer Disclaimer -->
            <div class="cert-disclaimer">
                This report is electronically verified for identity record management. Reference ID {{ $result->reference_id }}.
            </div>
        </div>
    </div>

    <script>
        function copyTrackingId(val) {
            if (navigator.clipboard && navigator.clipboard.writeText) {
                navigator.clipboard.writeText(val).then(() => {
                    alert('Tracking ID copied: ' + val);
                }).catch(() => {
                    prompt('Copy Tracking ID:', val);
                });
            } else {
                prompt('Copy Tracking ID:', val);
            }
        }

        @if($isPending)
            let pollCount = 0;
            const maxPolls = 30; // poll up to 2.5 minutes
            let pollInterval = null;

            function checkLiveStatus() {
                const btn = document.getElementById('checkStatusBtn');
                if (btn) {
                    btn.disabled = true;
                    btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Checking...';
                }

                fetch('{{ route('services.personalization.verify') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        mode: 'status',
                        tracking_id: '{{ $result->identifier }}',
                        result_id: {{ $result->id }}
                    })
                })
                .then(res => res.json())
                .then(data => {
                    if (btn) {
                        btn.disabled = false;
                        btn.innerHTML = '<i class="fa-solid fa-arrows-rotate"></i> Check Gateway Status';
                    }

                    if (data.is_completed || data.request_status === 'completed') {
                        if (pollInterval) clearInterval(pollInterval);
                        const pText = document.getElementById('pendingText');
                        if (pText) pText.innerText = 'Personalization completed! Reloading with your NIN slip...';
                        setTimeout(() => {
                            window.location.reload();
                        }, 800);
                    } else if (data.is_failed || data.request_status === 'failed') {
                        if (pollInterval) clearInterval(pollInterval);
                        window.location.reload();
                    } else {
                        const pText = document.getElementById('pendingText');
                        if (pText) {
                            pText.innerText = 'Checked at ' + new Date().toLocaleTimeString() + ' — Request is still processing on NIMC gateway...';
                        }
                    }
                })
                .catch(err => {
                    if (btn) {
                        btn.disabled = false;
                        btn.innerHTML = '<i class="fa-solid fa-arrows-rotate"></i> Check Gateway Status';
                    }
                });
            }

            // Start auto-poll every 5 seconds
            pollInterval = setInterval(() => {
                pollCount++;
                if (pollCount > maxPolls) {
                    clearInterval(pollInterval);
                    const pText = document.getElementById('pendingText');
                    if (pText) {
                        pText.innerText = 'Still processing. You can check back shortly or click "Check Gateway Status" above.';
                    }
                    return;
                }
                checkLiveStatus();
            }, 5000);
        @endif

        @if(!empty($autoPrint) && $isSuccess)
            window.addEventListener('DOMContentLoaded', () => {
                setTimeout(() => { window.print(); }, 400);
            });
        @endif
    </script>
</body>
</html>
