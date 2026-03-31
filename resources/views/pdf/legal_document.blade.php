<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Legal Document - {{ $reference }}</title>
    <style>
        body {
            font-family: 'Times New Roman', Times, serif;
            line-height: 1.6;
            color: #1a1a1a;
            padding: 40px;
        }
        .header {
            text-align: center;
            margin-bottom: 50px;
            border-bottom: 2px solid #333;
            padding-bottom: 20px;
        }
        .doc-type {
            font-size: 24px;
            font-weight: bold;
            text-transform: uppercase;
            margin-bottom: 10px;
        }
        .reference {
            font-size: 14px;
            color: #666;
        }
        .content {
            margin-top: 30px;
            text-align: justify;
        }
        .footer {
            margin-top: 60px;
            padding-top: 20px;
            border-top: 1px solid #eee;
            font-size: 12px;
            color: #888;
        }
        .stamp-box {
            position: absolute;
            bottom: 100px;
            right: 50px;
            width: 150px;
            text-align: center;
        }
        .stamp-img {
            width: 120px;
            opacity: 0.8;
        }
        .watermark {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-45deg);
            font-size: 100px;
            color: rgba(0, 0, 0, 0.05);
            z-index: -1;
            font-weight: bold;
            text-transform: uppercase;
        }
        h1, h2, h3 { color: #000; }
    </style>
</head>
<body>
    @if(!isset($is_final) || !$is_final)
        <div class="watermark">DRAFT</div>
    @endif

    <div class="header">
        <div class="doc-type">Legal Document</div>
        <div class="reference">Ref: {{ $reference }} | Date: {{ $date }}</div>
    </div>

    <div class="content">
        {!! $content !!}
    </div>

    @if(isset($is_final) && $is_final)
    <div class="stamp-box">
        @if(!empty($stamp_url) && file_exists($stamp_url))
            <img src="data:image/png;base64,{{ base64_encode(file_get_contents($stamp_url)) }}" class="stamp-img">
        @endif
        <div style="font-size: 10px; margin-top: 5px; color: #4f46e5; font-weight: bold;">CERTIFIED DIGITAL SEAL</div>
        <div style="font-size: 8px; color: #666;">Fuwa.NG Legal Hub</div>
    </div>
    @endif

    <div class="footer">
        <p>This document was electronically generated and certified via the Fuwa.NG AI Legal Hub.</p>
        <p>Verification Link: {{ url('/verify-document/' . $reference) }}</p>
    </div>
</body>
</html>
