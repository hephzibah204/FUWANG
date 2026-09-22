@extends('layouts.nexus')

@section('title', 'Referral Tiers | Admin')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="font-weight-bold text-white mb-1"><i class="fa-solid fa-layer-group text-warning mr-2"></i>Referral Tiers</h3>
            <p class="text-white-50 small mb-0">Configure multi-tier referral reward rates and thresholds.</p>
        </div>
        <a href="{{ route('admin.referral-tiers.create') }}" class="btn btn-warning font-weight-bold">
            <i class="fa-solid fa-plus mr-1"></i> Add Tier
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show text-white bg-success border-0 mb-4" role="alert">
            <i class="fa-solid fa-circle-check mr-2"></i>{{ session('success') }}
            <button type="button" class="close text-white" data-dismiss="alert" data-bs-dismiss="alert" aria-label="Close" style="background: transparent; border: 0; font-size: 1.5rem; opacity: 0.8;">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    <div class="card glass-card border-0 shadow-sm rounded-4 overflow-hidden">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 text-white">
                    <thead style="background: rgba(255,255,255,0.05);">
                        <tr>
                            <th>#</th>
                            <th>Tier Name</th>
                            <th>Minimum Referrals Required</th>
                            <th>Commission Rate (%)</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($tiers as $index => $tier)
                            <tr style="border-bottom: 1px solid rgba(255,255,255,0.05);">
                                <td>{{ $index + 1 }}</td>
                                <td><span class="badge bg-primary px-3 py-2 fs-6">{{ $tier->name }}</span></td>
                                <td><i class="fa-solid fa-users text-info me-2"></i>{{ number_format($tier->minimum_referrals) }} referrals</td>
                                <td><strong class="text-success">{{ number_format($tier->commission_rate, 2) }}%</strong></td>
                                <td class="text-end">
                                    <a href="{{ route('admin.referral-tiers.edit', $tier) }}" class="btn btn-sm btn-outline-light me-1">
                                        <i class="fa-solid fa-pen-to-square me-1"></i> Edit
                                    </a>
                                    <form action="{{ route('admin.referral-tiers.destroy', $tier) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this referral tier?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger">
                                            <i class="fa-solid fa-trash me-1"></i> Delete
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-5 text-white-50">
                                    <i class="fa-solid fa-folder-open fa-3x mb-3 text-secondary d-block"></i>
                                    No referral tiers configured yet.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
