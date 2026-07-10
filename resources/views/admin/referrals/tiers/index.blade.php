@extends('layouts.nexus')

@section('title', 'Referral Tiers - Fuwa.NG Control')

@section('content')
<div class="row mb-4">
    <div class="col-12 d-flex flex-column flex-md-row align-items-start align-items-md-center justify-content-between">
        <div>
            <h3 class="text-white mb-1 font-weight-bold"><i class="fa fa-sitemap text-primary mr-2"></i> Referral Tiers</h3>
            <p class="text-white-50 mb-0">Manage tier thresholds and commission rates</p>
        </div>
        <div class="mt-3 mt-md-0">
            <a href="{{ route('admin.referral-tiers.create') }}" class="btn btn-primary rounded-pill px-4 py-2 font-weight-bold">
                <i class="fa fa-plus-circle mr-2"></i> New Tier
            </a>
        </div>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success border-0" style="background: rgba(34,197,94,0.12); border: 1px solid rgba(34,197,94,0.25) !important; color: #d1fae5;">
        {{ session('success') }}
    </div>
@endif
@if ($errors->any())
    <div class="alert alert-danger border-0" style="background: rgba(220,53,69,0.12); border: 1px solid rgba(220,53,69,0.25) !important; color: #ffd0d7;">
        {{ $errors->first() }}
    </div>
@endif

<div class="card glass-card border-0 rounded-lg p-0" style="background: rgba(255,255,255,0.02); backdrop-filter: blur(10px); border: 1px solid rgba(255,255,255,0.05) !important;">
    <div class="table-responsive">
        <table class="table mb-0 text-white">
            <thead style="background: rgba(255,255,255,0.05);">
                <tr>
                    <th class="border-top-0 text-white-50 font-weight-normal py-3 px-4">Tier</th>
                    <th class="border-top-0 text-white-50 font-weight-normal py-3">Minimum Referrals</th>
                    <th class="border-top-0 text-white-50 font-weight-normal py-3">Commission Rate</th>
                    <th class="border-top-0 text-white-50 font-weight-normal py-3 text-right px-4">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($tiers as $tier)
                    <tr style="border-bottom: 1px solid rgba(255,255,255,0.05);">
                        <td class="py-3 px-4 font-weight-bold">{{ $tier->name }}</td>
                        <td class="py-3 text-white-50">{{ $tier->minimum_referrals }}</td>
                        <td class="py-3 text-white-50">{{ $tier->commission_rate }}%</td>
                        <td class="py-3 px-4 text-right">
                            <a href="{{ route('admin.referral-tiers.edit', $tier->id) }}" class="btn btn-sm btn-outline-secondary">Edit</a>
                            <form action="{{ route('admin.referral-tiers.destroy', $tier->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this tier?');">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger" type="submit">Delete</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="text-center py-5 text-white-50">No tiers found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection

