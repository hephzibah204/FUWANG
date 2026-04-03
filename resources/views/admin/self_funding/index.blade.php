@extends('layouts.nexus')

@section('title', 'Self-Funding Mechanism - Super Admin Control')

@section('content')
<div class="row mb-4 animate__animated animate__fadeInDown">
    <div class="col-12">
        <h3 class="text-white mb-1 font-weight-bold"><i class="fa fa-vault text-primary mr-2"></i> Self-Funding Mechanism</h3>
        <p class="text-white-50 mb-0">Internal funding tool for testing and platform verification.</p>
    </div>
</div>

<div class="row">
    <div class="col-lg-6 col-12 mb-4">
        <div class="card glass-card border-0 rounded-lg p-5" style="background: rgba(255,255,255,0.02); backdrop-filter: blur(10px); border: 1px solid rgba(255,255,255,0.05) !important;">
            <div class="text-center mb-4">
                <div class="rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 70px; height: 70px; background: rgba(99, 102, 241, 0.1); color: #818cf8;">
                    <i class="fa fa-shield-halved fa-2x"></i>
                </div>
                <h4 class="text-white font-weight-bold">Super Admin Override</h4>
                <p class="text-white-50">Add credits to your account without external payment gateways.</p>
            </div>

            <div class="p-3 mb-4 rounded" style="background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.05);">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-white-50">Target User</span>
                    <span class="text-white font-weight-bold">{{ $user->fullname }} ({{ $user->email }})</span>
                </div>
                <div class="d-flex justify-content-between align-items-center">
                    <span class="text-white-50">Current Balance</span>
                    <h5 class="text-success font-weight-bold mb-0">₦{{ number_format($balance, 2) }}</h5>
                </div>
            </div>

            <form id="selfFundingForm">
                @csrf
                <div class="form-group mb-4">
                    <label class="text-white-50 font-weight-bold">Funding Amount (₦)</label>
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text bg-transparent text-white-50 border-secondary">₦</span>
                        </div>
                        <input type="number" step="0.01" min="0.01" max="{{ $limit }}" name="amount" class="form-control bg-transparent text-white border-secondary" placeholder="0.00" required>
                    </div>
                    <small class="text-info mt-2 d-block"><i class="fa fa-info-circle mr-1"></i> Configured max limit per transaction: ₦{{ number_format($limit, 2) }}</small>
                </div>

                <div class="alert alert-info border-0 mb-4" style="background: rgba(59, 130, 246, 0.1); color: #60a5fa; font-size: 0.85rem;">
                    <i class="fa fa-circle-info mr-2"></i> This action bypasses all payment processors and directly modifies the database. All activities are audit-logged for security.
                </div>

                <button type="submit" class="btn btn-primary btn-block py-3 font-weight-bold shadow-sm" style="border-radius: 12px;">
                    <i class="fa fa-plus-circle mr-2"></i> Add Funds Immediately
                </button>
            </form>
        </div>
    </div>

    <div class="col-lg-6 col-12">
        <div class="card glass-card border-0 rounded-lg p-4" style="background: rgba(255,255,255,0.02); backdrop-filter: blur(10px); border: 1px solid rgba(255,255,255,0.05) !important;">
            <h5 class="text-white font-weight-bold mb-4"><i class="fa fa-history text-info mr-2"></i> Recent Self-Funding Activities</h5>
            <div class="table-responsive">
                <table class="table table-dark table-hover bg-transparent mb-0">
                    <thead class="text-white-50">
                        <tr>
                            <th class="border-secondary px-0">Date</th>
                            <th class="border-secondary">Amount</th>
                            <th class="border-secondary">Reference</th>
                            <th class="border-secondary text-right">Status</th>
                        </tr>
                    </thead>
                    <tbody id="activityList">
                        @php
                            $recentSelfFunds = \App\Models\Funding::where('funding_type', 'Self-Funding')
                                ->where('email', $admin->email)
                                ->latest()
                                ->take(10)
                                ->get();
                        @endphp
                        @forelse($recentSelfFunds as $fund)
                            <tr>
                                <td class="border-secondary px-0 small">{{ $fund->created_at->format('M d, H:i') }}</td>
                                <td class="border-secondary text-success font-weight-bold">₦{{ number_format($fund->amount, 2) }}</td>
                                <td class="border-secondary text-white-50 small">{{ $fund->reference }}</td>
                                <td class="border-secondary text-right"><span class="badge badge-success px-2 py-1">Internal Success</span></td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center text-white-50 py-4">No recent self-funding activities found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('selfFundingForm')?.addEventListener('submit', function(e) {
    e.preventDefault();
    const btn = this.querySelector('button[type="submit"]');
    const originalText = btn.innerHTML;
    
    if (!confirm('Are you sure you want to add these credits to your account?')) return;

    btn.disabled = true;
    btn.innerHTML = '<i class="fa fa-spinner fa-spin mr-2"></i> Processing...';

    const formData = new FormData(this);
    
    fetch('{{ route("admin.self_funding.fund") }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        },
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.status) {
            Swal.fire({
                icon: 'success',
                title: 'Success!',
                text: data.message,
                background: '#1a1f2d',
                color: '#fff',
                confirmButtonColor: '#3b82f6'
            }).then(() => {
                window.location.reload();
            });
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Failed',
                text: data.message,
                background: '#1a1f2d',
                color: '#fff',
                confirmButtonColor: '#3b82f6'
            });
            btn.disabled = false;
            btn.innerHTML = originalText;
        }
    })
    .catch(error => {
        console.error('Error:', error);
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'An unexpected error occurred.',
            background: '#1a1f2d',
            color: '#fff',
            confirmButtonColor: '#3b82f6'
        });
        btn.disabled = false;
        btn.innerHTML = originalText;
    });
});
</script>
@endsection
