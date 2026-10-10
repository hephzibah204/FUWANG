<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Agent ID Card - {{ $code }}</title>
    <style>
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            margin: 0;
            padding: 20px;
            color: #1e293b;
            background: #ffffff;
        }
        .page-wrap {
            width: 100%;
            text-align: center;
        }
        .card-table {
            width: 320px;
            margin: 0 auto 30px auto;
            border-collapse: collapse;
            border: 2px solid #2563eb;
            background: #ffffff;
            border-radius: 12px;
            overflow: hidden;
        }
        .header-cell {
            background-color: #1e3a8a;
            color: #ffffff;
            padding: 10px;
            text-align: center;
        }
        .header-title {
            font-size: 13px;
            font-weight: bold;
            letter-spacing: 1px;
            text-transform: uppercase;
        }
        .header-sub {
            font-size: 9px;
            color: #93c5fd;
            margin-top: 2px;
        }
        .body-cell {
            padding: 15px;
            text-align: center;
        }
        .photo-box {
            width: 95px;
            height: 110px;
            border: 2px solid #38bdf8;
            margin: 0 auto 10px auto;
            border-radius: 6px;
            overflow: hidden;
            background: #f1f5f9;
        }
        .photo-img {
            width: 95px;
            height: 110px;
            object-fit: cover;
        }
        .agent-name {
            font-size: 14px;
            font-weight: bold;
            color: #0f172a;
            margin-bottom: 4px;
            text-transform: uppercase;
        }
        .agent-code-box {
            display: inline-block;
            background-color: #dbeafe;
            color: #1e40af;
            padding: 3px 10px;
            font-size: 11px;
            font-weight: bold;
            border-radius: 4px;
            margin-bottom: 12px;
            font-family: monospace;
        }
        .meta-table {
            width: 100%;
            font-size: 10px;
            text-align: left;
            margin-top: 5px;
            border-top: 1px solid #e2e8f0;
            padding-top: 8px;
        }
        .meta-table td {
            padding: 3px 0;
        }
        .meta-label {
            color: #64748b;
            font-size: 9px;
            text-transform: uppercase;
        }
        .meta-val {
            font-weight: bold;
            color: #1e293b;
        }
        .card-footer {
            background-color: #f8fafc;
            color: #64748b;
            font-size: 8px;
            padding: 6px;
            text-align: center;
            border-top: 1px solid #e2e8f0;
        }
        .back-magstripe {
            background-color: #000000;
            height: 35px;
            width: 100%;
            margin-top: 15px;
        }
        .qr-box {
            padding: 10px;
            text-align: center;
        }
        .qr-img {
            width: 90px;
            height: 90px;
        }
    </style>
</head>
<body>
    <div class="page-wrap">
        <!-- FRONT OF CARD -->
        <table class="card-table">
            <tr>
                <td class="header-cell">
                    <div class="header-title">FUWA PARTNER NETWORK</div>
                    <div class="header-sub">OFFICIAL ENROLLMENT AGENT CREDENTIAL</div>
                </td>
            </tr>
            <tr>
                <td class="body-cell">
                    <div class="photo-box">
                        @if($agent->picture_local_path)
                            <img src="{{ $agent->picture_local_path }}" class="photo-img" alt="Photo">
                        @else
                            <div style="padding-top: 40px; color: #94a3b8; font-size: 10px;">PASSPORT</div>
                        @endif
                    </div>
                    <div class="agent-name">{{ $agent->full_name }}</div>
                    <div class="agent-code-box">CODE: {{ $code }}</div>

                    <table class="meta-table">
                        <tr>
                            <td>
                                <div class="meta-label">State of Operation</div>
                                <div class="meta-val">{{ $agent->state ?? 'Federal Capital' }}</div>
                            </td>
                            <td>
                                <div class="meta-label">Terminal IMEI</div>
                                <div class="meta-val">{{ $agent->machine_imei ? substr($agent->machine_imei, 0, 8) . '...' : 'ASSIGNED' }}</div>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <div class="meta-label">Issued</div>
                                <div class="meta-val">{{ $issueDate }}</div>
                            </td>
                            <td>
                                <div class="meta-label">Expires</div>
                                <div class="meta-val" style="color: #b45309;">{{ $expiryDate }}</div>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
            <tr>
                <td class="card-footer">
                    PROPERTY OF FUWA.NG • AUTHORIZED NIMC ENROLLMENT OPERATIVE
                </td>
            </tr>
        </table>

        <!-- BACK OF CARD -->
        <table class="card-table">
            <tr>
                <td style="padding: 0;">
                    <div class="back-magstripe"></div>
                </td>
            </tr>
            <tr>
                <td class="body-cell">
                    <div style="font-size: 11px; font-weight: bold; color: #0f172a; margin-bottom: 4px;">SECURITY & VERIFICATION</div>
                    <div style="font-size: 9px; color: #64748b; line-height: 1.3; margin-bottom: 8px;">
                        Scan QR code to verify accredited status on the official Fuwa registry.
                    </div>

                    <div class="qr-box">
                        @if($qrCode)
                            <img src="{{ $qrCode }}" class="qr-img" alt="QR Code">
                        @endif
                        <div style="font-size: 8px; color: #64748b; margin-top: 2px;">SCAN TO VERIFY</div>
                    </div>

                    <div style="font-size: 8px; color: #64748b; border-top: 1px solid #e2e8f0; padding-top: 6px; text-align: justify;">
                        This document certifies that the bearer is a registered and vetted Enrollment Agent. Unauthorized possession or reproduction is prohibited by federal law.
                    </div>
                </td>
            </tr>
            <tr>
                <td class="card-footer">
                    TOLL FREE HOTLINE: 0800-FUWA-AGENT • WWW.FUWA.NG
                </td>
            </tr>
        </table>
    </div>
</body>
</html>
