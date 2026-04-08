@extends('layouts.nexus')

@section('title', ($service['title'] ?? 'Service') . ' | ' . ($service['tagline'] ?? config('app.name')))
@section('meta_description', $service['summary'] ?? 'Explore services offered by ' . config('app.name'))
@section('meta_keywords', implode(', ', [$service['title'], $service['category'], 'Nigeria', 'Fuwa.NG']))
@section('canonical', route('explore.service', ['slug' => $slug]))

@section('og_title', $service['title'] ?? 'Service')
@section('og_description', $service['summary'] ?? 'Explore services offered by ' . config('app.name'))
@section('og_image', url('/images/og/service-' . ($slug ?? 'default') . '.jpg'))

@section('content')
@php
    $gradient = $service['image']['gradient'] ?? 'linear-gradient(135deg, rgba(59,130,246,0.15), rgba(139,92,246,0.10))';
    $primary = $service['links']['primary'] ?? route('register');
    $secondary = $service['links']['secondary'] ?? route('login');
    $isAuthed = auth()->check();
    $experiment = 'service_landing';
    $variant = (string) request()->cookie('ab_service_landing', 'A');

    $landing = is_array($service['landing'] ?? null) ? $service['landing'] : [];
    $hero = is_array($landing['hero'] ?? null) ? $landing['hero'] : [];

    $heroHeadline = $hero['headline_' . $variant] ?? $hero['headline_A'] ?? ($service['title'] ?? 'Service');
    $heroSubheadline = $hero['subheadline'] ?? ($service['summary'] ?? '');
    $primaryLabel = $hero['primary_cta_label'] ?? ($service['cta']['primary_label'] ?? 'Get started');
    $secondaryLabel = $hero['secondary_cta_label'] ?? ($service['cta']['secondary_label'] ?? 'Sign in');

    $painPoints = is_array($landing['pain_points'] ?? null) ? $landing['pain_points'] : [];
    $benefits = is_array($landing['benefits'] ?? null) ? $landing['benefits'] : [];
    $modules = is_array($landing['modules'] ?? null) ? $landing['modules'] : [];
    $steps = is_array($landing['steps'] ?? null) ? $landing['steps'] : [];
    $faq = is_array($landing['faq'] ?? null) ? $landing['faq'] : [];
    $closing = is_array($landing['closing'] ?? null) ? $landing['closing'] : [];
    $category = (string) ($service['category'] ?? 'general');

    if (empty($painPoints)) {
        $painPoints = match ($category) {
            'identity' => [
                ['title' => 'Slow onboarding', 'body' => 'Manual checks delay approvals and frustrate legitimate users.'],
                ['title' => 'Fraud and chargebacks', 'body' => 'Weak verification increases exposure and operational costs.'],
                ['title' => 'Audit gaps', 'body' => 'Without clean records, compliance and dispute resolution become difficult.'],
            ],
            'legal' => [
                ['title' => 'Drafting takes too long', 'body' => 'Rewriting documents repeatedly slows teams down.'],
                ['title' => 'Inconsistent clauses', 'body' => 'Copy-paste templates create risky omissions and errors.'],
                ['title' => 'Hard to track requests', 'body' => 'Without a workflow, handoffs and follow-ups get missed.'],
            ],
            'logistics' => [
                ['title' => 'Limited visibility', 'body' => 'Customers want updates without chasing support.'],
                ['title' => 'Manual tracking', 'body' => 'Copying tracking codes across tools wastes time.'],
                ['title' => 'Unclear accountability', 'body' => 'Lack of history makes investigations harder.'],
            ],
            'commerce' => [
                ['title' => 'Low trust listings', 'body' => 'Buyers hesitate when details and proof are unclear.'],
                ['title' => 'Slow discovery', 'body' => 'Finding the right item should take seconds, not minutes.'],
                ['title' => 'Weak transparency', 'body' => 'A clear trail reduces disputes and increases confidence.'],
            ],
            'finance' => [
                ['title' => 'Scattered billing tools', 'body' => 'Switching apps slows execution and increases errors.'],
                ['title' => 'Hard-to-reconcile payments', 'body' => 'Missing references create support and accounting pain.'],
                ['title' => 'Time-sensitive actions', 'body' => 'When it’s urgent, the flow must stay reliable and fast.'],
            ],
            'education' => [
                ['title' => 'Time-sensitive registrations', 'body' => 'Students and admins need pins without delays.'],
                ['title' => 'Limited visibility', 'body' => 'Tracking purchases reduces repeats and support load.'],
                ['title' => 'Operational overhead', 'body' => 'A clean workflow beats spreadsheets and screenshots.'],
            ],
            'insurance' => [
                ['title' => 'Complex decisions', 'body' => 'Customers need clarity on what they’re buying.'],
                ['title' => 'Slow follow-up', 'body' => 'Delays reduce conversions and trust.'],
                ['title' => 'Proof and references', 'body' => 'Clear records simplify support and compliance.'],
            ],
            default => [
                ['title' => 'Unclear next step', 'body' => 'Visitors bounce when they don’t know what to do first.'],
                ['title' => 'Low confidence', 'body' => 'Trust signals and transparency reduce hesitation.'],
                ['title' => 'Too much friction', 'body' => 'Fast, guided steps convert better than long forms.'],
            ],
        };
    }

    if (empty($benefits)) {
        $benefits = match ($category) {
            'identity' => [
                ['title' => 'Faster decisions', 'body' => 'Verify key fields quickly and move users forward.', 'icon' => 'fa-solid fa-stopwatch'],
                ['title' => 'Lower fraud risk', 'body' => 'Add strong checks where it matters most.', 'icon' => 'fa-solid fa-shield-halved'],
                ['title' => 'Audit-ready results', 'body' => 'Keep references and history for compliance.', 'icon' => 'fa-solid fa-clipboard-check'],
                ['title' => 'Provider flexibility', 'body' => 'Use available providers and routing options.', 'icon' => 'fa-solid fa-sitemap'],
            ],
            'finance' => [
                ['title' => 'Faster execution', 'body' => 'Complete time-sensitive actions without delays.', 'icon' => 'fa-solid fa-bolt'],
                ['title' => 'Trackable records', 'body' => 'Use references and history to reconcile spend.', 'icon' => 'fa-solid fa-receipt'],
                ['title' => 'Wallet-first billing', 'body' => 'Fund once and pay across services.', 'icon' => 'fa-solid fa-wallet'],
                ['title' => 'Operational clarity', 'body' => 'Keep a clean trail for support and teams.', 'icon' => 'fa-solid fa-people-group'],
            ],
            default => [
                ['title' => 'Clear value', 'body' => 'A benefit-led flow communicates why it matters.', 'icon' => 'fa-solid fa-bullseye'],
                ['title' => 'Reduced friction', 'body' => 'Fewer steps and clearer CTAs increase completion.', 'icon' => 'fa-solid fa-sliders'],
                ['title' => 'Trust built-in', 'body' => 'Signals and transparency reduce hesitation.', 'icon' => 'fa-solid fa-shield-halved'],
                ['title' => 'Better follow-through', 'body' => 'A closing argument pushes the decision forward.', 'icon' => 'fa-solid fa-arrow-right'],
            ],
        };
    }

    if (empty($steps)) {
        $steps = [
            ['title' => 'Create an account', 'body' => 'Unlock actions and keep a record of results.'],
            ['title' => 'Choose your option', 'body' => 'Select the service mode that fits your use case.'],
            ['title' => 'Run and track', 'body' => 'Get a response and keep a reference in history.'],
        ];
    }

    if (empty($closing)) {
        $closing = [
            'headline' => 'Ready to move from “maybe” to “done”?',
            'body' => 'Create an account to unlock full access, keep references, and complete actions with a cleaner workflow.',
        ];
    }

    $heroSvg = '';
    if (($slug ?? '') === 'airtime') {
        $heroSvg = <<<SVG
<svg viewBox="0 0 520 360" width="100%" height="100%" role="img" aria-label="Airtime top-up illustration" xmlns="http://www.w3.org/2000/svg">
  <defs>
    <linearGradient id="g1" x1="0" y1="0" x2="1" y2="1">
      <stop offset="0" stop-color="rgba(59,130,246,0.85)"/>
      <stop offset="1" stop-color="rgba(16,185,129,0.75)"/>
    </linearGradient>
    <linearGradient id="g2" x1="0" y1="0" x2="1" y2="1">
      <stop offset="0" stop-color="rgba(255,255,255,0.14)"/>
      <stop offset="1" stop-color="rgba(255,255,255,0.04)"/>
    </linearGradient>
  </defs>
  <rect x="0" y="0" width="520" height="360" rx="28" fill="url(#g2)"/>
  <circle cx="410" cy="94" r="54" fill="rgba(255,255,255,0.06)"/>
  <circle cx="110" cy="270" r="72" fill="rgba(255,255,255,0.05)"/>
  <rect x="160" y="58" width="200" height="270" rx="28" fill="rgba(0,0,0,0.22)" stroke="rgba(255,255,255,0.12)"/>
  <rect x="185" y="88" width="150" height="182" rx="18" fill="rgba(255,255,255,0.06)"/>
  <rect x="205" y="118" width="110" height="16" rx="8" fill="rgba(255,255,255,0.14)"/>
  <rect x="205" y="148" width="92" height="16" rx="8" fill="rgba(255,255,255,0.10)"/>
  <rect x="205" y="178" width="124" height="16" rx="8" fill="rgba(255,255,255,0.10)"/>
  <rect x="205" y="214" width="110" height="34" rx="12" fill="url(#g1)"/>
  <circle cx="260" cy="300" r="12" fill="rgba(255,255,255,0.10)"/>
  <path d="M92 132c34-40 92-56 144-40" fill="none" stroke="rgba(255,255,255,0.12)" stroke-width="10" stroke-linecap="round"/>
  <path d="M402 248c-28 36-78 52-124 40" fill="none" stroke="rgba(255,255,255,0.10)" stroke-width="10" stroke-linecap="round"/>
</svg>
SVG;
    } elseif (($slug ?? '') === 'data-bundles') {
        $heroSvg = <<<SVG
<svg viewBox="0 0 520 360" width="100%" height="100%" role="img" aria-label="Data bundles illustration" xmlns="http://www.w3.org/2000/svg">
  <defs>
    <linearGradient id="dg1" x1="0" y1="0" x2="1" y2="1">
      <stop offset="0" stop-color="rgba(139,92,246,0.85)"/>
      <stop offset="1" stop-color="rgba(59,130,246,0.80)"/>
    </linearGradient>
    <linearGradient id="dg2" x1="0" y1="0" x2="1" y2="1">
      <stop offset="0" stop-color="rgba(255,255,255,0.14)"/>
      <stop offset="1" stop-color="rgba(255,255,255,0.04)"/>
    </linearGradient>
  </defs>
  <rect x="0" y="0" width="520" height="360" rx="28" fill="url(#dg2)"/>
  <circle cx="122" cy="96" r="66" fill="rgba(255,255,255,0.06)"/>
  <circle cx="420" cy="270" r="78" fill="rgba(255,255,255,0.05)"/>
  <rect x="92" y="126" width="336" height="156" rx="22" fill="rgba(0,0,0,0.22)" stroke="rgba(255,255,255,0.12)"/>
  <path d="M148 236c26-54 70-84 112-84s86 30 112 84" fill="none" stroke="url(#dg1)" stroke-width="18" stroke-linecap="round"/>
  <path d="M182 236c18-32 44-50 70-50s52 18 70 50" fill="none" stroke="rgba(255,255,255,0.18)" stroke-width="12" stroke-linecap="round"/>
  <circle cx="260" cy="236" r="10" fill="rgba(255,255,255,0.22)"/>
  <rect x="160" y="158" width="200" height="18" rx="9" fill="rgba(255,255,255,0.12)"/>
  <rect x="160" y="186" width="160" height="18" rx="9" fill="rgba(255,255,255,0.10)"/>
  <rect x="160" y="258" width="200" height="16" rx="8" fill="rgba(255,255,255,0.08)"/>
</svg>
SVG;
    } else {
        $heroSvg = <<<SVG
<svg viewBox="0 0 520 360" width="100%" height="100%" role="img" aria-label="Service illustration" xmlns="http://www.w3.org/2000/svg">
  <defs>
    <linearGradient id="gg1" x1="0" y1="0" x2="1" y2="1">
      <stop offset="0" stop-color="rgba(59,130,246,0.55)"/>
      <stop offset="1" stop-color="rgba(139,92,246,0.45)"/>
    </linearGradient>
  </defs>
  <rect x="0" y="0" width="520" height="360" rx="28" fill="rgba(255,255,255,0.06)"/>
  <rect x="76" y="84" width="368" height="192" rx="24" fill="rgba(0,0,0,0.20)" stroke="rgba(255,255,255,0.10)"/>
  <rect x="116" y="124" width="200" height="18" rx="9" fill="rgba(255,255,255,0.12)"/>
  <rect x="116" y="156" width="260" height="16" rx="8" fill="rgba(255,255,255,0.10)"/>
  <rect x="116" y="186" width="240" height="16" rx="8" fill="rgba(255,255,255,0.08)"/>
  <rect x="116" y="222" width="150" height="36" rx="12" fill="url(#gg1)"/>
</svg>
SVG;
    }
