@extends('layouts.nexus')

@section('title', 'Referrals | ' . config('app.name'))

@section('content')
@include('dashboard_styles')
<div class="nexus-dashboard">
    <div class="welcome-hero mb-5">
        <div class="hero-bg-accent"></div>
        <div class="row align-items-center position-relative">
            <div class="col-lg-7">
                <div class="hero-welcome-text">
                    <h1 class="display-4 font-weight-bold mb-2">Refer & Earn</h1>
                    <p class="text-white-50 lead mb-4">Share Fuwa with your friends and earn rewards when they fund their wallets.</p>
                    
                    <div class="hero-stats-row d-flex gap-4">
                        <div class="h-stat">
                            <div class="h-stat-label">Total Referrals</div>
                            <div class="h-stat-val">{{ number_format($stats['total']) }}</div>
                        </div>
                        <div class="h-stat">
                            <div class="h-stat-label">Active (Funded)</div>
                            <div class="h-stat-val">{{ number_format($stats['funded']) }}</div>
                        </div>
                        <div class="h-stat">
                            <div class="h-stat-label">Total Earnings</div>
                            <div class="h-stat-val">₦{{ number_format($stats['earnings'], 2) }}</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-5">
                <div class="panel-card p-4" style="background: rgba(30, 41, 59, 0.5); border: 1px solid rgba(139, 92, 246, 0.3);">
                    <h3 class="h6 font-weight-bold text-white mb-3">Your Referral Link</h3>
                    <div class="d-flex flex-column gap-3">
                        <div class="ref-card-modern p-3 rounded-lg" style="background: rgba(15, 23, 42, 0.5); border: 1px solid rgba(255,255,255,0.1);">
                            <div class="small text-muted mb-2">Referral Code</div>
                            <div class="d-flex align-items-center justify-content-between">
                                <code class="text-primary font-weight-bold" style="font-size: 1.2rem;">{{ Auth::user()->referral_id }}</code>
                                <button class="btn btn-sm btn-primary py-1 px-3 ref-copy-code" data-code="{{ Auth::user()->referral_id }}" style="border-radius: 8px;">Copy</button>
                            </div>
                        </div>
                        <div class="ref-card-modern p-3 rounded-lg" style="background: rgba(15, 23, 42, 0.5); border: 1px solid rgba(255,255,255,0.1);">
                            <div class="small text-muted mb-2">Referral Link</div>
                            <div class="d-flex align-items-center justify-content-between overflow-hidden">
                                <span class="text-truncate text-white-50 small mr-2">{{ $link }}</span>
                                <button class="btn btn-sm btn-glass py-1 px-3 ref-copy-link" data-link="{{ $link }}" style="border-radius: 8px; flex-shrink: 0;">Copy Link</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-12 mb-4">
            <div class="panel-card">
                <div class="panel-hdr">
                    <h3 class="h6 font-weight-bold m-0 text-white"><i class="fa-solid fa-users mr-2 text-primary"></i> Recent Referrals</h3>
                </div>
                <div class="table-responsive">
                    <table class="table table-dark table-hover mb-0" style="background: transparent;">
                        <thead>
                            <tr style="border-bottom: 2px solid rgba(255,255,255,0.05);">
                                <th class="border-0 px-4 py-3 small text-muted text-uppercase">User</th>
                                <th class="border-0 px-4 py-3 small text-muted text-uppercase">Status</th>
                                <th class="border-0 px-4 py-3 small text-muted text-uppercase">Date Registered</th>
                                <th class="border-0 px-4 py-3 small text-muted text-uppercase">Reward</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recent as $ref)
                                <tr style="border-bottom: 1px solid rgba(255,255,255,0.05);">
                                    <td class="px-4 py-3">
                                        <div class="d-flex align-items-center">
                                            <div class="avatar-sm mr-3 bg-primary rounded-circle d-flex align-items-center justify-content-center" style="width: 32px; height: 32px; font-size: 0.8rem;">
                                                {{ strtoupper(substr($ref->referred->fullname ?? $ref->referred->username, 0, 1)) }}
                                            </div>
                                            <div>
                                                <div class="font-weight-bold text-white small">{{ $ref->referred->fullname ?? $ref->referred->username }}</div>
                                                <div class="x-small text-muted">{{ $ref->referred->username }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3">
                                        @php
                                            $statusClass = match($ref->status) {
                                                'registered' => 'badge-info',
                                                'funded' => 'badge-warning',
                                                'rewarded' => 'badge-success',
                                                default => 'badge-secondary'
                                            };
                                        @endphp
                                        <span class="badge {{ $statusClass }} px-2 py-1" style="border-radius: 6px;">{{ ucfirst($ref->status) }}</span>
                                    </td>
                                    <td class="px-4 py-3 small text-white-50">
                                        {{ $ref->registered_at->format('M d, Y') }}
                                    </td>
                                    <td class="px-4 py-3">
                                        @if($ref->reward_status === 'paid')
                                            <span class="text-success font-weight-bold small">+₦{{ number_format($ref->reward_amount, 2) }}</span>
                                        @elseif($ref->reward_status === 'failed')
                                            <span class="text-danger small">Failed</span>
                                        @else
                                            <span class="text-muted small">Pending</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center py-5 opacity-50">
                                        <i class="fa-solid fa-user-plus mb-3 d-block" style="font-size: 2rem;"></i>
                                        <p class="m-0">You haven't referred anyone yet.</p>
                                    </td>
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
    $(document).ready(function() {
        $('.ref-copy-code').click(function() {
            let code = $(this).data('code');
            copyToClipboard(code);
            $(this).text('Copied!');
            setTimeout(() => $(this).text('Copy'), 2000);
        });

        $('.ref-copy-link').click(function() {
            let link = $(this).data('link');
            copyToClipboard(link);
            $(this).text('Copied Link!');
            setTimeout(() => $(this).text('Copy Link'), 2000);
        });

        function copyToClipboard(text) {
            const temp = document.createElement('input');
            document.body.appendChild(temp);
            temp.value = text;
            temp.select();
            document.execCommand('copy');
            document.body.removeChild(temp);
        }
    });
</script>
@endpush
