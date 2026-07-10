@extends('layouts.nexus')

@section('title', 'Create Referral Tier - Fuwa.NG Control')

@section('content')
<div class="row mb-4">
    <div class="col-12 d-flex flex-column flex-md-row align-items-start align-items-md-center justify-content-between">
        <div>
            <h3 class="text-white mb-1 font-weight-bold"><i class="fa fa-plus-circle text-primary mr-2"></i> New Referral Tier</h3>
            <p class="text-white-50 mb-0">Add a new tier for referral commissions</p>
        </div>
        <div class="mt-3 mt-md-0">
            <a href="{{ route('admin.referral-tiers.index') }}" class="btn btn-outline-secondary rounded-pill px-4 py-2">Back</a>
        </div>
    </div>
</div>

@if ($errors->any())
    <div class="alert alert-danger border-0" style="background: rgba(220,53,69,0.12); border: 1px solid rgba(220,53,69,0.25) !important; color: #ffd0d7;">
        {{ $errors->first() }}
    </div>
@endif

<div class="card glass-card border-0 rounded-lg p-4" style="background: rgba(255,255,255,0.02); backdrop-filter: blur(10px); border: 1px solid rgba(255,255,255,0.05) !important;">
    <form method="POST" action="{{ route('admin.referral-tiers.store') }}">
        @csrf
        <div class="form-row">
            <div class="form-group col-md-4">
                <label class="text-white-50 small">Name</label>
                <input type="text" name="name" class="form-control" required value="{{ old('name') }}" placeholder="e.g., Bronze">
            </div>
            <div class="form-group col-md-4">
                <label class="text-white-50 small">Minimum referrals</label>
                <input type="number" name="minimum_referrals" class="form-control" required min="0" value="{{ old('minimum_referrals', 0) }}">
            </div>
            <div class="form-group col-md-4">
                <label class="text-white-50 small">Commission rate (%)</label>
                <input type="number" name="commission_rate" class="form-control" required min="0" max="100" step="0.01" value="{{ old('commission_rate', 0) }}">
            </div>
        </div>

        <div class="mt-4 d-flex justify-content-between">
            <button type="submit" class="btn btn-primary px-4"><i class="fa fa-save mr-2"></i>Create</button>
            <a href="{{ route('admin.referral-tiers.index') }}" class="btn btn-outline-secondary px-4">Cancel</a>
        </div>
    </form>
</div>
@endsection

