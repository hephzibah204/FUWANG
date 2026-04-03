@extends('layouts.nexus')

@section('title', 'User Directory - Admin')

@section('content')
<div class="row mb-4">
    <div class="col-12 d-flex justify-content-between align-items-center">
        <div class="d-flex align-items-center">
            <a href="{{ route('admin.dashboard') }}" class="btn btn-dark rounded-circle mr-3 d-flex align-items-center justify-content-center" style="width: 40px; height: 40px; background: rgba(255,255,255,0.05) !important; border: 1px solid rgba(255,255,255,0.1);">
                <i class="fa fa-arrow-left text-white"></i>
            </a>
            <div>
                <h3 class="text-white mb-0 fw-bold">User Directory</h3>
                <p class="text-white-50 mb-0">Manage system accounts and liquidity.</p>
            </div>
        </div>
        <form class="d-flex" method="GET" action="{{ route('admin.users.index') }}">
            <div class="input-group" style="width: 260px;">
                <input type="text" name="search" class="form-control bg-transparent text-white border-secondary" placeholder="Search name or email..." value="{{ request('search') }}" style="border-right: 0; background: rgba(255,255,255,0.04) !important;">
                <div class="input-group-append">
                    <button class="btn btn-outline-secondary" type="submit" style="border-left: 0; background: rgba(255,255,255,0.04);"><i class="fa fa-search text-white-50"></i></button>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="card glass-card border-0 rounded-4 overflow-hidden" style="background: rgba(255,255,255,0.02); backdrop-filter: blur(10px); border: 1px solid rgba(255,255,255,0.05) !important;">
    <div class="table-responsive">
        <table class="table table-borderless table-hover mb-0 text-white" style="background: transparent;">
            <thead style="background: rgba(255,255,255,0.05);">
                <tr>
                    <th class="py-3 px-4 text-white-50 text-uppercase tracking-wider small border-bottom-0">User Account</th>
                    <th class="py-3 px-4 text-white-50 text-uppercase tracking-wider small border-bottom-0">Status</th>
                    <th class="py-3 px-4 text-white-50 text-uppercase tracking-wider small border-bottom-0 text-right">Wallet Balance</th>
                    <th class="py-3 px-4 text-white-50 text-uppercase tracking-wider small border-bottom-0 text-center">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($users as $user)
                <tr style="border-bottom: 1px solid rgba(255,255,255,0.05);">
                    <td class="py-3 px-4 align-middle">
                        <a href="{{ route('admin.users.show', $user->id) }}" class="d-flex align-items-center text-decoration-none" aria-label="Open full profile for {{ $user->fullname }}">
                            <div class="rounded-circle d-flex align-items-center justify-content-center mr-3 font-weight-bold text-white shadow-sm" style="width: 40px; height: 40px; min-width: 40px; background: linear-gradient(135deg, rgba(99,102,241,0.3), rgba(79,70,229,0.1)); border: 1px solid rgba(99,102,241,0.2);">
                                {{ strtoupper(substr($user->fullname ?? 'U', 0, 1)) }}
                            </div>
                            <div>
                                <h6 class="mb-1 font-weight-bold text-white">{{ $user->fullname }}</h6>
                                <small class="text-white-50">{{ $user->email }}</small>
                            </div>
                        </a>
                    </td>
                    <td class="py-3 px-4 align-middle">
                        <div class="d-flex flex-column" style="gap:6px;">
                            @php($status = $user->user_status ?? 'active')
                            @if($status === 'active')
                                <span class="badge rounded-pill px-3 py-1" style="background: rgba(34,197,94,0.15); color: #22c55e; border: 1px solid rgba(34,197,94,0.3);">Active</span>
                            @elseif($status === 'suspended')
                                <span class="badge rounded-pill px-3 py-1" style="background: rgba(239,68,68,0.15); color: #ef4444; border: 1px solid rgba(239,68,68,0.3);">Suspended</span>
                            @else
                                <span class="badge rounded-pill px-3 py-1" style="background: rgba(255,255,255,0.06); color: rgba(255,255,255,0.65); border: 1px solid rgba(255,255,255,0.12);">Deleted</span>
                            @endif
                            @if($user->online_status === 'online')
                                <span class="badge rounded-pill px-3 py-1" style="background: rgba(34,197,94,0.12); color: rgba(34,197,94,0.95); border: 1px solid rgba(34,197,94,0.22);"><i class="fa fa-circle mr-1" style="font-size:7px;"></i> Online</span>
                            @else
                                <span class="badge rounded-pill px-3 py-1" style="background: rgba(255,255,255,0.05); color: #ccc; border: 1px solid rgba(255,255,255,0.1);">Offline</span>
                            @endif
                        </div>
                    </td>
                    <td class="py-3 px-4 align-middle text-right">
                        <h6 class="mb-0 font-weight-bold" style="color: #6ee7b7;">₦{{ number_format(optional($user->balance)->user_balance ?? 0, 2) }}</h6>
                    </td>
                    <td class="py-3 px-4 align-middle text-center">
                        <div class="d-flex justify-content-center gap-2" style="gap: 8px;">
                            <button type="button" class="btn btn-sm rounded-pill px-3 fund-btn" data-email="{{ $user->email }}" data-name="{{ $user->fullname }}" data-balance="{{ optional($user->balance)->user_balance ?? 0 }}" style="background: rgba(34,197,94,0.15); color: #22c55e; border: 1px solid rgba(34,197,94,0.3); font-size: 0.72rem; font-weight: 600;">
                                <i class="fa fa-plus mr-1"></i>Fund
                            </button>
                            <button type="button" class="btn btn-sm rounded-pill px-3 refund-btn" data-email="{{ $user->email }}" data-name="{{ $user->fullname }}" data-balance="{{ optional($user->balance)->user_balance ?? 0 }}" @if(empty($user->has_transactions)) disabled @endif style="background: rgba(245,158,11,0.15); color: #f59e0b; border: 1px solid rgba(245,158,11,0.3); font-size: 0.72rem; font-weight: 600; @if(empty($user->has_transactions)) opacity: 0.45; cursor: not-allowed; @endif">
                                <i class="fa fa-rotate-left mr-1"></i>Refund
                            </button>
                            <button type="button" class="btn btn-sm rounded-pill px-3 deduct-btn" data-email="{{ $user->email }}" data-name="{{ $user->fullname }}" data-balance="{{ optional($user->balance)->user_balance ?? 0 }}" style="background: rgba(239,68,68,0.15); color: #ef4444; border: 1px solid rgba(239,68,68,0.3); font-size: 0.72rem; font-weight: 600;">
                                <i class="fa fa-minus mr-1"></i>Deduct
                            </button>
                            <a href="{{ route('admin.users.show', $user->id) }}" class="btn btn-sm rounded-pill px-3" style="background: rgba(255,255,255,0.06); color: rgba(255,255,255,0.85); border: 1px solid rgba(255,255,255,0.12); font-size: 0.72rem; font-weight: 600;">
                                <i class="fa fa-user mr-1"></i>Profile
                            </a>
                            <a href="{{ route('admin.users.history', urlencode($user->email)) }}" class="btn btn-sm rounded-pill px-3" style="background: rgba(99,102,241,0.15); color: #a5b4fc; border: 1px solid rgba(99,102,241,0.3); font-size: 0.72rem; font-weight: 600;">
                                <i class="fa fa-clock-rotate-left mr-1"></i>History
                            </a>
                            <div class="dropdown">
                                <button class="btn btn-sm rounded-pill px-3 dropdown-toggle" type="button" id="moreMenu{{ $user->id }}" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" style="background: rgba(59,130,246,0.12); color: #93c5fd; border: 1px solid rgba(59,130,246,0.22); font-size: 0.72rem; font-weight: 600;">
                                    More
                                </button>
                                <div class="dropdown-menu dropdown-menu-right" aria-labelledby="moreMenu{{ $user->id }}" style="background:#0f172a; border:1px solid rgba(255,255,255,0.12);">
                                    <a class="dropdown-item text-white" href="{{ route('admin.direct_messages.create', ['audience_type' => 'emails', 'audience_value' => $user->email, 'title' => 'Message to ' . $user->fullname]) }}">
                                        <i class="fa fa-envelope mr-2"></i>Message
                                    </a>
                                    <button class="dropdown-item text-white reset-pass-btn" type="button" data-id="{{ $user->id }}" data-name="{{ $user->fullname }}" data-email="{{ $user->email }}">
                                        <i class="fa fa-key mr-2"></i>Reset Password
                                    </button>
                                    @if(($user->user_status ?? 'active') === 'suspended')
                                        <button class="dropdown-item text-white status-btn" type="button" data-id="{{ $user->id }}" data-status="active" data-name="{{ $user->fullname }}">
                                            <i class="fa fa-circle-check mr-2"></i>Activate
                                        </button>
                                    @else
                                        <button class="dropdown-item text-white status-btn" type="button" data-id="{{ $user->id }}" data-status="suspended" data-name="{{ $user->fullname }}">
                                            <i class="fa fa-ban mr-2"></i>Suspend
                                        </button>
                                    @endif
                                    <div class="dropdown-divider" style="border-color: rgba(255,255,255,0.08);"></div>
                                    <button class="dropdown-item status-btn" style="color:#ef4444;" type="button" data-id="{{ $user->id }}" data-status="deleted" data-name="{{ $user->fullname }}">
                                        <i class="fa fa-trash mr-2"></i>Delete
                                    </button>
                                </div>
                            </div>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" class="text-center py-5 text-white-50">No users found.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($users->hasPages())
    <div class="px-4 py-3 d-flex justify-content-center border-top" style="border-color: rgba(255,255,255,0.05) !important;">
        {{ $users->links('pagination::bootstrap-4') }}
    </div>
    <style>
        .pagination .page-link { background: rgba(255,255,255,0.05); border-color: rgba(255,255,255,0.1); color: #fff; }
        .pagination .page-item.active .page-link { background: var(--clr-primary); border-color: var(--clr-primary); }
        .pagination .page-item.disabled .page-link { background: rgba(255,255,255,0.02); color: #666; border-color: rgba(255,255,255,0.05); }
    </style>
    @endif
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

            // Use axios for better interceptor support & robustness
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

$(document).on('click', '.reset-pass-btn', function() {
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
        $.ajax({
            url: url,
            method: 'POST',
            timeout: 10000,
            data: { password: result.value.password, _token: '{{ csrf_token() }}' },
            success: function(res) {
                Swal.fire({
                    icon: 'success',
                    title: 'Success!',
                    html: `<div class="text-left"><p class="mb-2">${res.message}</p><p class="mb-0"><strong>Temporary Password:</strong> <span class="text-warning">${res.temporary_password}</span></p></div>`,
                    background: '#141826',
                    color: '#fff'
                });
            },
            error: function(xhr, status) {
                let errMsg = status === 'timeout' ? 'Connection timeout' : (xhr.responseJSON?.message || 'An error occurred.');
                Swal.fire({ icon: 'error', title: 'Failed', text: errMsg, background: '#141826', color: '#fff' });
            }
        });
    });
});