@endphp

<div class="container py-5">
    <div class="mb-4">
        <a href="{{ url('/explore') }}" class="text-white-50 text-decoration-none"><i class="fa-solid fa-arrow-left mr-2"></i>Back to services</a>
    </div>

    <div class="p-4 p-lg-5 rounded-lg mb-4" style="background: {{ $gradient }}; border: 1px solid rgba(255,255,255,0.10);">
        <div class="row align-items-center">
            <div class="col-lg-8">
                <div class="d-inline-flex align-items-center px-3 py-2 rounded-pill mb-3" style="background: rgba(0,0,0,0.25); border: 1px solid rgba(255,255,255,0.12);">
                    <i class="{{ $service['icon'] ?? 'fa-solid fa-layer-group' }} mr-2 text-white"></i>
                    <span class="text-white-50 small">Public preview — actions unlock after login</span>
                </div>
                <h1 class="text-white font-weight-bold mb-2">{{ $heroHeadline }}</h1>
                <p class="text-white-50 mb-0" style="max-width: 70ch;">{{ $heroSubheadline }}</p>

                <div class="mt-3 d-flex flex-wrap" style="gap: 10px;">
                    <span class="px-3 py-2 rounded-pill small" style="background: rgba(0,0,0,0.20); border: 1px solid rgba(255,255,255,0.12); color: rgba(255,255,255,0.80);"><i class="fa-solid fa-bolt mr-2"></i>Fast checkout</span>
                    <span class="px-3 py-2 rounded-pill small" style="background: rgba(0,0,0,0.20); border: 1px solid rgba(255,255,255,0.12); color: rgba(255,255,255,0.80);"><i class="fa-solid fa-receipt mr-2"></i>Trackable references</span>
                    <span class="px-3 py-2 rounded-pill small" style="background: rgba(0,0,0,0.20); border: 1px solid rgba(255,255,255,0.12); color: rgba(255,255,255,0.80);"><i class="fa-solid fa-shield-halved mr-2"></i>Security-first</span>
                </div>

                <div class="mt-4 d-flex flex-wrap" style="gap: 12px;">
                    <a href="{{ $primary }}" class="btn btn-primary" data-cta="service_primary">{{ $primaryLabel }}</a>
                    <a href="{{ $secondary }}" class="btn btn-outline-glass" data-cta="service_secondary">{{ $secondaryLabel }}</a>
                </div>
            </div>
            <div class="col-lg-4 mt-4 mt-lg-0">
                <div class="p-3 rounded-lg mb-3" style="background: rgba(0,0,0,0.22); border: 1px solid rgba(255,255,255,0.12);">
                    {!! $heroSvg !!}
                </div>
                <div class="p-3 rounded-lg" style="background: rgba(0,0,0,0.22); border: 1px solid rgba(255,255,255,0.12);">
                    <div class="text-white font-weight-bold mb-2">What you get</div>
                    <ul class="mb-0 pl-3 text-white-50 small">
                        @foreach(($service['highlights'] ?? []) as $h)
                            <li class="mb-1">{{ $h }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    </div>

    @if(!empty($modules))
        <div class="mb-4">
            <div class="d-flex align-items-end justify-content-between mb-3">
                <div>
                    <h2 class="h5 text-white font-weight-bold mb-1">What you can do</h2>
                    <div class="text-white-50 small" style="max-width: 80ch;">Pick a category. Create an account to unlock actions and keep references for each payment.</div>
                </div>
            </div>
            <div class="row">
                @foreach($modules as $m)
                    @php
                        $mLink = $m['link'] ?? '#';
                        $mBadge = $m['badge'] ?? null;
                    @endphp
                    <div class="col-md-6 col-lg-4 mb-3">
                        <a href="{{ $mLink }}" class="text-decoration-none d-block h-100" data-cta="module_card">
                            <div class="p-4 rounded-lg h-100" style="border: 1px solid rgba(255,255,255,0.10); background: rgba(255,255,255,0.03);">
                                <div class="d-flex align-items-center justify-content-between mb-2">
                                    <div class="d-flex align-items-center">
                                        <div class="mr-3 d-flex align-items-center justify-content-center" style="width: 44px; height: 44px; border-radius: 14px; background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.10);">
                                            <i class="{{ $m['icon'] ?? 'fa-solid fa-layer-group' }} text-white"></i>
                                        </div>
                                        <div>
                                            <div class="text-white font-weight-bold">{{ $m['title'] ?? 'Module' }}</div>
                                            <div class="text-white-50 small">{{ $m['subtitle'] ?? '' }}</div>
                                        </div>
                                    </div>
                                    @if($mBadge)
                                        <span class="badge badge-secondary">{{ $mBadge }}</span>
                                    @endif
                                </div>
                                <div class="text-white font-weight-bold">Open <i class="fa-solid fa-arrow-right ml-1"></i></div>
                            </div>
                        </a>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    @if(!empty($painPoints))
        <div class="mb-4">
            <div class="row">
                @foreach($painPoints as $p)
                    <div class="col-lg-4 mb-3">
                        <div class="p-4 rounded-lg h-100" style="border: 1px solid rgba(255,255,255,0.10); background: rgba(255,255,255,0.03);">
                            <div class="text-white font-weight-bold mb-2">{{ $p['title'] ?? 'Pain point' }}</div>
                            <div class="text-white-50 small">{{ $p['body'] ?? '' }}</div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    @if(!empty($benefits))
        <div class="mb-4">
            <div class="d-flex align-items-end justify-content-between mb-3">
                <div>
                    <h2 class="h5 text-white font-weight-bold mb-1">Why this converts better</h2>
                    <div class="text-white-50 small" style="max-width: 80ch;">Benefit-driven by design: reduce friction, increase confidence, and make the next step obvious.</div>
                </div>
            </div>
            <div class="row">
                @foreach($benefits as $b)
                    <div class="col-md-6 col-lg-3 mb-3">
                        <div class="p-4 rounded-lg h-100" style="border: 1px solid rgba(255,255,255,0.10); background: rgba(255,255,255,0.03);">
                            <div class="d-flex align-items-center mb-2">
                                <div class="mr-3 d-flex align-items-center justify-content-center" style="width: 42px; height: 42px; border-radius: 14px; background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.10);">
                                    <i class="{{ $b['icon'] ?? 'fa-solid fa-circle-check' }} text-white"></i>
                                </div>
                                <div class="text-white font-weight-bold">{{ $b['title'] ?? 'Benefit' }}</div>
                            </div>
                            <div class="text-white-50 small">{{ $b['body'] ?? '' }}</div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <div class="row">
        <div class="col-lg-8 mb-4">
            <div class="p-4 rounded-lg position-relative" style="border: 1px solid rgba(255,255,255,0.10); background: rgba(255,255,255,0.03);">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <h2 class="h6 text-white font-weight-bold mb-0">Preview the flow</h2>
                    <span class="badge badge-secondary">Read-only</span>
                </div>
                <div class="text-white-50 small mb-3" style="max-width: 90ch;">This is a safe preview of the form and response shape. Create an account to run the service for real and keep references in your history.</div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="text-white-50 small mb-1">Example input</label>
                        <input class="form-control" placeholder="Locked until login" disabled>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="text-white-50 small mb-1">Provider</label>
                        <select class="form-control" disabled>
                            <option>Enabled providers</option>
                        </select>
                    </div>
                </div>
                <button class="btn btn-primary" disabled>Run service</button>

                <div class="mt-4 p-3 rounded-lg" style="background: rgba(0,0,0,0.18); border: 1px solid rgba(255,255,255,0.08);">
                    <div class="text-white-50 small mb-2">Example response</div>
                    <pre class="mb-0 text-white-50 small" style="white-space: pre-wrap;">{"status":false,"message":"Login required to use this service."}</pre>
                </div>

                @if(!$isAuthed)
                    <div class="position-absolute w-100 h-100" style="top:0;left:0;background: rgba(0,0,0,0.55); backdrop-filter: blur(2px); border-radius: 16px; display:flex; align-items:center; justify-content:center; padding: 18px;">
                        <div class="text-center" style="max-width: 520px;">
                            <div class="text-white font-weight-bold mb-2">Sign in to use {{ $service['title'] }}</div>
                            <div class="text-white-50 small mb-3">To protect data and prevent unauthorized actions, all interactions are disabled until authentication.</div>
                            <div class="d-flex justify-content-center flex-wrap" style="gap: 10px;">
                                <a class="btn btn-primary" href="{{ route('register') }}" data-cta="lockout_register">Create account</a>
                                <a class="btn btn-outline-glass" href="{{ route('login') }}" data-cta="lockout_login">Sign in</a>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>

        <div class="col-lg-4 mb-4">
            <div class="p-4 rounded-lg" style="border: 1px solid rgba(255,255,255,0.10); background: rgba(255,255,255,0.03);">
                <h2 class="h6 text-white font-weight-bold mb-3">Trust & compliance</h2>
                <div class="d-flex flex-wrap" style="gap: 10px;">
                    <span class="px-3 py-2 rounded-pill small" style="border: 1px solid rgba(255,255,255,0.10); background: rgba(255,255,255,0.05); color: rgba(255,255,255,0.75);"><i class="fa-solid fa-shield-halved mr-2"></i>Security-first</span>
                    <span class="px-3 py-2 rounded-pill small" style="border: 1px solid rgba(255,255,255,0.10); background: rgba(255,255,255,0.05); color: rgba(255,255,255,0.75);"><i class="fa-solid fa-key mr-2"></i>Admin-managed credentials</span>
                    <span class="px-3 py-2 rounded-pill small" style="border: 1px solid rgba(255,255,255,0.10); background: rgba(255,255,255,0.05); color: rgba(255,255,255,0.75);"><i class="fa-solid fa-clipboard-check mr-2"></i>Audit trails</span>
                </div>

                <div class="mt-4">
                    <div class="text-white-50 small mb-2">Recommended next step</div>
                    <a href="{{ url('/explore') }}" class="btn btn-outline-glass w-100" data-cta="next_explore">Explore more services</a>
                </div>
            </div>
        </div>
    </div>

    @if(!empty($steps))
        <div class="mb-4">
            <div class.blade.php"p-4 p-lg-5 rounded-lg" style="border: 1px solid rgba(255,255,255,0.10); background: rgba(255,255,255,0.03);">
                <div class="row align-items-center">
                    <div class="col-lg-5 mb-4 mb-lg-0">
                        <h2 class="h5 text-white font-weight-bold mb-2">How it works</h2>
                        <div class="text-white-50 small" style="max-width: 70ch;">A simple path that removes friction: create access, fund once, complete purchases fast.</div>
                    </div>
                    <div class="col-lg-7">
                        <div class="row">
                            @foreach($steps as $i => $s)
                                <div class="col-md-4 mb-3 mb-md-0">
                                    <div class="p-4 rounded-lg h-100" style="background: rgba(0,0,0,0.18); border: 1px solid rgba(255,255,255,0.10);">
                                        <div class="text-white font-weight-bold mb-2">{{ (int) $i + 1 }}. {{ $s['title'] ?? 'Step' }}</div>
                                        <div class="text-white-50 small">{{ $s['body'] ?? '' }}</div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    @if(!empty($faq))
        <div class="mb-4">
            <h2 class="h5 text-white font-weight-bold mb-3">FAQ</h2>
            <div class="row">
                @foreach($faq as $f)
                    <div class="col-lg-6 mb-3">
                        <div class="p-4 rounded-lg h-100" style="border: 1px solid rgba(255,255,255,0.10); background: rgba(255,255,255,0.03);">
                            <div class="text-white font-weight-bold mb-2">{{ $f['q'] ?? '' }}</div>
                            <div class="text-white-50 small">{{ $f['a'] ?? '' }}</div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    @if(!empty($closing))
        <div class="mb-2">
            <div class="p-4 p-lg-5 rounded-lg" style="background: {{ $gradient }}; border: 1px solid rgba(255,255,255,0.10);">
                <div class="row align-items-center">
                    <div class="col-lg-8">
                        <h2 class="h4 text-white font-weight-bold mb-2">{{ $closing['headline'] ?? 'Ready to get started?' }}</h2>
                        <div class="text-white-50 mb-0" style="max-width: 80ch;">{{ $closing['body'] ?? '' }}</div>
                    </div>
                    <div class="col-lg-4 mt-4 mt-lg-0 d-flex flex-wrap justify-content-lg-end" style="gap: 12px;">
                        <a href="{{ $primary }}" class="btn btn-primary" data-cta="closing_primary">{{ $primaryLabel }}</a>
                        <a href="{{ $secondary }}" class="btn btn-outline-glass" data-cta="closing_secondary">{{ $secondaryLabel }}</a>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
@endsection

@push('scripts')
<script type="application/ld+json">
{
    "@context": "https://schema.org",
    "@type": "Service",
    "serviceType": "{{ $service['category'] ?? 'General' }}",
    "provider": {
        "@type": "Organization",
        "name": "{{ config('app.name') }}"
    },
    "areaServed": {
        "@type": "Country",
        "name": "Nigeria"
    },
    "name": "{{ $service['title'] ?? 'Service' }}",
    "description": "{{ $service['summary'] ?? '' }}",
    @if(!empty($faq))
    "mainEntity": {
        "@type": "FAQPage",
        "mainEntity": [
            @foreach($faq as $f)
            {
                "@type": "Question",
                "name": "{{ $f['q'] ?? '' }}",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "{{ $f['a'] ?? '' }}"
                }
            }{{ !$loop->last ? ',' : '' }}
            @endforeach
        ]
    },
    @endif
    "potentialAction": {
        "@type": "ViewAction",
        "target": "{{ route('explore.service', ['slug' => $slug]) }}"
    }
}
</script>
<script>
(() => {
  const url = @json(route('ab.event'));
  const variant = @json($variant);
  const experiment = @json($experiment);
  const slug = @json($slug ?? null);

  function getSessionId() {
    const k = 'mk_sid_v1';
    let v = null;
    try { v = localStorage.getItem(k); } catch (e) {}
    if (!v) {
      v = (crypto?.randomUUID ? crypto.randomUUID() : (Date.now().toString(36) + Math.random().toString(36).slice(2)));
      try { localStorage.setItem(k, v); } catch (e) {}
    }
    return v;
  }

  function postEvent(eventName, meta) {
    const payload = {
      event_name: eventName,
      page: window.location.pathname + (window.location.search || ''),
      experiment,
      variant,
      session_id: getSessionId(),
      meta: Object.assign({ slug }, meta || {})
    };

    if (window.csrfFetch) {
      window.csrfFetch(url, { data: payload }).catch(() => {});
      return;
    }

    const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    fetch(url, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
      body: JSON.stringify(payload),
      keepalive: true
    }).catch(() => {});
  }

  document.querySelectorAll('[data-cta]').forEach(el => {
    el.addEventListener('click', () => {
      const cta = el.getAttribute('data-cta');
      const href = el.getAttribute('href');
      postEvent('cta_click', { cta, href });

      if (cta === 'service_primary' || cta === 'lockout_register' || cta === 'closing_primary') {
        postEvent('conversion', { cta, href });
      }
    });
  });
})();
</script>
@endpush
