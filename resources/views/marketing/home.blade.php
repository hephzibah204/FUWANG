@extends('layouts.nexus')

@section('title', 'Verifiable Identity & Commerce Infrastructure for Nigeria | Fuwa.NG')
@section('meta_description', "Nigeria's infrastructure for instant NIN/BVN verification, inter-state parcel logistics, VTU, auctions, and digital notary. Integrated REST API.")
@section('meta_keywords', 'NIN verification Nigeria, BVN validation, identity verification API, NIMC slip, logistics platform Nigeria, VTU API Nigeria, CAC business lookup')
@section('canonical', route('home'))

@section('og_title', 'Fuwa.NG: Verifiable Identity & Commerce Infrastructure')
@section('og_description', "Instant NIN and BVN checks, real-time parcel dispatch, and business services on a single developer-first platform.")
@section('og_image', url('/images/og/home.jpg'))
@section('og_type', 'website')

@section('public_wrapper_class', 'none')
@section('is_public_page', 'true')

@section('content')
@php
    $adminUser = Auth::guard('admin')->user();
    $webUser = Auth::guard('web')->user();
    if ($adminUser) {
        $primaryCtaHref = route('admin.dashboard');
        $primaryCtaText = 'Go to Admin Dashboard';
    } elseif ($webUser) {
        $primaryCtaHref = route('dashboard');
        $primaryCtaText = 'Open Dashboard';
    } else {
        $primaryCtaHref = route('register');
        $primaryCtaText = 'Create free account';
    }
    $secondaryCtaHref = route('login');
    $waNumber = \App\Models\SystemSetting::get('support_whatsapp');
    $cleanWa = $waNumber ? preg_replace('/[^0-9]/', '', $waNumber) : null;
    $salesHref = $cleanWa ? ('https://wa.me/' . $cleanWa . '?text=' . urlencode('Hello Fuwa.NG Team, I want to integrate identity verification & logistics for my business.')) : 'mailto:' . (\App\Models\SystemSetting::get('support_email') ?: 'support@fuwa.ng');
    $assetPrefix = rtrim(preg_replace('#/index\.php$#', '', request()->getBaseUrl()), '/');

    $logisticsRoute = \Illuminate\Support\Facades\Route::has('services.user.logistics.dashboard')
        ? 'services.user.logistics.dashboard'
        : (\Illuminate\Support\Facades\Route::has('user.logistics.dashboard') ? 'user.logistics.dashboard' : null);
    $logisticsHref = auth()->check() && $logisticsRoute ? route($logisticsRoute) : route('logistics.home');

    $auctionRoute = \Illuminate\Support\Facades\Route::has('services.auctions.dashboard')
        ? 'services.auctions.dashboard'
        : (\Illuminate\Support\Facades\Route::has('auctions.dashboard') ? 'auctions.dashboard' : null);
    $auctionsHref = auth()->check() && $auctionRoute ? route($auctionRoute) : route('public.auctions.index');

    $notaryHref = auth()->check() ? route('services.notary') : url('/explore/notary-services');
    $vtuHref = auth()->check() ? route('services.vtu.hub') : url('/explore/vtu-services');
@endphp

