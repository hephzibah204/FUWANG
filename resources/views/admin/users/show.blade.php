@extends('layouts.nexus')

@section('title', ($user->fullname ?? 'User Profile') . ' - Admin')

@section('content')
<div class="row mb-4">
    <div class="col-12 d-flex justify-content-between align-items-center">
        <div class="d-flex align-items-center">
            <a href="{{ route('admin.users.index') }}" class="btn btn-dark rounded-circle mr-3 d-flex align-items-center justify-content-center" style="width: 40px; height: 40px; background: rgba(255,255,255,0.05) !important; border: 1px solid rgba(255,255,255,0.1);">
                <i class="fa fa-arrow-left text-white"></i>
            </a>
            <div>
                <h3 class="text-white mb-0 fw-bold">User Profile</h3>
                <p class="text-white-50 mb-0">Manage account details for <span class="text-white">{{ $user->fullname }}</span></p>
            </div>
        </div>
        @php
            $hasRecentTransactions = !empty($hasTransactions) && !empty($transactions);
            $refundButtonStyle = 'background: rgba(245,158,11,0.15); color: #f59e0b; border: 1px solid rgba(245,158,11,0.3); font-weight: 600;';

            if (! $hasRecentTransactions) {
                $refundButtonStyle .= ' opacity: 0.45; cursor: not-allowed;';
            }
        @endphp
        <div class="d-flex gap-2" style="gap: 10px;">
            <button type="button" class="btn btn-sm rounded-pill px-4 fund-btn" data-id="{{ $user->id }}" data-email="{{ $user->email }}" data-name="{{ $user->fullname }}" data-balance="{{ $user->balance->user_balance ?? 0 }}" style="background: rgba(34,197,94,0.15); color: #22c55e; border: 1px solid rgba(34,197,94,0.3); font-weight: 600;">
                <i class="fa fa-plus mr-1"></i>Fund
            </button>
            <button type="button" class="btn btn-sm rounded-pill px-4 refund-btn" data-id="{{ $user->id }}" data-email="{{ $user->email }}" data-name="{{ $user->fullname }}" data-balance="{{ $user->balance->user_balance ?? 0 }}" @disabled(!$hasRecentTransactions) style="{{ $refundButtonStyle }}">
                <i class="fa fa-rotate-left mr-1"></i>Refund
            </button>
            <button type="button" class="btn btn-sm rounded-pill px-4 deduct-btn" data-id="{{ $user->id }}" data-email="{{ $user->email }}" data-name="{{ $user->fullname }}" data-balance="{{ $user->balance->user_balance ?? 0 }}" style="background: rgba(239,68,68,0.15); color: #ef4444; border: 1px solid rgba(239,68,68,0.3); font-weight: 600;">
                <i class="fa fa-minus mr-1"></i>Deduct
            </button>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-4">
        <!-- User Info Card -->
        <div class="card glass-card border-0 mb-4" style="background: rgba(255,255,255,0.02); border: 1px solid rgba(255,255,255,0.05) !important;">
            <div class="card-body text-center py-5">
                <div class="rounded-circle d-flex align-items-center justify-content-center mx-auto mb-4 font-weight-bold text-white" style="width: 100px; height: 100px; background: linear-gradient(135deg, rgba(99,102,241,0.4), rgba(79,70,229,0.2)); border: 2px solid rgba(99,102,241,0.3); font-size: 2.5rem;">
                    {{ strtoupper(substr($user->fullname ?? 'U', 0, 1)) }}
                </div>
                <h4 class="text-white mb-1 fw-bold">{{ $user->fullname }}</h4>
                <p class="text-white-50 mb-4">{{ $user->email }}</p>
                
                <div class="d-flex justify-content-center gap-2 mb-4" style="gap:10px;">
                    @php($status = $user->user_status ?? 'active')
                    @if($status === 'active')
                        <span class="badge rounded-pill px-3 py-1" style="background: rgba(34,197,94,0.15); color: #22c55e; border: 1px solid rgba(34,197,94,0.3);">Active Account</span>
                    @elseif($status === 'suspended')
                        <span class="badge rounded-pill px-3 py-1" style="background: rgba(239,68,68,0.15); color: #ef4444; border: 1px solid rgba(239,68,68,0.3);">Suspended</span>
                    @else
                        <span class="badge rounded-pill px-3 py-1" style="background: rgba(255,255,255,0.06); color: rgba(255,255,255,0.65); border: 1px solid rgba(255,255,255,0.12);">Deleted</span>
                    @endif
                </div>

                <div class="p-3 rounded-3 mb-4" style="background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.05);">
                    <small class="text-white-50 text-uppercase d-block mb-1">Available Balance</small>
                    <h3 class="mb-0 font-weight-bold" style="color: #6ee7b7;">₦{{ number_format($user->balance->user_balance ?? 0, 2) }}</h3>
                </div>

                <div class="d-grid gap-2" style="display: grid; gap: 8px;">
                    <button class="btn btn-outline-primary btn-block rounded-pill reset-pass-btn" data-id="{{ $user->id }}" data-name="{{ $user->fullname }}" style="border-color: rgba(59,130,246,0.3); background: rgba(59,130,246,0.05); color: #93c5fd;">
                        <i class="fa fa-key mr-2"></i>Reset Password
                    </button>
                    @if(($user->user_status ?? 'active') === 'suspended')
                        <button class="btn btn-success btn-block rounded-pill status-btn" data-id="{{ $user->id }}" data-status="active" data-name="{{ $user->fullname }}" style="background: rgba(34,197,94,0.2); border-color: rgba(34,197,94,0.3); color: #22c55e;">
                            <i class="fa fa-check mr-2"></i>Activate Account
                        </button>
                    @else
                        <button class="btn btn-warning btn-block rounded-pill status-btn" data-id="{{ $user->id }}" data-status="suspended" data-name="{{ $user->fullname }}" style="background: rgba(245,158,11,0.2); border-color: rgba(245,158,11,0.3); color: #f59e0b;">
                            <i class="fa fa-ban mr-2"></i>Suspend Account
                        </button>
                    @endif
                    <button class="btn btn-outline-danger btn-block rounded-pill status-btn" data-id="{{ $user->id }}" data-status="deleted" data-name="{{ $user->fullname }}" style="border-color: rgba(239,68,68,0.3); background: rgba(239,68,68,0.05); color: #ef4444;">
                        <i class="fa fa-trash mr-2"></i>Delete User
                    </button>
                </div>
            </div>
        </div>

        <!-- KYC & Limits Card -->
        <div class="card glass-card border-0 mb-4" style="background: rgba(255,255,255,0.02); border: 1px solid rgba(255,255,255,0.05) !important;">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h6 class="text-white font-weight-bold mb-0">KYC & Transaction Limits</h6>
                    <span class="badge rounded-pill px-2" style="background: rgba(99,102,241,0.2); color: #818cf8; border: 1px solid rgba(99,102,241,0.3);">{{ $kycData['limits']['label'] }}</span>
                </div>

                <div class="mb-4">
                    <div class="d-flex justify-content-between mb-1">
                        <small class="text-white-50">Daily Progress</small>
                        <small class="text-white-50">₦{{ number_format($kycData['daily_spent'], 0) }} / ₦{{ number_format($kycData['limits']['daily'], 0) }}</small>
                    </div>
                    @php($dailyPercent = min(100, ($kycData['daily_spent'] / max(1, $kycData['limits']['daily'])) * 100))
                    <div class="progress" style="height: 6px; background: rgba(255,255,255,0.05); border-radius: 3px;">
                        <div class="progress-bar {{ $dailyPercent > 90 ? 'bg-danger' : ($dailyPercent > 70 ? 'bg-warning' : 'bg-success') }}" role="progressbar" style="width: {{ $dailyPercent }}%; border-radius: 3px;"></div>
                    </div>
                </div>

                <div class="mb-4">
                    <div class="d-flex justify-content-between mb-1">
                        <small class="text-white-50">Monthly Progress</small>
                        <small class="text-white-50">₦{{ number_format($kycData['monthly_spent'], 0) }} / ₦{{ number_format($kycData['limits']['monthly'], 0) }}</small>
                    </div>
                    @php($monthlyPercent = min(100, ($kycData['monthly_spent'] / max(1, $kycData['limits']['monthly'])) * 100))
                    <div class="progress" style="height: 6px; background: rgba(255,255,255,0.05); border-radius: 3px;">
                        <div class="progress-bar bg-info" role="progressbar" style="width: {{ $monthlyPercent }}%; border-radius: 3px;"></div>
                    </div>
                </div>

                <div class="p-2 rounded" style="background: rgba(255,255,255,0.02); border-left: 3px solid rgba(99,102,241,0.5);">
                    <div class="d-flex justify-content-between">
                        <small class="text-white-50">Single Tx Limit</small>
                        <small class="text-white font-weight-bold">₦{{ number_format($kycData['limits']['single'], 2) }}</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Meta Info -->
        <div class="card glass-card border-0" style="background: rgba(255,255,255,0.02); border: 1px solid rgba(255,255,255,0.05) !important;">
            <div class="card-body">
                <h6 class="text-white font-weight-bold mb-3">System Metadata</h6>
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-white-50 small">User ID</span>
                    <span class="text-white small">#{{ $user->id }}</span>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-white-50 small">Username</span>
                    <span class="text-white small">{{ $user->username ?? 'N/A' }}</span>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-white-50 small">Date Joined</span>
                    <span class="text-white small">{{ $user->created_at->format('M d, Y') }}</span>
                </div>
                <div class="d-flex justify-content-between">
                    <span class="text-white-50 small">Last Activity</span>
                    <span class="text-white small">{{ $user->updated_at->diffForHumans() }}</span>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-8">
        <!-- Content Area -->
        <div class="card glass-card border-0 mb-4" style="background: rgba(255,255,255,0.02); border: 1px solid rgba(255,255,255,0.05) !important;">
            <div class="card-header bg-transparent border-0 pt-4 px-4">
                <h5 class="text-white mb-0 font-weight-bold">Recent Transactions</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-borderless table-hover mb-0 text-white">
                        <thead style="background: rgba(255,255,255,0.03);">
                            <tr>
                                <th class="py-3 px-4 text-white-50 small text-uppercase font-weight-bold border-bottom-0">Reference</th>
                                <th class="py-3 px-4 text-white-50 small text-uppercase font-weight-bold border-bottom-0">Type</th>
                                <th class="py-3 px-4 text-white-50 small text-uppercase font-weight-bold border-bottom-0">Amount</th>
                                <th class="py-3 px-4 text-white-50 small text-uppercase font-weight-bold border-bottom-0">Date</th>
                                <th class="py-3 px-4 text-white-50 small text-uppercase font-weight-bold border-bottom-0 text-center">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse(($transactions ?? []) as $tx)
                                    @php
                                        $delta = (float) $tx->balance_before - (float) $tx->balance_after;
                                        $isDebit = $delta > 0;
                                        $statusPalette = [
                                            'success' => ['bg' => 'rgba(34,197,94,0.1)', 'fg' => '#22c55e'],
                                            'failed' => ['bg' => 'rgba(239,68,68,0.1)', 'fg' => '#ef4444'],
                                            'pending' => ['bg' => 'rgba(245,158,11,0.1)', 'fg' => '#f59e0b'],
                                        ];
                                        $statusColor = $statusPalette[$tx->status ?? ''] ?? ['bg' => 'rgba(255,255,255,0.06)', 'fg' => '#9ca3af'];
                                    @endphp
                                    <tr style="border-bottom: 1px solid rgba(255,255,255,0.03);">
                                        <td class="py-3 px-4 align-middle">
                                            <code class="text-white-50">{{ $tx->transaction_id }}</code>
                                        </td>
                                        <td class="py-3 px-4 align-middle">
                                            <span class="text-white">{{ \Illuminate\Support\Str::limit($tx->order_type ?? 'Payment', 30) }}</span>
                                        </td>
                                        <td class="py-3 px-4 align-middle">
                                            <span class="font-weight-bold {{ $isDebit ? 'text-danger' : 'text-success' }}">
                                                {{ $isDebit ? '-' : '+' }}₦{{ number_format(abs($delta), 2) }}
                                            </span>
                                        </td>
                                        <td class="py-3 px-4 align-middle">
                                            <small class="text-white-50">{{ $tx->created_at->format('d M, H:i') }}</small>
                                        </td>
                                        <td class="py-3 px-4 align-middle text-center">
                                            <span class="badge rounded-pill px-2 py-1" style="background: {{ $statusColor['bg'] }}; color: {{ $statusColor['fg'] }};">
                                                {{ ucfirst($tx->status ?? 'unknown') }}
                                            </span>
                                        </td>
                                    </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-5 text-white-50">No recent transactions found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer bg-transparent border-0 px-4 py-3 text-right">
                <a href="{{ route('admin.users.history', urlencode($user->email)) }}" class="btn btn-sm text-white-50" style="text-decoration: underline;">View Full History</a>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11" nonce="{{ $cspNonce ?? '' }}"></script>
