@extends('layouts.nexus')

@section('title', 'Referrals')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <h4 class="card-title">Referral Statistics</h4>
                <div class="row">
                    <div class="col-md-3">
                        <div class="card-body">
                            <h5 class="card-title">Total Referrals</h5>
                            <p class="card-text">{{ $stats['total'] }}</p>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card-body">
                            <h5 class="card-title">Registered Referrals</h5>
                            <p class="card-text">{{ $stats['registered'] }}</p>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card-body">
                            <h5 class="card-title">Funded Referrals</h5>
                            <p class="card-text">{{ $stats['funded'] }}</p>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card-body">
                            <h5 class="card-title">Total Earnings</h5>
                            <p class="card-text">{{ $stats['earnings'] }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row mt-4">
    <div class="col-md-6">
        <div class="card">
            <div class="card-body">
                <h4 class="card-title">Your Referral Link</h4>
                <div class="input-group">
                    <input type="text" id="referralLinkInput" class="form-control" value="{{ $referralLink }}" readonly>
                    <div class="input-group-append">
                        <button class="btn btn-primary" type="button" onclick="copyReferralLink()">Copy Full URL</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card">
            <div class="card-body">
                <h4 class="card-title">Your Current Tier</h4>
                <p class="card-text">You are currently in the <strong>{{ $stats['tier'] }}</strong> tier.</p>
            </div>
        </div>
    </div>
</div>

<div class="row mt-4">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <h4 class="card-title">Referral Tiers</h4>
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Tier</th>
                                <th>Minimum Referrals</th>
                                <th>Commission Rate</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($tiers as $tier)
                                <tr>
                                    <td>{{ $tier->name }}</td>
                                    <td>{{ $tier->minimum_referrals }}</td>
                                    <td>{{ $tier->commission_rate }}%</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row mt-4">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <h4 class="card-title">Recent Referrals</h4>
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>User</th>
                                <th>Status</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($recentReferrals as $referral)
                                <tr>
                                    <td>{{ $referral->referred->fullname }}</td>
                                    <td>{{ $referral->status }}</td>
                                    <td>{{ $referral->created_at->format('Y-m-d') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="text-center">No recent referrals.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function copyReferralLink() {
        const input = document.getElementById('referralLinkInput');
        if (!input) return;

        const text = input.value || '';
        if (!text) return;

        if (navigator.clipboard && window.isSecureContext) {
            navigator.clipboard.writeText(text)
                .then(() => {
                    if (window.nexusToast) {
                        window.nexusToast('Referral URL copied successfully.');
                    } else {
                        alert('Referral URL copied successfully.');
                    }
                })
                .catch(() => fallbackCopy(input, text));
            return;
        }

        fallbackCopy(input, text);
    }

    function fallbackCopy(input, text) {
        input.focus();
        input.select();
        input.setSelectionRange(0, text.length);
        document.execCommand('copy');
        if (window.nexusToast) {
            window.nexusToast('Referral URL copied successfully.');
        } else {
            alert('Referral URL copied successfully.');
        }
    }
</script>
@endpush
