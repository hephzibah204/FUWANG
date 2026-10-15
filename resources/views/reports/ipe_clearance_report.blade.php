<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NIMC IPE Clearance Certificate - {{ $result->reference_id }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    
    <style>
        :root {
            --primary: #059669;
            --primary-dark: #047857;
            --primary-light: #d1fae5;
            --warning: #f59e0b;
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
            background: #f59e0b;
            color: #0f172a;
        }
        .btn-suite:hover {
            background: #d97706;
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

        /* Top Tricolor Decorative Border */
        .cert-top-bar {
            height: 6px;
            background: linear-gradient(90deg, #059669 0%, #059669 35%, #ffffff 35%, #ffffff 65%, #059669 65%, #059669 100%);
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
            background: linear-gradient(135deg, #059669, #047857);
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            box-shadow: 0 4px 10px rgba(5, 150, 105, 0.3);
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
            color: #2563eb;
            display: block;
        }

        /* Status Banner */
        @php
            $d = $result->response_data ?? [];
            $status = $result->status ?? 'waiting_for_review';
            $newTrackingId = $d['new_tracking_id'] ?? null;
            $category = $d['category'] ?? 'Improcessing Error';
            $adminNote = $result->admin_note ?: ($d['admin_note'] ?? null);
            $applicantName = $d['user_name'] ?? ($result->user?->fullname ?? 'Applicant');
        @endphp

        .status-hero {
            border-radius: 12px;
            padding: 22px 24px;
            margin-bottom: 28px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
        }

        .status-hero.successful {
            background: linear-gradient(135deg, #ecfdf5 0%, #d1fae5 100%);
            border: 1px solid #a7f3d0;
        }

        .status-hero.waiting_for_review,
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

        .status-hero.successful .status-hero-text h2 { color: #065f46; }
        .status-hero.waiting_for_review .status-hero-text h2,
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

        .status-tag.successful {
            background: #059669;
            color: #ffffff;
        }

        .status-tag.waiting_for_review,
        .status-tag.pending {
            background: #f59e0b;
            color: #0f172a;
        }

        .status-tag.failed {
            background: #dc2626;
            color: #ffffff;
        }

        /* New Tracking ID Box */
        .spotlight-box {
            background: linear-gradient(135deg, #065f46, #047857);
            color: #ffffff;
            border-radius: 12px;
            padding: 24px;
            margin-bottom: 28px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 16px;
            box-shadow: 0 8px 20px rgba(5, 150, 105, 0.25);
        }

        .spotlight-info .spotlight-title {
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: #a7f3d0;
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
            color: #d1fae5;
            margin-top: 4px;
        }

        .btn-copy-id {
            background: #ffffff;
            color: #065f46;
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

        /* Remarks Note Box */
        .note-box {
            background: #f8fafc;
            border-left: 4px solid #3b82f6;
            padding: 14px 18px;
            border-radius: 0 8px 8px 0;
            margin-bottom: 28px;
        }

        .note-box .note-label {
            font-size: 11.5px;
            text-transform: uppercase;
            font-weight: 700;
            color: #3b82f6;
            margin-bottom: 4px;
            display: block;
        }

        .note-box .note-content {
            font-size: 13px;
            color: #334155;
            font-weight: 500;
            line-height: 1.5;
        }

        /* Official Seal & Signature Section */
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
            border: 2px dashed #059669;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            color: #059669;
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

        /* Print Media Styles */
        @media print {
            body {
                background: #ffffff !important;
                padding: 0 !important;
            }
            .action-bar {
                display: none !important;
            }
            .cert-card {
                box-shadow: none !important;
                border: 1px solid #cbd5e1 !important;
                max-width: 100% !important;
                border-radius: 0 !important;
            }
            .spotlight-box {
                box-shadow: none !important;
                background: #065f46 !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }
            .btn-copy-id {
                display: none !important;
            }
        }
    </style>
</head>
<body>

    <!-- On-Screen Navigation & Actions Bar -->
    <div class="action-bar">
        <a href="{{ route('services.clearance') }}" class="btn btn-back">
            <i class="fa-solid fa-arrow-left"></i> Back to Clearance Portal
        </a>
        <div style="display: flex; gap: 8px; flex-wrap: wrap;">
            <button type="button" class="btn btn-print" onclick="window.print()">
                <i class="fa-solid fa-print"></i> Print Certificate
            </button>
            <a href="{{ route('services.clearance.report', ['id' => $result->id, 'download' => 1]) }}" class="btn btn-pdf">
                <i class="fa-solid fa-file-pdf"></i> Download PDF
            </a>
            @if($status === 'successful')
                <a href="{{ route('services.personalization', ['tracking_id' => $newTrackingId ?: $result->identifier]) }}" class="btn" style="background: #8b5cf6; color: #fff;">
                    <i class="fa-solid fa-user-gear"></i> Proceed to Personalization
                </a>
                <a href="{{ route('services.nin.suite') }}" class="btn btn-suite">
                    <i class="fa-solid fa-id-card"></i> NIN Suite
                </a>
            @endif
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
                        <i class="fa-solid fa-shield-halved"></i>
                    </div>
                    <div class="cert-title">
                        <h1>{{ \App\Models\SystemSetting::get('site_name', 'Fuwa.NG') }} Identity Services</h1>
                        <p>NIMC IPE Clearance Certificate &amp; Verification Report</p>
                    </div>
                </div>
                <div class="cert-ref-badge">
                    <span class="ref-label">Reference ID</span>
                    <span class="ref-val">{{ $result->reference_id }}</span>
                </div>
            </div>

            <!-- Status Banner -->
            <div class="status-hero {{ $status }}">
                <div class="status-hero-text">
                    @if($status === 'successful')
                        <h2><i class="fa-solid fa-circle-check"></i> Clearance Completed Successfully</h2>
                        <p>The enrollment processing issue for this identity has been resolved. A new Tracking ID is active.</p>
                    @elseif($status === 'failed')
                        <h2><i class="fa-solid fa-circle-xmark"></i> Clearance Request Unsuccessful</h2>
                        <p>Application could not be cleared. A 100% refund has been credited back to the applicant's wallet.</p>
                    @else
                        <h2><i class="fa-solid fa-clock-rotate-left"></i> Clearance In Progress / Under Review</h2>
                        <p>Application has been received and is actively being vetted by the verification desk.</p>
                    @endif
                </div>
                <span class="status-tag {{ $status }}">
                    @if($status === 'successful')
                        Cleared &amp; Ready
                    @elseif($status === 'failed')
                        Rejected / Refunded
                    @else
                        Under Review
                    @endif
                </span>
            </div>

            <!-- Cleared / Newly Assigned Tracking ID Spotlight (Shown on success) -->
            @if($status === 'successful')
                <div class="spotlight-box">
                    <div class="spotlight-info">
                        <div class="spotlight-title">{{ !empty($newTrackingId) ? 'Newly Assigned NIMC Tracking ID' : 'Cleared NIMC Tracking ID' }}</div>
                        <div class="tracking-number" id="spotlightTrackingId">{{ $newTrackingId ?: $result->identifier }}</div>
                        <div class="spotlight-sub">
                            {{ !empty($newTrackingId) 
                                ? 'Your previous enrollment error was resolved with a new Tracking ID. Use this ID to retrieve your slip in the NIN Suite.' 
                                : 'Your enrollment record error has been cleared. You can now use your Tracking ID in the NIN Suite.' }}
                        </div>
                    </div>
                    <div style="display: flex; gap: 8px; align-items: center; flex-wrap: wrap;">
                        <button type="button" class="btn-copy-id" onclick="copyNewTrackingId('{{ $newTrackingId ?: $result->identifier }}')">
                            <i class="fa-solid fa-copy"></i> Copy Tracking ID
                        </button>
                        <a href="{{ route('services.personalization', ['tracking_id' => $newTrackingId ?: $result->identifier]) }}" class="btn-copy-id" style="color: #6d28d9; text-decoration: none;">
                            <i class="fa-solid fa-user-gear"></i> Personalize ID
                        </a>
                    </div>
                </div>
            @endif

            <!-- Application Details Grid -->
            <div class="data-section-title">
                <i class="fa-solid fa-list-check"></i> Clearance Record Details
            </div>
            <table class="details-table">
                <tbody>
                    <tr>
                        <th>Applicant Full Name</th>
                        <td>{{ $applicantName }}</td>
                    </tr>
                    <tr>
                        <th>Original Submitted Tracking ID</th>
                        <td><code>{{ $result->identifier }}</code></td>
                    </tr>
                    @if(!empty($newTrackingId))
                    <tr style="background: #f0fdf4;">
                        <th style="color: #065f46; font-weight: 700;">New Assigned Tracking ID</th>
                        <td><strong style="color: #059669; font-family: 'JetBrains Mono', monospace; font-size: 15px;">{{ $newTrackingId }}</strong></td>
                    </tr>
                    @endif
                    <tr>
                        <th>IPE Issue Category</th>
                        <td>{{ $category }}</td>
                    </tr>
                    <tr>
                        <th>Application Status</th>
                        <td>
                            @if($status === 'successful')
                                <span style="color: #059669; font-weight: 700;"><i class="fa-solid fa-check-circle"></i> Cleared / Ready</span>
                            @elseif($status === 'failed')
                                <span style="color: #dc2626; font-weight: 700;"><i class="fa-solid fa-times-circle"></i> Rejected / Refunded</span>
                            @else
                                <span style="color: #d97706; font-weight: 700;"><i class="fa-solid fa-clock"></i> In Progress (&lt; 24h)</span>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <th>Submission Timestamp</th>
                        <td>{{ $result->created_at->format('F d, Y - h:i A') }}</td>
                    </tr>
                    <tr>
                        <th>Last Updated</th>
                        <td>{{ $result->updated_at->format('F d, Y - h:i A') }}</td>
                    </tr>
                </tbody>
            </table>

            <!-- Clearance Notes Box -->
            <div class="note-box">
                <span class="note-label"><i class="fa-solid fa-circle-info"></i> Clearance Desk Notes:</span>
                <p class="note-content">
                    {{ $adminNote ?: ($status === 'successful' ? 'Clearance processed successfully. Your updated record is active on NIMC database.' : 'Awaiting verification desk review. Processing is typically completed in less than 24 hours.') }}
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
                This verification report is generated electronically for record and retrieval purposes. To verify this certificate, visit {{ config('app.url') }} and reference ID {{ $result->reference_id }}.
            </div>
        </div>
    </div>

    <script>
        function copyNewTrackingId(val) {
            if (navigator.clipboard && navigator.clipboard.writeText) {
                navigator.clipboard.writeText(val).then(() => {
                    alert('New Tracking ID copied: ' . val);
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