$(document).on('click', '.status-btn', function() {
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
        $.ajax({
            url: url,
            method: 'POST',
            timeout: 10000,
            data: { user_status: status, _token: '{{ csrf_token() }}' },
            success: function(res) {
                Swal.fire({ icon: 'success', title: 'Done!', text: res.message, background: '#141826', color: '#fff' }).then(() => location.reload());
            },
            error: function(xhr, status) {
                let errMsg = status === 'timeout' ? 'Connection timeout' : (xhr.responseJSON?.message || 'An error occurred.');
                Swal.fire({ icon: 'error', title: 'Failed', text: errMsg, background: '#141826', color: '#fff' });
            }
        });
    });
});

$(document).on('click', '.delete-user', function() {
    const id = $(this).data('id');
    const name = $(this).data('name') || 'this user';
    const url = '{{ route("admin.users.status", ["id" => "__ID__"]) }}'.replace('__ID__', String(id));

    Swal.fire({
        title: 'Delete User?',
        text: `Are you sure you want to delete ${name}? This will prevent login immediately.`,
        icon: 'error',
        background: '#141826',
        color: '#fff',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        confirmButtonText: 'Yes, Delete!',
        cancelButtonColor: '#374151'
    }).then((result) => {
        if (!result.isConfirmed) return;
        $.ajax({
            url: url,
            method: 'POST',
            timeout: 10000,
            data: { user_status: 'deleted', _token: '{{ csrf_token() }}' },
            success: function(res) {
                Swal.fire({ icon: 'success', title: 'Deleted', text: res.message, background: '#141826', color: '#fff' }).then(() => location.href = '{{ route("admin.users.index") }}');
            },
            error: function(xhr) {
                Swal.fire({ icon: 'error', title: 'Error', text: xhr.responseJSON?.message || 'Failed to delete user.', background: '#141826', color: '#fff' });
            }
        });
    });
});
</script>
@endpush
