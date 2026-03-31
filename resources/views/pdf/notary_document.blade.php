<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $reference }}</title>
    <style>
        body { font-family: 'Times New Roman', serif; color: #333; line-height: 1.6; padding: 40px; }
        .header { text-align: center; margin-bottom: 40px; border-bottom: 2px solid #333; padding-bottom: 20px; }
        .header h1 { margin: 0; font-size: 24px; text-transform: uppercase; }
        .header p { margin: 5px 0; font-size: 14px; color: #666; }
        
        .content { margin-bottom: 60px; min-height: 600px; position: relative; }
        .legal-text { font-size: 14px; text-align: justify; }
        .legal-text h1 { text-align: center; font-size: 18px; text-decoration: underline; margin-bottom: 25px; }
        
        .footer { margin-top: 50px; }
        .signature-section { display: flex; justify-content: space-between; align-items: flex-end; margin-top: 40px; }
        .sig-block { width: 45%; text-align: center; border-top: 1px solid #333; padding-top: 10px; font-size: 12px; }
        
        .stamp-container { position: absolute; bottom: 100px; right: 40px; text-align: center; }
        .org-stamp { width: 150px; opacity: 0.9; }
        .org-signature { width: 180px; position: absolute; bottom: 80px; }

        .watermark {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-45deg);
            font-size: 100px;
            color: rgba(0, 0, 0, 0.05);
            font-weight: bold;
            z-index: -1;
            text-transform: uppercase;
        }

        .meta-info { font-style: italic; color: #888; font-size: 10px; margin-top: 20px; }
    </style>
</head>
<body>
    @if($isDraft)
        <div class="watermark">DRAFT</div>
    @endif

    <div class="header">
        <h1>{{ config('app.name') }}</h1>
        <p>Legal Services & Notary Department</p>
        <p>Reference: {{ $reference }} | Date: {{ $date }}</p>
    </div>

    <div class="content">
        <div class="legal-text">
            {!! $content !!}
        </div>

        @if(!$isDraft)
            <div class="stamp-container">
                @if($stamp_path)
                    <img src="{{ public_path($stamp_path) }}" class="org-stamp">
                @endif
                <p style="font-size: 10px; color: #333; margin-top: 5px;">
                    Digitally Signed & Sealed<br>
                    {{ $date }}
                </p>
            </div>
            
            @if($signature_path)
                <img src="{{ public_path($signature_path) }}" class="org-signature" style="left: 40px;">
            @elseif(!empty($signature_text))
                <div class="org-signature" style="left: 40px; width: 180px; bottom: 90px; text-align: left; font-style: italic; color: #111;">
                    {{ $signature_text }}
                </div>
            @endif
        @endif
    </div>

    <div class="footer">
        <div class="signature-section">
            <div class="sig-block">Principal Involved Signature</div>
            <div class="sig-block">Authorized Signatory / Notary</div>
        </div>
        <div class="meta-info">
            This document was generated and certified by {{ config('app.name') }} Notary Services. 
            Verification ID: {{ $reference }}. Visit {{ config('app.url') }}/verify to validate.
        </div>
    </div>
</body>
</html>