<script nonce="{{ $cspNonce ?? '' }}">
function walletAction(actionType, email, name, currentBalance) {
    const isFund   = actionType === 'fund';
    const isRefund = actionType === 'refund';
    const label    = isFund ? 'Fund' : (isRefund ? 'Refund' : 'Deduct');
    const color    = isFund ? '#22c55e' : (isRefund ? '#eab308' : '#ef4444');
    const route    = isFund ? '{{ route("admin.users.fund") }}' : (isRefund ? '{{ route("admin.users.refund") }}' : '{{ route("admin.users.deduct") }}');

    Swal.fire({
        title: `${label} Wallet`,
        html: `
            <p class="text-white-50 small mb-3">Target: <strong class="text-white">${name}</strong> (${email})</p>
            ${!isFund ? `<p class="text-white-50 small mb-3">Current Balance: <strong style="color:#6ee7b7">₦${parseFloat(currentBalance).toLocaleString('en-NG', {minimumFractionDigits:2})}</strong></p>` : ''}
            <div style="display: flex; flex-direction: column; align-items: center; gap: 10px;">
                <input type="number" id="wallet_amount" class="form-control text-white" placeholder="Amount (₦)" min="1" step="0.01" style="background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); width: 80%; max-width: 300px;">
                <input type="text" id="wallet_note" class="form-control text-white" placeholder="Note / Reason (optional)" style="background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); width: 80%; max-width: 300px;">
            </div>
        `,
        background: '#141826',
        color: '#fff',
        confirmButtonColor: color,
        confirmButtonText: `${label} Wallet`,
        showCancelButton: true,
        cancelButtonColor: '#374151',
        focusConfirm: false,
        preConfirm: () => {
            const amountInput = Swal.getPopup().querySelector('#wallet_amount');
            const noteInput   = Swal.getPopup().querySelector('#wallet_note');
            const amount = amountInput ? amountInput.value : null;
            const note   = noteInput ? noteInput.value : '';
            if (!amount || +amount <= 0) {
                Swal.showValidationMessage('Please enter a valid amount greater than 0');
                return false;
            }
            return { amount, note };
        }
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire({
                title: 'Processing…',
                text: 'Updating account balance...',
                background: '#141826',
                color: '#fff',
                allowOutsideClick: false,
                didOpen: () => Swal.showLoading()
            });

            // Modernized with axios
            axios.post(route, {
                email: email,
                amount: result.value.amount,
                note: result.value.note
            })
            .then(res => {
                const data = res.data;
                Swal.fire({
                    icon: 'success',
                    title: 'Success!',
                    text: data.message || 'Balance updated successfully',
                    background: '#141826',
                    color: '#fff',
                    confirmButtonColor: '#3b82f6'
                }).then(() => location.reload());
            })
            .catch(err => {
                console.error('Wallet Action Failed:', err);
                const msg = err.response?.data?.message || err.message || 'An unexpected error occurred.';
                Swal.fire({
                    icon: 'error',
                    title: 'Operation Failed',
                    text: msg,
                    background: '#141826',
                    color: '#fff',
                    confirmButtonColor: '#ef4444'
                });
            });
        }
    });
}

