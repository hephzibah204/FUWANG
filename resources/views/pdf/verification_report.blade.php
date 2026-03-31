<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Verification Report - {{ $result->reference_id }}</title>
    <style>
        body { font-family: 'Helvetica', 'Arial', sans-serif; color: #333; line-height: 1.5; font-size: 13px; margin: 0; padding: 0; }
        .header { text-align: center; border-bottom: 2px solid #3b82f6; padding-bottom: 20px; margin-bottom: 30px; }
        .logo { font-size: 24px; font-weight: bold; color: #3b82f6; margin-bottom: 5px; }
        .report-title { font-size: 18px; color: #666; text-transform: uppercase; letter-spacing: 1px; }
        .meta-section { margin-bottom: 30px; }
        .meta-table { width: 100%; border-collapse: collapse; }
        .meta-table td { padding: 8px 0; border-bottom: 1px solid #eee; }
        .label { font-weight: bold; color: #777; width: 30%; }
        .value { color: #000; }
        .result-section { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 25px; margin-bottom: 30px; }
        .result-title { font-size: 16px; font-weight: bold; color: #1e293b; margin-bottom: 20px; border-bottom: 1px solid #cbd5e1; padding-bottom: 10px; }
        .data-grid { display: block; }
        .data-row { margin-bottom: 15px; border-bottom: 1px solid #f1f5f9; padding-bottom: 5px; }
        .data-label { font-size: 11px; color: #64748b; text-transform: uppercase; font-weight: bold; }
        .data-value { font-size: 14px; color: #0f172a; font-weight: 500; }
        .footer { text-align: center; font-size: 11px; color: #94a3b8; margin-top: 50px; border-top: 1px solid #e2e8f0; padding-top: 20px; }
        .status-badge { display: inline-block; padding: 4px 12px; border-radius: 20px; font-size: 11px; font-weight: bold; text-transform: uppercase; }
        .status-success { background: #dcfce7; color: #166534; }
        .qr-placeholder { float: right; width: 80px; height: 80px; background: #eee; border: 1px solid #ccc; text-align: center; line-height: 80px; font-size: 10px; color: #888; }
    </style>
</head>
<body>
    <div class="header">
        <div class="logo">G-SOFT VERIFY</div>
        <div class="report-title">Official Verification Certificate</div>
    </div>

    <div class="meta-section">
        <div class="qr-placeholder">VERIFIED</div>
        <table class="meta-table">
            <tr>
                <td class="label">Reference ID</td>
                <td class="value"><strong>{{ $result->reference_id }}</strong></td>
            </tr>
            <tr>
                <td class="label">Service Type</td>
                <td class="value">{{ strtoupper(str_replace('_', ' ', $result->service_type)) }}</td>
            </tr>
            <tr>
                <td class="label">Date Verified</td>
                <td class="value">{{ $result->created_at->format('F d, Y - H:i:s') }}</td>
            </tr>
            <tr>
                <td class="label">Status</td>
                <td class="value"><span class="status-badge status-success">VERIFIED</span></td>
            </tr>
        </table>
    </div>

    <div class="result-section">
        <div class="result-title">Verification Details</div>
        <div class="data-grid">
            @foreach($result->response_data as $key => $value)
                @if(!is_array($value))
                    <div class="data-row">
                        <div class="data-label">{{ strtoupper(str_replace('_', ' ', $key)) }}</div>
                        <div class="data-value">{{ $value ?: 'N/A' }}</div>
                    </div>
                @endif
            @endforeach
        </div>
    </div>

    <div class="footer">
        <p>This document is an official verification report from {{ \App\Models\SystemSetting::get('site_name', 'Fuwa.NG') }} Verify. To verify the authenticity of this document, visit our portal and enter the Reference ID above.</p>
        <p>&copy; {{ date('Y') }} {{ \App\Models\SystemSetting::get('site_name', 'Fuwa.NG') }}. All rights reserved.</p>
    </div>
</body>
</html>
