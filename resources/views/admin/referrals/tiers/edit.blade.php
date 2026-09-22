@extends('layouts.nexus')

@section('title', 'Edit Referral Tier | Admin')

@section('content')
<div class="container py-4" style="max-width: 650px;">
    <div class="mb-4 d-flex align-items-center justify-content-between">
        <h3 class="font-weight-bold text-white mb-0"><i class="fa-solid fa-pen-to-square text-warning me-2"></i>Edit Referral Tier</h3>
        <a href="{{ route('admin.referral-tiers.index') }}" class="btn btn-outline-light btn-sm">
            <i class="fa-solid fa-arrow-left me-1"></i> Back
        </a>
    </div>

    @if($errors->any())
        <div class="alert alert-danger py-2 px-3 small rounded-3 mb-3">
            <i class="fa-solid fa-circle-exclamation me-2"></i>{{ $errors->first() }}
        </div>
    @endif

    <div class="card glass-card border-0 shadow rounded-4 p-4">
        <form action="{{ route('admin.referral-tiers.update', $tier) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="mb-3">
                <label for="name" class="form-label text-white-50 small">Tier Name</label>
                <input type="text" id="name" name="name" class="form-control bg-dark text-white border-secondary" value="{{ old('name', $tier->name) }}" required>
            </div>

            <div class="mb-3">
                <label for="minimum_referrals" class="form-label text-white-50 small">Minimum Referrals Required</label>
                <input type="number" id="minimum_referrals" name="minimum_referrals" class="form-control bg-dark text-white border-secondary" min="0" value="{{ old('minimum_referrals', $tier->minimum_referrals) }}" required>
            </div>

            <div class="mb-4">
                <label for="commission_rate" class="form-label text-white-50 small">Commission Rate (%)</label>
                <input type="number" step="0.01" id="commission_rate" name="commission_rate" class="form-control bg-dark text-white border-secondary" min="0" max="100" value="{{ old('commission_rate', $tier->commission_rate) }}" required>
            </div>

            <button type="submit" class="btn btn-warning font-weight-bold w-100 py-2.5">
                <i class="fa-solid fa-save me-2"></i> Update Referral Tier
            </button>
        </form>
    </div>
</div>
@endsection