$(document).on('click', '.fund-btn', function(e) {
    e.preventDefault();
    walletAction('fund', $(this).data('email'), $(this).data('name'), $(this).data('balance'));
});
$(document).on('click', '.refund-btn', function(e) {
    e.preventDefault();
    if ($(this).prop('disabled')) return;
    walletAction('refund', $(this).data('email'), $(this).data('name'), $(this).data('balance'));
});
$(document).on('click', '.deduct-btn', function(e) {
    e.preventDefault();
    walletAction('deduct', $(this).data('email'), $(this).data('name'), $(this).data('balance'));
});

$(document).on('click', '.reset-pass-btn', function(e) {
    e.preventDefault();
    const id = $(this).data('id');
    const name = $(this).data('name');
    const url = '{{ route("admin.users.reset_password", ["id" => "__ID__"]) }}'.replace('__ID__', String(id));

    Swal.fire({
        title: 'Reset Password',
        html: `
            <p class="text-white-50 small mb-3">Target: <strong class="text-white">${name}</strong></p>
            <input type="password" id="new_password" class="swal2-input" placeholder="Leave empty to auto-generate" style="font-size:1rem; background: rgba(255,255,255,0.05); color: #fff; border: 1px solid rgba(255,255,255,0.2);">
        `,
        background: '#141826',
        color: '#fff',
        confirmButtonColor: '#3b82f6',
        confirmButtonText: 'Reset',
        showCancelButton: true,
        cancelButtonColor: '#374151',
        focusConfirm: false,
        preConfirm: () => {
            const password = Swal.getPopup().querySelector('#new_password').value;
            return { password };
        }
    }).then((result) => {
        if (!result.isConfirmed) return;
        Swal.fire({ title: 'Processing…', text: 'Securing account...', background: '#141826', color: '#fff', allowOutsideClick: false, didOpen: () => Swal.showLoading() });
        axios.post(url, {
            password: result.value.password
        })
        .then(res => {
            const data = res.data;
            Swal.fire({
                icon: 'success',
                title: 'Success!',
                html: `<div class="text-left text-white"><p class="mb-2">${data.message}</p><p class="mb-0"><strong>Temporary Password:</strong> <span class="text-warning">${data.temporary_password}</span></p></div>`,
                background: '#141826',
                color: '#fff'
            });
        })
        .catch(err => {
            console.error('Password Reset Failed:', err);
            const msg = err.response?.data?.message || err.message || 'An error occurred.';
            Swal.fire({ icon: 'error', title: 'Failed', text: msg, background: '#141826', color: '#fff' });
        });
    });
});

