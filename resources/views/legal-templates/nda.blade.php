<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>NDA - {{ $doc_number }}</title>
    <style>
        body { font-family: 'Helvetica', sans-serif; line-height: 1.6; color: #333; margin: 40px; }
        .header { text-align: center; border-bottom: 2px solid #3b82f6; padding-bottom: 20px; margin-bottom: 30px; }
        .doc-num { color: #3b82f6; font-weight: bold; }
        .watermark { position: fixed; top: 50%; left: 50%; transform: translate(-SourcePath: 50%, -50%) rotate(-45deg); font-size: 80px; color: rgba(200, 200, 200, 0.2); z-index: -1; white-space: nowrap; pointer-events: none; }
        .stamp { position: absolute; bottom: 50px; right: 50px; width: 150px; }
        .section { margin-bottom: 20px; }
        .title { font-size: 24px; font-weight: bold; text-transform: uppercase; margin-bottom: 5px; }
        .date { font-style: italic; margin-bottom: 30px; }
        .parties { background: #f9fafb; padding: 20px; border-radius: 8px; margin-bottom: 30px; }
        .signature-block { margin-top: 100px; display: table; width: 100%; }
        .sig-col { display: table-cell; width: 50%; }
        .sig-line { border-top: 1px solid #000; width: 200px; margin-top: 50px; }
    </style>
</head>
<body>
    <div class="watermark">NEXUS LEGAL HUB</div>

    <div class="header">
        <div class="title">Non-Disclosure Agreement</div>
        <div class="doc-num">REF: {{ $doc_number }}</div>
        <div class="date">Executed on: {{ $date }}</div>
    </div>

    <div class="parties">
        <p>This Non-Disclosure Agreement (the "Agreement") is entered into between:</p>
        <p><strong>Disclosing Party:</strong> {{ $disclosing_party }}</p>
        <p><strong>Receiving Party:</strong> {{ $receiving_party }}</p>
    </div>

    <div class="section">
        <h3>1. Purpose</h3>
        <p>The parties wish to explore a business opportunity of mutual interest (the "Purpose"): {{ $purpose }}. In connection with the Purpose, the Disclosing Party may disclose to the Receiving Party certain confidential technical and business information which the Disclosing Party desires the Receiving Party to treat as confidential.</p>
    </div>

    <div class="section">
        <h3>2. Confidential Information</h3>
        <p>"Confidential Information" means any information disclosed by Disclosing Party to Receiving Party either directly or indirectly, in writing, orally or by inspection of tangible objects.</p>
    </div>

    <div class="section">
        <h3>3. Non-Use and Non-Disclosure</h3>
        <p>Receiving Party shall not use any Confidential Information for any purpose except to evaluate and engage in discussions concerning the Purpose. Receiving Party shall not disclose any Confidential Information to third parties.</p>
    </div>

    <div class="section">
        <h3>4. Term</h3>
        <p>The obligations of Receiving Party shall terminate after {{ $term }} years from the date of disclosure.</p>
    </div>

    <div class="signature-block">
        <div class="sig-col">
            <p><strong>For Disclosing Party:</strong></p>
            <div class="sig-line"></div>
            <p>{{ $disclosing_party }}</p>
        </div>
        <div class="sig-col">
            <p><strong>For Receiving Party:</strong></p>
            <div class="sig-line"></div>
            <p>{{ $receiving_party }}</p>
        </div>
    </div>

    @if(file_exists($stamp_url))
        <img src="{{ $stamp_url }}" class="stamp">
    @endif
</body>
</html>
