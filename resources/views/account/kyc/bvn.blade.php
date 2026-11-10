@extends('layouts.nexus')

@section('title', 'Account BVN | ' . config('app.name'))

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-6 fade-up stagger-1">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-4">
            <h3 class="text-white mb-0 fw-bold">Account BVN verification</h3>
            <a href="{{ route('account.kyc.index') }}" class="small text-primary font-weight-bold">Hub</a>
        </div>

        @if ($hasAccountBvn)
            <div class="alert alert-success border-0 rounded-3" style="background: rgba(34, 197, 94, 0.15); color: #bbf7d0;">
                @if (!empty($hasTier2Identity))
                    Tier 2 is already completed on your account. No further NIN/BVN verification is required.
                @else
                    Your account already has a successful BVN verification on file.
                @endif
            </div>
        @else
            <p class="text-white-50 small mb-4">
                This step is for <strong class="text-white">KYC tier upgrade</strong> only. We attempt virtual account generation using your BVN and upgrade you when banking partners confirm it.
                @if (Route::has('services.bvn'))
                    For match/combi and other BVN tools, use <a href="{{ route('services.bvn') }}" class="text-primary font-weight-bold">Services → BVN</a>.
                @endif
            </p>
            <p class="text-white-50 small mb-3">No wallet debit for this verification step.</p>

            <div class="card glass-card border-0 rounded-4 p-4" style="background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.06) !important;">
                <form method="POST" action="{{ route('account.kyc.bvn.submit') }}">
                    @csrf
                    <div class="form-group mb-4">
                        <label class="text-white-50 small">Bank Verification Number (BVN)</label>
                        <input type="text" name="bvn" value="{{ old('bvn') }}" class="form-control bg-dark text-white border-secondary" required maxlength="11" inputmode="numeric" pattern="\d{11}" autocomplete="off" placeholder="11-digit BVN">
                        @error('bvn')
                            <span class="text-danger small d-block mt-1">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group mb-4">
                        <label class="text-white-50 small d-flex justify-content-between align-items-center mb-1">
                            <span>Phone Number</span>
                            @if (!empty($user->number ?? auth()->user()->number))
                                <span class="badge rounded-pill px-2 py-0.5 text-success" style="font-size: 0.72rem; background: rgba(34, 197, 94, 0.15); border: 1px solid rgba(34, 197, 94, 0.3);">Saved on profile</span>
                            @else
                                <span class="text-warning small" style="font-size: 0.72rem;">Required for Tier 2 bank verification</span>
                            @endif
                        </label>
                        <input type="tel" name="phone_number" value="{{ old('phone_number', $user->number ?? auth()->user()->number) }}" class="form-control bg-dark text-white border-secondary" placeholder="e.g. 08012345678" maxlength="15" autocomplete="tel" {{ empty($user->number ?? auth()->user()->number) ? 'required' : '' }}>
                        <small class="text-white-50 d-block mt-1" style="font-size: 0.72rem;">Banking partners require this to link and issue your Tier 2 virtual bank account.</small>
                        @error('phone_number')
                            <span class="text-danger small d-block mt-1">{{ $message }}</span>
                        @enderror
                    </div>

                    <button type="submit" class="btn btn-primary btn-block rounded-pill py-3 font-weight-bold">
                        Submit for account KYC
                    </button>
                </form>
            </div>
        @endif
    </div>
</div>
@endsection
