<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NIMC Identity Personalization Certificate - {{ $result->reference_id }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    
    <style>
        :root {
            --primary: #8b5cf6;
            --primary-dark: #7c3aed;
            --primary-light: #ede9fe;
            --success: #059669;
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
            max-width: 780px;
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
            font-size: 14px;
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

        .btn-suite {
            background: #8b5cf6;
            color: #fff;
        }
        .btn-suite:hover {
            background: #7c3aed;
        }

        /* Certificate Container */
        .cert-card {
            width: 100%;
            max-width: 780px;
            background: #ffffff;
            border-radius: 16px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.4);
            position: relative;
            overflow: hidden;
            border: 1px solid rgba(226, 232, 240, 0.8);
        }

        .cert-top-bar {
            height: 6px;
            background: linear-gradient(90deg, #8b5cf6 0%, #a78bfa 50%, #8b5cf6 100%);
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

        @php
            $d = $result->response_data ?? [];
            $status = $result->status ?? 'successful';
            $isSuccess = in_array(strtolower((string)$status), ['successful', 'success', 'cleared', 'active', 'completed']);
            $applicantName = $result->user?->fullname ?? 'Applicant';
            $apiMessage = $d['message'] ?? 'Identity record successfully personalized.';
        @endphp

        /* Status Hero */
        .status-hero {
            border-radius: 12px;
            padding: 22px 24px;
            margin-bottom: 28px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            background: linear-gradient(135deg, #f5f3ff 0%, #ede9fe 100%);
            border: 1px solid #ddd6fe;
        }

        .status-hero-text h2 {
            font-size: 18px;
            font-weight: 800;
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 4px;
            color: #5b21b6;
        }

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
            background: #7c3aed;
            color: #ffffff;
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
            .action-bar { display: none !important; }
            .cert-card { box-shadow: none !important; border: 1px solid #cbd5e1 !important; max-width: 100% !important; border-radius: 0 !important; }
            .spotlight-box { box-shadow: none !important; background: #6d28d9 !important; -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; }
            .btn-copy-id { display: none !important; }
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
            <button type="button" class="btn btn-print" onclick="window.print()">
                <i class="fa-solid fa-print"></i> Print Certificate
            </button>
            <a href="{{ route('services.personalization.report', ['id' => $result->id, 'download' => 1]) }}" class="btn btn-pdf">
                <i class="fa-solid fa-file-pdf"></i> Download PDF
            </a>
            <a href="{{ route('services.nin.suite') }}" class="btn btn-suite">
                <i class="fa-solid fa-id-card"></i> Proceed to NIN Suite
            </a>
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
            <div class="status-hero">
                <div class="status-hero-text">
                    <h2><i class="fa-solid fa-circle-check"></i> Identity Personalization Completed</h2>
                    <p>{{ $apiMessage }} The profile configuration has been committed to NIMC identity records.</p>
                </div>
                <span class="status-tag">
                    Personalized
                </span>
            </div>

            <!-- Tracking ID Spotlight Box -->
            <div class="spotlight-box">
                <div class="spotlight-info">
                    <div class="spotlight-title">Personalized NIMC Tracking ID</div>
                    <div class="tracking-number" id="spotlightTrackingId">{{ $result->identifier }}</div>
                    <div class="spotlight-sub">
                        Your identity profile has been successfully personalized. You can now retrieve your NIN slip.
                    </div>
                </div>
                <button type="button" class="btn-copy-id" onclick="copyTrackingId('{{ $result->identifier }}')">
                    <i class="fa-solid fa-copy"></i> Copy ID
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
                        <td><span style="color: #059669; font-weight: 700;"><i class="fa-solid fa-circle-check mr-1"></i> Active / Personalized</span></td>
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
                    {{ $apiMessage }} Personalization was processed through the official verification gateway. Use your Tracking ID in the NIN Suite to print your official slips.
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

        @if(!empty($autoPrint))
            window.addEventListener('DOMContentLoaded', () => {
                setTimeout(() => { window.print(); }, 400);
            });
        @endif
    </script>
</body>
</html>