$(document).on('click', '.status-btn', function(e) {
    e.preventDefault();
    const id = $(this).data('id');
    const status = $(this).data('status');
    const name = $(this).data('name');
    const url = '{{ route("admin.users.status", ["id" => "__ID__"]) }}'.replace('__ID__', String(id));

    const actionLabel = status === 'active' ? 'Activate' : (status === 'suspended' ? 'Suspend' : 'Delete');
    const confirmColor = status === 'active' ? '#22c55e' : '#ef4444';

    Swal.fire({
        title: `${actionLabel} User`,
        text: `Are you sure you want to ${actionLabel.toLowerCase()} ${name}?`,
        icon: 'warning',
        background: '#141826',
        color: '#fff',
        showCancelButton: true,
        confirmButtonColor: confirmColor,
        confirmButtonText: `Yes, ${actionLabel}`,
        cancelButtonColor: '#374151'
    }).then((result) => {
        if (!result.isConfirmed) return;
        Swal.fire({ title: 'Processing…', text: 'Applying changes...', background: '#141826', color: '#fff', allowOutsideClick: false, didOpen: () => Swal.showLoading() });
        axios.post(url, {
            user_status: status
        })
        .then(res => {
            const data = res.data;
            Swal.fire({ icon: 'success', title: 'Done!', text: data.message, background: '#141826', color: '#fff' }).then(() => location.reload());
        })
        .catch(err => {
            console.error('Status Update Failed:', err);
            const msg = err.response?.data?.message || err.message || 'An error occurred.';
            Swal.fire({ icon: 'error', title: 'Failed', text: msg, background: '#141826', color: '#fff' });
        });
    });
});
</script>
@endpush
