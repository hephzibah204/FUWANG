@props(['kyc'])

@php
    /** @var array $kyc from KycService::tierUpgradeSummary */
@endphp

@if (! ($kyc['kyc_enabled'] ?? true))
    {{-- Limits middleware may still run; hide marketing of tiers when KYC is off --}}
@else
<div class="panel-card mb-4 overflow-hidden" style="border: 1px solid rgba(59, 130, 246, 0.25); background: rgba(59, 130, 246, 0.06);">
    <div class="p-4">
        <div class="d-flex flex-column flex-md-row align-items-start justify-content-between gap-3">
            <div>
                <div class="d-flex align-items-center gap-2 mb-2">
                    <span class="badge px-3 py-2 rounded-pill" style="background: rgba(59, 130, 246, 0.2); color: #93c5fd;">
                        <i class="fa-solid fa-layer-group mr-1"></i> KYC Tier {{ (int) ($kyc['tier'] ?? 0) }}
                    </span>
                    <span class="text-white font-weight-bold">{{ $kyc['label'] ?? '' }}</span>
                </div>
                <p class="text-white-50 small mb-0">
                    Your limits: up to <strong class="text-white">₦{{ number_format((float) ($kyc['single_limit'] ?? 0), 0) }}</strong> per transaction
                    and <strong class="text-white">₦{{ number_format((float) ($kyc['daily_limit'] ?? 0), 0) }}</strong> per day (among other rules).
                </p>
            </div>
        </div>

        <hr class="my-3" style="border-color: rgba(255,255,255,0.08);">

        <p class="text-white-50 small font-weight-bold text-uppercase mb-2" style="letter-spacing: 0.06em;">How to raise your tier</p>
        <ul class="list-unstyled small mb-0 pl-0">
            <li class="mb-2 d-flex align-items-start" style="gap: 10px;">
                @if (! empty($kyc['email_verified']))
                    <i class="fa-solid fa-circle-check text-success mt-1"></i>
                    <span class="text-white-50"><strong class="text-white">Tier 1 — Email</strong> — Your email is verified.</span>
                @else
                    <i class="fa-regular fa-circle text-warning mt-1"></i>
                    <span class="text-white-50">
                        <strong class="text-white">Tier 1 — Email</strong> — Confirm the email on your account. If you never received a link, check spam or resend from the verification page.
                        @if (\Illuminate\Support\Facades\Route::has('verification.notice'))
                            <a href="{{ route('verification.notice') }}" class="d-inline-block ml-1 text-primary font-weight-bold">Verify email</a>
                        @endif
                    </span>
                @endif
            </li>
            <li class="mb-2 d-flex align-items-start" style="gap: 10px;">
                @if (! empty($kyc['has_nin_or_bvn']) || (int) ($kyc['tier'] ?? 0) >= 2)
                    <i class="fa-solid fa-circle-check text-success mt-1"></i>
                    <span class="text-white-50"><strong class="text-white">Tier 2 — NIN or BVN</strong> — Identity verification is on file.</span>
                @else
                    <i class="fa-regular fa-circle text-warning mt-1"></i>
                    <span class="text-white-50">
                        <strong class="text-white">Tier 2 — NIN or BVN</strong> — Complete <strong>account</strong> NIN or BVN verification (separate from Services marketplace). Limits increase after we record a success.
                    </span>
                @endif
            </li>
            <li class="d-flex align-items-start" style="gap: 10px;">
                @if ((int) ($kyc['tier'] ?? 0) >= 3)
                    <i class="fa-solid fa-circle-check text-success mt-1"></i>
                    <span class="text-white-50"><strong class="text-white">Tier 3 — VIP</strong> — Active on your account.</span>
                @else
                    <i class="fa-regular fa-circle text-white-50 mt-1"></i>
                    <span class="text-white-50"><strong class="text-white">Tier 3 — VIP</strong> — Higher limits are set by admin. Open a ticket if your business needs a review.</span>
                @endif
            </li>
        </ul>

        @if (! empty($kyc['needs_identity']))
            <div class="d-flex flex-wrap gap-2 mt-3">
                @if (! empty($kyc['nin_service_enabled']) && \Illuminate\Support\Facades\Route::has('account.kyc.nin'))
                    <a href="{{ route('account.kyc.nin') }}" class="btn btn-sm btn-primary rounded-pill px-3">
                        <i class="fa-solid fa-id-card mr-1"></i> Account NIN
                    </a>
                @endif
                @if (! empty($kyc['bvn_service_enabled']) && \Illuminate\Support\Facades\Route::has('account.kyc.bvn'))
                    <a href="{{ route('account.kyc.bvn') }}" class="btn btn-sm btn-outline-light rounded-pill px-3">
                        <i class="fa-solid fa-building-columns mr-1"></i> Account BVN
                    </a>
                @endif
                @if (\Illuminate\Support\Facades\Route::has('account.kyc.index'))
                    <a href="{{ route('account.kyc.index') }}" class="btn btn-sm btn-link text-white-50 px-2">
                        Account KYC hub
                    </a>
                @endif
            </div>
        @endif
    </div>
</div>
@endif
