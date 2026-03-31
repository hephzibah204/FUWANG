<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Sales Agreement - {{ $doc_number }}</title>
    <style>
        body { font-family: 'Helvetica', sans-serif; line-height: 1.6; color: #333; margin: 40px; }
        .header { text-align: center; border-bottom: 2px solid #10b981; padding-bottom: 20px; margin-bottom: 30px; }
        .doc-num { color: #10b981; font-weight: bold; }
        .watermark { position: fixed; top: 50%; left: 50%; transform: translate(-50%, -50%) rotate(-45deg); font-size: 80px; color: rgba(200, 200, 200, 0.2); z-index: -1; white-space: nowrap; pointer-events: none; }
        .stamp { position: absolute; bottom:SourcePath: 50px; right: 50px; width: 150px; }
        .section { margin-bottom: 20px; }
        .title { font-size: 24px; font-weight: bold; text-transform: uppercase; margin-bottom: 5px; }
        .date { font-style: italic; margin-bottom: 30px; }
        .parties { background: #f0fdf4; padding: 20px; border-radius: 8px; margin-bottom: 30px; }
        .signature-block { margin-top: 100px; display: table; width: 100%; }
        .sig-col { display: table-cell; width: 50%; }
        .sig-line { border-top: 1px solid #000; width: 200px; margin-top: 50px; }
    </style>
</head>
<body>
    <div class="watermark">NEXUS LEGAL HUB</div>

    <div class="header">
        <div class="title">Agreement of Sale</div>
        <div class="doc-num">REF: {{ $doc_number }}</div>
        <div class="date">Dated: {{ $date }}</div>
    </div>

    <div class="parties">
        <p>This Sales Agreement is entered into between:</p>
        <p><strong>Seller:</strong> {{ $seller_name }}</p>
        <p><strong>Buyer:</strong> {{ $buyer_name }}</p>
    </div>

    <div class="section">
        <h3>1. Sale of Goods/Asset</h3>
        <p>The Seller hereby agrees to sell, and the Buyer hereby agrees to purchase, the following item(s) (the "Asset"):</p>
        <p style="padding: 10px; background: #fff; border: 1px solid #ddd;">{{ $item_desc }}</p>
    </div>

    <div class="section">
        <h3>2. Purchase Price</h3>
        <p>The Buyer shall pay to the Seller the total sum of <strong>₦{{ number_format($sale_price, 2) }}</strong> as full and final payment for the Asset.</p>
    </div>

    <div class="section">
        <h3>3. Warranties</h3>
        <p>The Seller warrants that they have good and legal title to the Asset and that the Asset is sold free from all liens and encumbrances.</p>
    </div>

    <div class="signature-block">
        <div class="sig-col">
            <p><strong>Seller's Signature:</strong></p>
            <div class="sig-line"></div>
            <p>{{ $seller_name }}</p>
        </div>
        <div class="sig-col">
            <p><strong>Buyer's Signature:</strong></p>
            <div class="sig-line"></div>
            <p>{{ $buyer_name }}</p>
        </div>
    </div>

    @if(file_exists($stamp_url))
        <img src="{{ $stamp_url }}" class="stamp">
    @endif
</body>
</html>
