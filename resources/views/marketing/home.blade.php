@extends('layouts.nexus')

@section('title', 'Instant KYC & All-In-One Business Tools for Nigeria | Fuwa.NG')
@section('meta_description', 'Onboard users seamlessly with Nigeria's most reliable NIN and BVN verification. Grow your business with integrated VTU, logistics, auctions, and notary services. Get started for free.')
@section('meta_keywords', 'NIN verification Nigeria, BVN validation, KYC Nigeria, identity verification API, VTU services, logistics platform Nigeria, online auctions Nigeria, notary services Nigeria')
@section('canonical', route('home'))

@section('og_title', 'Fuwa.NG: Instant KYC & All-In-One Business Tools for Nigeria')
@section('og_description', 'Onboard users with reliable NIN/BVN checks. Expand with VTU, logistics, auctions, and legal services on a single platform. Built for growth in Nigeria.')
@section('og_image', url('/images/og/home.jpg'))
@section('og_type', 'website')

@section('public_wrapper_class', 'none')

@section('content')
@php
    $primaryCtaHref = route('register');
    $secondaryCtaHref = route('login');
    $waNumber = \App\Models\SystemSetting::get('support_whatsapp');
    $cleanWa = $waNumber ? preg_replace('/[^0-9]/', '', $waNumber) : null;
    $salesHref = $cleanWa ? ('https://wa.me/' . $cleanWa . '?text=' . urlencode('Hello Fuwa.NG Sales, I want to onboard KYC (NIN/BVN) for my business.')) : 'mailto:' . (\App\Models\SystemSetting::get('support_email') ?: 'support@example.com');
    $assetPrefix = rtrim(preg_replace('#/index\.php$#', '', request()->getBaseUrl()), '/');
@endphp

<main class="home-sales" role="main">
    <header class="home-sales__hero" aria-labelledby="homeHeroTitle">
        <div class="container home-sales__grid">
            <div class="home-sales__copy">
                <p class="home-sales__eyebrow">KYC that works in Nigeria</p>
                <h1 class="home-sales__title" id="homeHeroTitle">Verify customers in seconds. Reduce fraud. Convert more sign-ups.</h1>
                <p class="home-sales__sub">
                    Run compliant onboarding with NIN and BVN validation, power daily sales with VTU, and expand into logistics, auctions, and notary — all from one simple dashboard.
                </p>

                <div class="home-sales__cta" role="group" aria-label="Primary actions">
                    <a class="btn btn-primary" href="{{ $primaryCtaHref }}">Create free account</a>
                    <a class="btn btn-outline" href="{{ $salesHref }}" target="_blank" rel="noopener noreferrer">Talk to sales</a>
                </div>

                <ul class="home-sales__trust" aria-label="Trust highlights">
                    <li>Encrypted requests & audit trail</li>
                    <li>Pay-as-you-go pricing</li>
                    <li>Fast setup for teams</li>
                </ul>
            </div>

            <div class="home-sales__visual" aria-label="Diverse customers and operators">
                <img
                    src="{{ $assetPrefix }}/images/people/hero-human.png"
                    alt="A smiling woman representing a verified customer using her mobile phone"
                    width="640"
                    height="520"
                    decoding="async"
                    fetchpriority="high"
                >
            </div>
        </div>
    </header>

    <section class="home-sales__section" aria-labelledby="servicesTitle">
        <div class="container">
            <div class="home-sales__head">
                <h2 class="home-sales__h2" id="servicesTitle">Services that drive revenue</h2>
                <p class="home-sales__p">Grow into logistics, auctions, notary, and VTU — without switching platforms.</p>
            </div>

            <div class="home-sales__cards" role="list">
                <article class="home-sales__card" role="listitem">
                    <div class="home-sales__icon" aria-hidden="true"><i class="fa-regular fa-id-card"></i></div>
                    <h3 class="home-sales__h3">NIN Verification</h3>
                    <p class="home-sales__p">Confirm identity and retrieve verified records. Built for branch and remote flows.</p>
                    <a class="home-sales__link" href="{{ $primaryCtaHref }}">Verify NIN →</a>
                </article>

                <article class="home-sales__card" role="listitem">
                    <div class="home-sales__icon" aria-hidden="true"><i class="fa-solid fa-fingerprint"></i></div>
                    <h3 class="home-sales__h3">BVN Validation</h3>
                    <p class="home-sales__p">Reduce fraud with strong financial identity checks and match confidence signals.</p>
                    <a class="home-sales__link" href="{{ $primaryCtaHref }}">Validate BVN →</a>
                </article>

                <article class="home-sales__card" role="listitem">
                    <div class="home-sales__icon" aria-hidden="true"><i class="fa-solid fa-truck-fast"></i></div>
                    <h3 class="home-sales__h3">Logistics</h3>
                    <p class="home-sales__p">Track shipments, reduce delivery disputes, and give customers real-time updates that build trust.</p>
                    @php
                        $logisticsHref = auth()->check() && \Illuminate\Support\Facades\Route::has('user.logistics.dashboard')
                            ? route('user.logistics.dashboard')
                            : route('public.logistics.index');
                    @endphp
                    <a class="home-sales__link" href="{{ $logisticsHref }}">Explore logistics →</a>
                </article>

                <article class="home-sales__card" role="listitem">
                    <div class="home-sales__icon" aria-hidden="true"><i class="fa-solid fa-gavel"></i></div>
                    <h3 class="home-sales__h3">Auctions</h3>
                    <p class="home-sales__p">Run transparent bidding with verified listings, clear history, and higher buyer confidence.</p>
                    @php
                        $auctionsHref = auth()->check() && \Illuminate\Support\Facades\Route::has('auctions.dashboard')
                            ? route('auctions.dashboard')
                            : route('public.auctions.index');
                    @endphp
                    <a class="home-sales__link" href="{{ $auctionsHref }}">View auctions →</a>
                </article>

                <article class="home-sales__card" role="listitem">
                    <div class="home-sales__icon" aria-hidden="true"><i class="fa-solid fa-scale-balanced"></i></div>
                    <h3 class="home-sales__h3">Notary</h3>
                    <p class="home-sales__p">Get documents notarized faster with less back-and-forth and clearer status updates.</p>
                    @php
                        $notaryHref = auth()->check() ? route('services.notary') : url('/explore/notary-services');
                    @endphp
                    <a class="home-sales__link" href="{{ $notaryHref }}">Request notary →</a>
                </article>

                <article class="home-sales__card" role="listitem">
                    <div class="home-sales__icon" aria-hidden="true"><i class="fa-solid fa-signal"></i></div>
                    <h3 class="home-sales__h3">VTU (Airtime & Data)</h3>
                    <p class="home-sales__p">Instant top-ups with wallet tracking and clean transaction history.</p>
                    @php
                        $vtuHref = auth()->check() ? route('services.vtu.hub') : url('/explore/vtu-services');
                    @endphp
                    <a class="home-sales__link" href="{{ $vtuHref }}">Open VTU →</a>
                </article>
            </div>

            <div class="home-sales__more">
                <button
                    class="home-sales__morebtn"
                    type="button"
                    aria-expanded="false"
                    aria-controls="homeMoreServices"
                    data-home-more
                >
                    See more services
                </button>
                <a class="home-sales__morelink" href="{{ url('/explore/services') }}">Open full catalog</a>
            </div>

            <div class="home-sales__catalog" id="homeMoreServices" hidden>
                <div class="home-sales__cataloghead">More services</div>
                <div class="home-sales__cataloggrid" role="list">
                    @php
                        $moreServices = [
                            ['label' => 'Driver’s License', 'href' => auth()->check() ? route('services.drivers_license') : url('/explore/drivers-license-check')],
                            ['label' => 'Agency Banking', 'href' => auth()->check() ? route('services.agency') : url('/explore/agency-banking')],
                            ['label' => 'Virtual Cards', 'href' => auth()->check() ? route('services.virtual_card') : url('/explore/virtual-cards')],
                            ['label' => 'Payments & Invoicing', 'href' => auth()->check() ? route('services.invoicing') : url('/explore/invoicing')],
                            ['label' => 'FX Exchange', 'href' => auth()->check() ? route('services.fx') : url('/explore/fx-exchange')],
                            ['label' => 'Ticketing', 'href' => auth()->check() ? route('services.ticketing') : url('/explore/ticketing')],
                        ];
                    @endphp
                    @foreach($moreServices as $ms)
                        <a class="home-sales__catalogitem" role="listitem" href="{{ $ms['href'] }}">{{ $ms['label'] }}</a>
                    @endforeach
                </div>
            </div>
        </div>
    </section>

    <section class="home-sales__section home-sales__section--muted" aria-labelledby="howTitle">
        <div class="container">
            <div class="home-sales__head">
                <h2 class="home-sales__h2" id="howTitle">How it works</h2>
                <p class="home-sales__p">A simple flow designed to increase conversion and reduce manual review.</p>
            </div>

            <ol class="home-sales__steps">
                <li class="home-sales__step">
                    <span class="home-sales__stepn" aria-hidden="true">1</span>
                    <div>
                        <div class="home-sales__stept">Create your account</div>
                        <div class="home-sales__stepsub">Set up your team and wallet in minutes.</div>
                    </div>
                </li>
                <li class="home-sales__step">
                    <span class="home-sales__stepn" aria-hidden="true">2</span>
                    <div>
                        <div class="home-sales__stept">Run NIN/BVN verification</div>
                        <div class="home-sales__stepsub">Collect only what’s needed and verify instantly.</div>
                    </div>
                </li>
                <li class="home-sales__step">
                    <span class="home-sales__stepn" aria-hidden="true">3</span>
                    <div>
                        <div class="home-sales__stept">Store results + audit trail</div>
                        <div class="home-sales__stepsub">Keep a compliant vault for disputes and compliance.</div>
                    </div>
                </li>
            </ol>
        </div>
    </section>

    <section class="home-sales__section" aria-labelledby="trustTitle">
        <div class="container">
            <div class="home-sales__head">
                <h2 class="home-sales__h2" id="trustTitle">Trusted by real teams</h2>
                <p class="home-sales__p">What customers say after switching to a faster verification flow.</p>
            </div>

            <div class="home-sales__quotes">
                <figure class="home-sales__quote">
                    <img src="{{ $assetPrefix }}/images/people/customer-1.jpg" alt="Customer portrait" width="56" height="56" loading="lazy" decoding="async" style="object-fit: cover; border-radius: 50%;">
                    <blockquote>“We reduced failed onboarding and support tickets immediately. Customers finish verification faster.”</blockquote>
                    <figcaption>Chioma, Operations — Fintech</figcaption>
                </figure>
                <figure class="home-sales__quote">
                    <img src="{{ $assetPrefix }}/images/people/customer-2.jpg" alt="Customer portrait" width="56" height="56" loading="lazy" decoding="async" style="object-fit: cover; border-radius: 50%;">
                    <blockquote>“The verification vault saves us hours every week. Everything is traceable and clean.”</blockquote>
                    <figcaption>Yusuf, Compliance — Lending</figcaption>
                </figure>
                <figure class="home-sales__quote">
                    <img src="{{ $assetPrefix }}/images/people/customer-3.jpg" alt="Customer portrait" width="56" height="56" loading="lazy" decoding="async" style="object-fit: cover; border-radius: 50%;">
                    <blockquote>“Our agents can do NIN checks on-site, and we can top up VTU without switching apps.”</blockquote>
                    <figcaption>Amaka, Product — Agency Network</figcaption>
                </figure>
            </div>
        </div>
    </section>

    <section class="home-sales__section home-sales__cta2" aria-label="Sign up call to action">
        <div class="container home-sales__cta2inner">
            <div>
                <h2 class="home-sales__h2">Ready to improve conversion?</h2>
                <p class="home-sales__p">Start free, or talk to sales for enterprise onboarding and compliance.</p>
            </div>
            <div class="home-sales__cta">
                <a class="btn btn-primary" href="{{ $primaryCtaHref }}">Create free account</a>
                <a class="btn btn-outline" href="{{ $salesHref }}" target="_blank" rel="noopener noreferrer">Contact sales</a>
                <a class="btn btn-link" href="{{ $secondaryCtaHref }}">Sign in</a>
            </div>
        </div>
    </section>
@endsection

@push('styles')
<link rel="stylesheet" href="{{ $assetPrefix . '/assets/nexus/css/home-sales.css' }}">
@endpush

@push('scripts')
<script src="{{ $assetPrefix . '/assets/nexus/js/home-sales.js' }}" defer></script>
<script type="application/ld+json">
{
    "@context": "https://schema.org",
    "@type": "WebPage",
    "name": "Instant KYC & All-In-One Business Tools for Nigeria | Fuwa.NG",
    "description": "Onboard users seamlessly with Nigeria's most reliable NIN and BVN verification. Grow your business with integrated VTU, logistics, auctions, and notary services. Get started for free.",
    "url": "{{ route('home') }}",
    "mainEntity": {
        "@type": "FAQPage",
        "mainEntity": [
            {
                "@type": "Question",
                "name": "What is Fuwa.NG?",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "Fuwa.NG is a comprehensive platform providing Nigerian businesses with tools for growth. We offer instant KYC (NIN & BVN) for customer onboarding, alongside VTU services for airtime/data sales, logistics and shipment tracking, online auctions, and digital notary services."
                }
            },
            {
                "@type": "Question",
                "name": "How does NIN and BVN verification work?",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "Our system connects to official databases to instantly verify customer identities using their National Identification Number (NIN) or Bank Verification Number (BVN). This reduces fraud, ensures compliance, and speeds up your onboarding process. You can verify users through our dashboard or integrate our API into your own application."
                }
            },
            {
                "@type": "Question",
                "name": "Is there a cost to get started?",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "No, you can create a Fuwa.NG account for free. We operate on a pay-as-you-go model, so you only pay for the verification checks you perform or the services you use. There are no monthly subscription fees for a standard account."
                }
            }
        ]
    }
}
</script>
@endpush