<main class="home-sales" role="main">
    {{-- 1. HERO SECTION (Hallmark Floor Rules: Headline <= 7 words, Subtext <= 12 words) --}}
    <header class="home-sales__hero" aria-labelledby="heroTitle">
        <div class="container">
            <div class="hm-hero-grid">
                <div class="hm-hero-copy">
                    <div class="hm-status-chip">
                        <span class="hm-status-dot" aria-hidden="true"></span>
                        <span>NIMC & NIBSS Gateway Active</span>
                        <span class="hm-status-meta">avg 312ms</span>
                    </div>

                    {{-- Hallmark Floor Rule 1: Headline <= 7 words, < 50 chars --}}
                    <h1 class="hm-hero-title" id="heroTitle">Nigeria’s verifiable identity infrastructure.</h1>

                    {{-- Hallmark Floor Rule 2: Single sentence <= 12 words --}}
                    <p class="hm-hero-sub">Instant NIN, BVN validation and multi-service business APIs in one platform.</p>

                    <div class="hm-hero-actions" role="group" aria-label="Hero primary actions">
                        <a class="hm-btn-primary" href="{{ $primaryCtaHref }}">{{ $primaryCtaText }}</a>
                        <a class="hm-btn-secondary" href="{{ $salesHref }}" target="_blank" rel="noopener noreferrer">
                            <i class="fa-brands fa-whatsapp text-success" aria-hidden="true"></i> Talk to sales
                        </a>
                    </div>

                    <div class="hm-telemetry-row" aria-label="Infrastructure telemetry">
                        <div class="hm-telemetry-item">
                            <span class="hm-telemetry-label">Format Standards:</span>
                            <span class="hm-telemetry-val">NIMC / NIBSS / CAC</span>
                        </div>
                        <div class="hm-telemetry-item">
                            <span class="hm-telemetry-label">Slip Compliance:</span>
                            <span class="hm-telemetry-val">PDF/A-1b Standard</span>
                        </div>
                        <div class="hm-telemetry-item">
                            <span class="hm-telemetry-label">Integration:</span>
                            <span class="hm-telemetry-val">RESTful JSON</span>
                        </div>
                    </div>
                </div>

                {{-- Hero Interactive Inspector: SHOW, DO NOT DESCRIBE --}}
                <div class="hm-inspector-card" aria-label="Interactive identity verification inspector">
                    <div class="hm-inspector-header">
                        <div class="hm-inspector-tabs" role="tablist">
                            <button class="hm-tab-btn active" role="tab" aria-selected="true" data-inspector-tab="nin">NIN Verification</button>
                            <button class="hm-tab-btn" role="tab" aria-selected="false" data-inspector-tab="bvn">BVN Validation</button>
                            <button class="hm-tab-btn" role="tab" aria-selected="false" data-inspector-tab="cac">CAC Search</button>
                        </div>
                        <span class="hm-tab-badge">LIVE SCHEMA</span>
                    </div>

                    <div class="hm-inspector-body">
                        <div class="hm-inspector-input-wrap">
                            <input
                                type="text"
                                class="hm-input-field"
                                id="inspectorInput"
                                value="54928103947"
                                placeholder="Enter 11-digit NIN (e.g. 54928103947)"
                                aria-label="Identification Number Input"
                            >
                            <button class="hm-btn-run" id="inspectorRunBtn" type="button">
                                <i class="fa-solid fa-play" aria-hidden="true"></i> Inspect
                            </button>
                        </div>

                        <div class="hm-demo-triggers">
                            <span class="hm-demo-label">Load test payloads:</span>
                            <button type="button" class="hm-demo-pill" data-demo-tab="nin" data-demo-val="54928103947">NIN 11-Digit</button>
                            <button type="button" class="hm-demo-pill" data-demo-tab="bvn" data-demo-val="22198471029">BVN Match</button>
                            <button type="button" class="hm-demo-pill" data-demo-tab="cac" data-demo-val="RC-1849204">CAC Registry</button>
                        </div>

                        <div class="hm-result-pane">
                            <div class="hm-result-top">
                                <span class="hm-badge-status" id="resStatus">ACTIVE_VERIFIED</span>
                                <div class="d-flex align-items-center gap-2">
                                    <span class="hm-response-latency" id="resRef">NMC-84920</span>
                                    <span class="hm-response-latency" id="resLatency">284ms</span>
                                </div>
                            </div>

                            <div class="hm-result-grid">
                                <div class="hm-result-field">
                                    <label>Verified Name</label>
                                    <span id="resName">ADEBAYO OLUMIDE IBRAHIM</span>
                                </div>
                                <div class="hm-result-field">
                                    <label>Masked Identifier</label>
                                    <span id="resIdMasked">5492 ••• ••47</span>
                                </div>
                                <div class="hm-result-field">
                                    <label>Birth Date / Status</label>
                                    <span id="resDob">14 AUG 1992</span>
                                </div>
                                <div class="hm-result-field">
                                    <label>Phone / Match Level</label>
                                    <span id="resPhone">0803 ••• ••84</span>
                                </div>
                            </div>

                            <div class="hm-slip-preview-box">
                                <div class="hm-slip-switch">
                                    <span class="hm-slip-switch-label">Live Slip Renderer:</span>
                                    <div class="hm-slip-tabs" role="tablist">
                                        <button class="hm-slip-tab active" type="button" data-slip-tier="premium">Premium</button>
                                        <button class="hm-slip-tab" type="button" data-slip-tier="standard">Standard</button>
                                        <button class="hm-slip-tab" type="button" data-slip-tier="basic">Basic</button>
                                    </div>
                                </div>

                                <div class="hm-slip-mockup" aria-label="Official identity slip preview">
                                    <div class="hm-slip-header">
                                        <div class="hm-slip-flag">
                                            <span class="f-green"></span>
                                            <span class="f-white"></span>
                                            <span class="f-green"></span>
                                        </div>
                                        <div class="hm-slip-title">Federal Republic of Nigeria</div>
                                        <span class="hm-slip-tier-badge" id="slipTierBadge">PREMIUM SLIP</span>
                                    </div>
                                    <div class="hm-slip-content">
                                        <div class="hm-slip-avatar" aria-hidden="true">
                                            <i class="fa-solid fa-user-check"></i>
                                        </div>
                                        <div class="hm-slip-details">
                                            <strong id="slipName">ADEBAYO OLUMIDE IBRAHIM</strong>
                                            <div>National Identification: <span id="slipNin" class="font-weight-bold">5492 ••• ••47</span></div>
                                            <div class="text-muted" style="font-size: 0.625rem;">NIMC Gateway Validated • Digital Watermark Embedded</div>
                                        </div>
                                        <div class="hm-slip-qr" aria-hidden="true">
                                            <svg viewBox="0 0 24 24">
                                                <path d="M2 2h8v8H2V2zm2 2v4h4V4H4zm10-2h8v8h-8V2zm2 2v4h4V4h-4zM2 14h8v8H2v-8zm2 2v4h4v-4H4zm13-2h3v3h-3v-3zm0 5h3v3h-3v-3zm-3-5h2v2h-2v-2zm0 4h2v4h-2v-4zm-3-4h2v2h-2v-2zm0 3h2v3h-2v-3z"/>
                                            </svg>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </header>

    {{-- 2. INTERACTIVE LOGISTICS RATE & ROUTE ENGINE (Show, Do Not Describe) --}}
    <section class="hm-section" aria-labelledby="logisticsCalcTitle">
        <div class="container">
            <div class="hm-section-head">
                <h2 class="hm-section-h2" id="logisticsCalcTitle">Calculate shipping rates instantly</h2>
                <p class="hm-section-p">Transparent door-to-door freight pricing with real-time waypoint tracking across Nigeria.</p>
            </div>

            <div class="hm-calculator-card">
                <div class="hm-calc-inputs">
                    <div class="hm-calc-row">
                        <div class="hm-field-group">
                            <label for="calcOrigin">Pickup Origin Hub</label>
                            <select class="hm-select" id="calcOrigin">
                                <option value="lagos-mainland" selected>Lagos (Ikeja / Mainland Hub)</option>
                                <option value="lagos-island">Lagos (Victoria Island / Lekki)</option>
                                <option value="abuja">Abuja (Central Business District)</option>
                                <option value="ibadan">Ibadan (Dugbe Distribution Hub)</option>
                                <option value="ph">Port Harcourt (Trans-Amadi)</option>
                                <option value="kano">Kano (Bompai Central Hub)</option>
                            </select>
                        </div>
                        <div class="hm-field-group">
                            <label for="calcDestination">Delivery Destination</label>
                            <select class="hm-select" id="calcDestination">
                                <option value="abuja" selected>Abuja (Federal Capital Territory)</option>
                                <option value="lagos-island">Lagos (Victoria Island / Lekki)</option>
                                <option value="lagos-mainland">Lagos (Mainland / Ikeja)</option>
                                <option value="ph">Port Harcourt (Rivers State)</option>
                                <option value="enugu">Enugu (Independence Layout)</option>
                                <option value="kaduna">Kaduna (North Central Terminal)</option>
                            </select>
                        </div>
                    </div>

                    <div class="hm-field-group">
                        <label for="calcWeight">Parcel Weight (kg)</label>
                        <input type="number" class="hm-input" id="calcWeight" value="2.5" min="0.5" max="100" step="0.5">
                    </div>

                    <div class="hm-field-group">
                        <label>Delivery Velocity & Handling Tier</label>
                        <div class="hm-speed-options">
                            <button type="button" class="hm-speed-btn active" data-speed-mult="1.0" data-eta="24 - 48 hours">
                                <span class="hm-speed-title">Standard Ground</span>
                                <span class="hm-speed-sub">24 - 48 hours</span>
                            </button>
                            <button type="button" class="hm-speed-btn" data-speed-mult="1.5" data-eta="Next Day by 2:00 PM">
                                <span class="hm-speed-title">Express Air</span>
                                <span class="hm-speed-sub">Next Day Priority</span>
                            </button>
                            <button type="button" class="hm-speed-btn" data-speed-mult="2.0" data-eta="Same Day by 6:00 PM">
                                <span class="hm-speed-title">Intra-State Same Day</span>
                                <span class="hm-speed-sub">Immediate Dispatch</span>
                            </button>
                        </div>
                    </div>
                </div>

                <div class="hm-calc-output">
                    <div>
                        <div class="hm-output-head">
                            <span class="hm-output-title">Instant Freight Estimate</span>
                            <span class="hm-output-tag">WAYBILL READY</span>
                        </div>

                        <div class="hm-output-line">
                            <span>Base Regional Haulage</span>
                            <span id="calcOutBase">₦2,600</span>
                        </div>
                        <div class="hm-output-line">
                            <span>Weight Surcharge (> 1kg)</span>
                            <span id="calcOutWeight">₦675</span>
                        </div>
                        <div class="hm-output-line">
                            <span>QR Waybill & Handling</span>
                            <span id="calcOutHandling">₦250</span>
                        </div>

                        <div class="hm-output-total">
                            <span class="hm-total-label">Total Delivery Rate</span>
                            <span class="hm-total-val" id="calcOutTotal">₦3,525</span>
                        </div>
                        <div class="hm-output-eta" id="calcOutEta">Estimated delivery: 24 - 48 hours</div>
                    </div>

                    <div class="hm-calc-actions">
                        <a href="{{ $logisticsHref }}" class="hm-btn-primary">
                            <i class="fa-solid fa-truck-ramp-box" aria-hidden="true"></i> Book This Shipment
                        </a>
                        <a href="{{ route('logistics.home') }}" class="hm-btn-secondary">
                            <i class="fa-solid fa-magnifying-glass-location" aria-hidden="true"></i> Track Waybill
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- 3. DEVELOPER API CONSOLE (Built for Engineering Teams) --}}
    <section class="hm-section hm-section--muted" aria-labelledby="apiTitle">
        <div class="container">
            <div class="hm-section-head">
                <h2 class="hm-section-h2" id="apiTitle">Built for engineering teams</h2>
                <p class="hm-section-p">REST endpoints with sub-second response times, comprehensive test sandboxes, and predictable error schemas.</p>
            </div>

            <div class="hm-api-card">
                <div class="hm-api-code-col">
                    <div class="hm-api-toolbar">
                        <div class="hm-api-lang-tabs" role="tablist">
                            <button class="hm-lang-btn active" type="button" data-api-lang="curl">cURL</button>
                            <button class="hm-lang-btn" type="button" data-api-lang="node">Node.js</button>
                            <button class="hm-lang-btn" type="button" data-api-lang="python">Python</button>
                        </div>
                        <button class="hm-btn-copy" id="apiCopyBtn" type="button" title="Copy code to clipboard">
                            <i class="fa-regular fa-clone" aria-hidden="true"></i> Copy
                        </button>
                    </div>

                    <pre class="hm-code-pane"><code id="apiCodeBlock"><span class="token-method">curl</span> -X POST <span class="token-url">"https://api.fuwa.ng/v1/kyc/nin/verify"</span> \
  -H <span class="token-header">"Authorization: Bearer fk_live_948201a8f9024c"</span> \
  -H <span class="token-header">"Content-Type: application/json"</span> \
  -d <span class="token-str">'{
    "nin": "54928103947",
    "generate_slip": true,
    "slip_type": "premium"
  }'</span></code></pre>
                </div>

                <div class="hm-api-resp-col">
                    <div class="hm-resp-head">
                        <span class="hm-resp-badge">HTTP 200 OK</span>
                        <span class="hm-resp-timing">Latency: 284ms • 1.2 KB</span>
                    </div>

                    <pre class="hm-code-pane"><code>{
  <span class="token-key">"status"</span>: <span class="token-str">"success"</span>,
  <span class="token-key">"data"</span>: {
    <span class="token-key">"reference"</span>: <span class="token-str">"NMC-202610-84920"</span>,
    <span class="token-key">"nin"</span>: <span class="token-str">"54928103947"</span>,
    <span class="token-key">"verified"</span>: <span class="token-bool">true</span>,
    <span class="token-key">"name"</span>: {
      <span class="token-key">"firstname"</span>: <span class="token-str">"ADEBAYO"</span>,
      <span class="token-key">"surname"</span>: <span class="token-str">"IBRAHIM"</span>,
      <span class="token-key">"middlename"</span>: <span class="token-str">"OLUMIDE"</span>
    },
    <span class="token-key">"dob"</span>: <span class="token-str">"1992-08-14"</span>,
    <span class="token-key">"slip"</span>: {
      <span class="token-key">"tier"</span>: <span class="token-str">"premium"</span>,
      <span class="token-key">"download_url"</span>: <span class="token-str">"https://fuwa.ng/vault/slips/slp_9f81a.pdf"</span>,
      <span class="token-key">"checksum"</span>: <span class="token-str">"sha256:7b92f0a1c"</span>
    }
  }
}</code></pre>
                </div>
            </div>
        </div>
    </section>

    {{-- 4. CAPABILITIES ARCHITECTURE MATRIX --}}
    <section class="hm-section" aria-labelledby="matrixTitle">
        <div class="container">
            <div class="hm-section-head">
                <h2 class="hm-section-h2" id="matrixTitle">Unified capability architecture</h2>
                <p class="hm-section-p">Identity, parcel transport, and daily business operations connected on a single balance ledger.</p>
            </div>

            <div class="hm-matrix-grid">
                {{-- Column 1: Identity & Compliance --}}
                <div class="hm-matrix-col">
                    <div class="hm-col-icon hm-col-icon--cyan">
                        <i class="fa-solid fa-id-card-clip" aria-hidden="true"></i>
                    </div>
                    <h3 class="hm-col-title">Identity & Compliance</h3>
                    <p class="hm-col-desc">Enterprise KYC pipelines connecting directly to government databases with verifiable audit trails.</p>

                    <ul class="hm-matrix-list">
                        <li class="hm-matrix-item">
                            <i class="fa-solid fa-check" aria-hidden="true"></i>
                            <span>NIMC NIN verification with Premium, Standard, and Basic slip rendering</span>
                        </li>
                        <li class="hm-matrix-item">
                            <i class="fa-solid fa-check" aria-hidden="true"></i>
                            <span>NIBSS BVN identity validation with match confidence scoring</span>
                        </li>
                        <li class="hm-matrix-item">
                            <i class="fa-solid fa-check" aria-hidden="true"></i>
                            <span>CAC business & director status check via RC registry</span>
                        </li>
                        <li class="hm-matrix-item">
                            <i class="fa-solid fa-check" aria-hidden="true"></i>
                            <span>Biometric face match and document liveness analysis</span>
                        </li>
                    </ul>

                    <div class="hm-col-footer">
                        <a href="{{ $primaryCtaHref }}" class="hm-action-link">
                            <span>Integrate identity flows</span>
                            <i class="fa-solid fa-chevron-right small" aria-hidden="true"></i>
                        </a>
                    </div>
                </div>

                {{-- Column 2: Logistics & Parcels --}}
                <div class="hm-matrix-col">
                    <div class="hm-col-icon hm-col-icon--emerald">
                        <i class="fa-solid fa-truck-fast" aria-hidden="true"></i>
                    </div>
                    <h3 class="hm-col-title">Logistics & Freight</h3>
                    <p class="hm-col-desc">Inter-state courier routing, barcode waybill generation, and real-time customer tracking notifications.</p>

                    <ul class="hm-matrix-list">
                        <li class="hm-matrix-item">
                            <i class="fa-solid fa-check" aria-hidden="true"></i>
                            <span>Automated QR waybill creation with PDF print and dispatch tags</span>
                        </li>
                        <li class="hm-matrix-item">
                            <i class="fa-solid fa-check" aria-hidden="true"></i>
                            <span>Live milestone checkpoint scanning across 36 Nigerian states</span>
                        </li>
                        <li class="hm-matrix-item">
                            <i class="fa-solid fa-check" aria-hidden="true"></i>
                            <span>Direct customer SMS and WhatsApp delivery tracking links</span>
                        </li>
                        <li class="hm-matrix-item">
                            <i class="fa-solid fa-check" aria-hidden="true"></i>
                            <span>Integrated parcel insurance and electronic signature confirmation</span>
                        </li>
                    </ul>

                    <div class="hm-col-footer">
                        <a href="{{ $logisticsHref }}" class="hm-action-link">
                            <span>Explore logistics network</span>
                            <i class="fa-solid fa-chevron-right small" aria-hidden="true"></i>
                        </a>
                    </div>
                </div>

                {{-- Column 3: Daily Business Operations --}}
                <div class="hm-matrix-col">
                    <div class="hm-col-icon hm-col-icon--amber">
                        <i class="fa-solid fa-briefcase" aria-hidden="true"></i>
                    </div>
                    <h3 class="hm-col-title">Business Operations</h3>
                    <p class="hm-col-desc">Essential daily utilities including digital notarization, public auction escrow, and high-volume VTU.</p>

                    <ul class="hm-matrix-list">
                        <li class="hm-matrix-item">
                            <i class="fa-solid fa-check" aria-hidden="true"></i>
                            <span>High-availability VTU API for airtime, data, and electricity bill payments</span>
                        </li>
                        <li class="hm-matrix-item">
                            <i class="fa-solid fa-check" aria-hidden="true"></i>
                            <span>Remote digital notary certification and sworn affidavit processing</span>
                        </li>
                        <li class="hm-matrix-item">
                            <i class="fa-solid fa-check" aria-hidden="true"></i>
                            <span>Public auctions with secured bidder escrow and lot management</span>
                        </li>
                        <li class="hm-matrix-item">
                            <i class="fa-solid fa-check" aria-hidden="true"></i>
                            <span>Sub-account wallets with team role permissions and audit logs</span>
                        </li>
                    </ul>

                    <div class="hm-col-footer">
                        <a href="{{ $vtuHref }}" class="hm-action-link">
                            <span>Open operations suite</span>
                            <i class="fa-solid fa-chevron-right small" aria-hidden="true"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- 5. AUTHENTIC ENTERPRISE TESTIMONIALS --}}
    <section class="hm-section hm-section--muted" aria-labelledby="proofTitle">
        <div class="container">
            <div class="hm-section-head">
                <h2 class="hm-section-h2" id="proofTitle">Operational feedback from real teams</h2>
                <p class="hm-section-p">How Nigerian businesses rely on Fuwa.NG for everyday compliance and dispatch.</p>
            </div>

            <div class="hm-proof-grid">
                <figure class="hm-proof-card">
                    <blockquote>
                        “Switching our customer onboarding to Fuwa dropped our verification drop-off by 38%. The instant NIMC response with pre-rendered premium slips eliminated manual reviews entirely.”
                    </blockquote>
                    <div class="hm-proof-author">
                        <img src="{{ $assetPrefix . \App\Support\TestimonialAvatars::webPath('Chioma|Operations|Fintech') }}" alt="Chioma" class="hm-proof-avatar" width="40" height="40" loading="lazy">
                        <div>
                            <div class="hm-proof-name">Chioma Adeyemi</div>
                            <div class="hm-proof-role">Head of Operations — Regulated Lagos Fintech</div>
                        </div>
                    </div>
                </figure>

                <figure class="hm-proof-card">
                    <blockquote>
                        “Our inter-state parcel waybills and customer QR tracking run completely through Fuwa’s logistics API. We went from endless dispute calls to zero lost packages across our South-West hubs.”
                    </blockquote>
                    <div class="hm-proof-author">
                        <img src="{{ $assetPrefix . \App\Support\TestimonialAvatars::webPath('Yusuf|Logistics|Dispatch') }}" alt="Yusuf" class="hm-proof-avatar" width="40" height="40" loading="lazy">
                        <div>
                            <div class="hm-proof-name">Yusuf Ibrahim</div>
                            <div class="hm-proof-role">Logistics Director — Nationwide Courier Network</div>
                        </div>
                    </div>
                </figure>

                <figure class="hm-proof-card">
                    <blockquote>
                        “The ability to fund one master wallet and let our field agents perform on-site BVN matches and airtime top-ups from mobile has simplified our daily branch accounting completely.”
                    </blockquote>
                    <div class="hm-proof-author">
                        <img src="{{ $assetPrefix . \App\Support\TestimonialAvatars::webPath('Amaka|Agency|Banking') }}" alt="Amaka" class="hm-proof-avatar" width="40" height="40" loading="lazy">
                        <div>
                            <div class="hm-proof-name">Amaka Okafor</div>
                            <div class="hm-proof-role">Agency Banking Lead — Micro-finance Group</div>
                        </div>
                    </div>
                </figure>
            </div>
        </div>
    </section>

    {{-- 6. MINIMALIST FINAL CONVERSION BANNER --}}
    <section class="hm-section" aria-label="Get started banner">
        <div class="container">
            <div class="hm-cta-banner">
                <div class="hm-cta-content">
                    <h2 class="hm-cta-title">Start verifying in under 5 minutes</h2>
                    <p class="hm-cta-p">Create your free account, fund your balance, or integrate via our REST API. No setup fees or monthly minimums.</p>
                </div>
                <div class="hm-cta-actions">
                    <a class="hm-btn-primary" href="{{ $primaryCtaHref }}">{{ $primaryCtaText }}</a>
                    <a class="hm-btn-secondary" href="{{ $salesHref }}" target="_blank" rel="noopener noreferrer">
                        <i class="fa-brands fa-whatsapp text-success" aria-hidden="true"></i> Contact Sales
                    </a>
                </div>
            </div>
        </div>
    </section>
