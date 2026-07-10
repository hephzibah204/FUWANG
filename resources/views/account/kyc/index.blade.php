@extends('layouts.nexus')

@section('title', 'Account KYC | ' . config('app.name'))

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8 fade-up stagger-1">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-4">
            <h3 class="text-white mb-0 fw-bold">Account KYC</h3>
            <a href="{{ route('profile') }}" class="small text-primary font-weight-bold">Back to profile</a>
        </div>

        @if (session('status'))
            <div class="alert alert-success border-0 rounded-3 mb-4" style="background: rgba(34, 197, 94, 0.15); color: #bbf7d0;">
                {{ session('status') }}
            </div>
        @endif

        <p class="text-white-50 small mb-4">
            Use this flow to upgrade your <strong class="text-white">KYC tier</strong> with NIN or BVN. We validate by attempting virtual account generation with configured payment providers, so there is no wallet charge for this step.
        </p>
        @php
            $tier2Source = $bvnVerifiedProvider ?: $ninVerifiedProvider;
        @endphp
        @if (!empty($hasTier2Identity))
            <div class="alert border-0 rounded-3 mb-4" style="background: rgba(34, 197, 94, 0.15); color: #bbf7d0;">
                <strong>Tier 2 already completed.</strong>
                @if (!empty($tier2Source))
                    Verified via <strong>{{ $tier2Source }}</strong>.
                @else
                    A successful BVN/NIN account KYC verification is already on file.
                @endif
                You do not need to run another Tier 2 verification.
            </div>
        @endif

        <div class="row g-3">
            @if (Route::has('account.kyc.nin'))
                <div class="col-md-6">
                    <div class="card glass-card border-0 rounded-4 p-4 h-100" style="background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.06) !important;">
                        <h5 class="text-white font-weight-bold mb-2"><i class="fa-solid fa-id-card text-primary mr-2"></i>NIN</h5>
                        <p class="text-white-50 small mb-3">
                            @if ($hasAccountNin)
                                <span class="text-success font-weight-bold">Completed</span> — on file for your account.
                                @if(!empty($ninVerifiedProvider))
                                    <span class="badge badge-pill ml-2" style="background: rgba(59, 130, 246, 0.18); color: #bfdbfe; border: 1px solid rgba(59, 130, 246, 0.35);">
                                        Verified via {{ $ninVerifiedProvider }}
                                    </span>
                                @elseif(!empty($hasTier2Identity))
                                    <span class="badge badge-pill ml-2" style="background: rgba(34, 197, 94, 0.18); color: #bbf7d0; border: 1px solid rgba(34, 197, 94, 0.35);">
                                        Tier 2 already completed
                                    </span>
                                @endif
                            @else
                                No wallet fee for this verification step.
                            @endif
                        </p>
                        <a href="{{ route('account.kyc.nin') }}" class="btn btn-primary rounded-pill {{ $hasAccountNin ? 'disabled' : '' }}">
                            {{ $hasAccountNin ? 'Tier 2 completed' : 'Start NIN' }}
                        </a>
                        @if ($hasAccountNin)
                            <div class="small text-white-50 mt-2">No further Tier 2 action is required.</div>
                        @endif
                    </div>
                </div>
            @endif
            @if (Route::has('account.kyc.bvn'))
                <div class="col-md-6">
                    <div class="card glass-card border-0 rounded-4 p-4 h-100" style="background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.06) !important;">
                        <h5 class="text-white font-weight-bold mb-2"><i class="fa-solid fa-building-columns text-info mr-2"></i>BVN</h5>
                        <p class="text-white-50 small mb-3">
                            @if ($hasAccountBvn)
                                <span class="text-success font-weight-bold">Completed</span> — on file for your account.
                                @if(!empty($bvnVerifiedProvider))
                                    <span class="badge badge-pill ml-2" style="background: rgba(6, 182, 212, 0.18); color: #a5f3fc; border: 1px solid rgba(6, 182, 212, 0.35);">
                                        Verified via {{ $bvnVerifiedProvider }}
                                    </span>
                                @elseif(!empty($hasTier2Identity))
                                    <span class="badge badge-pill ml-2" style="background: rgba(34, 197, 94, 0.18); color: #bbf7d0; border: 1px solid rgba(34, 197, 94, 0.35);">
                                        Tier 2 already completed
                                    </span>
                                @endif
                            @else
                                No wallet fee for this verification step.
                            @endif
                        </p>
                        <a href="{{ route('account.kyc.bvn') }}" class="btn btn-outline-light rounded-pill {{ $hasAccountBvn ? 'disabled' : '' }}">
                            {{ $hasAccountBvn ? 'Tier 2 completed' : 'Start BVN' }}
                        </a>
                        @if ($hasAccountBvn)
                            <div class="small text-white-50 mt-2">No further Tier 2 action is required.</div>
                        @endif
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