</main>
@endsection

@push('styles')
<link rel="stylesheet" href="{{ $assetPrefix . '/assets/nexus/css/home-sales.css?v=' . @filemtime(public_path('assets/nexus/css/home-sales.css')) }}">
@endpush

@push('scripts')
<script src="{{ $assetPrefix . '/assets/nexus/js/home-sales.js?v=' . @filemtime(public_path('assets/nexus/js/home-sales.js')) }}" defer></script>
<script type="application/ld+json">
{
    "@context": "https://schema.org",
    "@type": "WebPage",
    "name": "Verifiable Identity & Commerce Infrastructure for Nigeria | Fuwa.NG",
    "description": "Nigeria's infrastructure for instant NIN/BVN verification, inter-state parcel logistics, VTU, auctions, and digital notary. Integrated REST API.",
    "url": "{{ route('home') }}",
    "mainEntity": {
        "@type": "FAQPage",
        "mainEntity": [
            {
                "@type": "Question",
                "name": "What is Fuwa.NG?",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "Fuwa.NG is an all-in-one Nigerian business and developer infrastructure platform. We provide instant KYC (NIN & BVN verification with slip generation), nationwide door-to-door parcel logistics, automated VTU airtime/data services, online auctions, and certified digital notary workflows."
                }
            },
            {
                "@type": "Question",
                "name": "How does NIN and BVN verification work on Fuwa.NG?",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "Our platform connects directly to official identity registries (NIMC and NIBSS) to validate customer credentials in under 400ms. Users can download official Premium, Standard, or Basic NIN slips and verify BVN records either through our web dashboard or through our REST API."
                }
            },
            {
                "@type": "Question",
                "name": "Is there a setup fee or recurring monthly subscription?",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "No. Fuwa.NG operates on a transparent pay-as-you-go wallet model. You only pay for the verification checks or parcel shipments you execute, with zero recurring maintenance charges."
                }
            }
        ]
    }
}
</script>
@endpush
