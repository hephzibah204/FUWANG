@extends('layouts.nexus')

@section('title', 'My Profile - Fuwa.NG')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8 fade-up stagger-1">
        <div class="d-flex align-items-center mb-4">
            <h3 class="text-white mb-0 fw-bold">My Profile</h3>
        </div>

        <div class="card glass-card border-0 rounded-4 overflow-hidden mb-4" style="background: rgba(255,255,255,0.03); backdrop-filter: blur(10px); border: 1px solid rgba(255,255,255,0.05) !important;">
            <div class="p-4 text-center" style="background: linear-gradient(135deg, rgba(25, 15, 146, 0.2), rgba(0,0,0,0));">
                <div class="avatar-wrapper mx-auto shadow-lg d-flex align-items-center justify-content-center mb-3" style="width: 100px; height: 100px; border-radius: 50%; background: linear-gradient(135deg, var(--clr-primary), var(--clr-primary-hover)); color: white; font-size: 2.5rem; font-weight: 800; border: 3px solid rgba(255,255,255,0.1);">
                    {{ strtoupper(substr($user->fullname ?? $user->username, 0, 1)) }}
                </div>
                <h4 class="mb-1 fw-bold text-white">{{ $user->fullname ?? $user->username }}</h4>
                <span class="badge px-3 py-2 rounded-pill" style="background: rgba(255,255,255,0.1); color: #ccc;">{{ ucfirst($user->role ?? 'User') }}</span>
            </div>
            <div class="card-body p-0">
                <div class="list-group list-group-flush">
                    <div class="list-group-item py-3 px-4 d-flex justify-content-between align-items-center" style="background: transparent; border-color: rgba(255,255,255,0.05);">
                        <span class="text-white-50"><i class="fa fa-user mr-2 text-primary"></i> Username</span>
                        <span class="font-weight-bold text-white">{{ $user->username }}</span>
                    </div>
                    <div class="list-group-item py-3 px-4 d-flex justify-content-between align-items-center" style="background: transparent; border-color: rgba(255,255,255,0.05);">
                        <span class="text-white-50"><i class="fa fa-envelope mr-2 text-primary"></i> Email Address</span>
                        <span class="font-weight-bold text-white">{{ $user->email }}</span>
                    </div>
                    <div class="list-group-item py-3 px-4 d-flex justify-content-between align-items-center" style="background: transparent; border-color: rgba(255,255,255,0.05);">
                        <span class="text-white-50"><i class="fa fa-phone mr-2 text-primary"></i> Phone Number</span>
                        <span class="font-weight-bold text-white">{{ $user->number ?? 'N/A' }}</span>
                    </div>
                    <div class="list-group-item py-3 px-4 d-flex justify-content-between align-items-center" style="background: transparent; border-color: rgba(255,255,255,0.05);">
                        <span class="text-white-50"><i class="fa fa-hashtag mr-2 text-primary"></i> Referral ID</span>
                        <span class="font-weight-bold text-primary">{{ $user->referral_id ?? 'N/A' }}</span>
                    </div>
                </div>
            </div>
        </div>

        <h5 class="mb-3 font-weight-bold text-white">Security & Settings</h5>
        <div class="list-group shadow-sm rounded-4 border-0 mb-4 overflow-hidden">
            <button type="button" onclick="changePassword()" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center py-3 px-4" style="background: rgba(255,255,255,0.03); border-color: rgba(255,255,255,0.05); color: #fff;">
                <span><i class="fa fa-lock mr-2 text-warning"></i> Change Password</span>
                <i class="fa fa-chevron-right text-white-50 small"></i>
            </button>
            <button type="button" onclick="changePin()" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center py-3 px-4" style="background: rgba(255,255,255,0.03); border-color: rgba(255,255,255,0.05); color: #fff;">
                <span><i class="fa fa-key mr-2 text-info"></i> Change Transaction PIN</span>
                <i class="fa fa-chevron-right text-white-50 small"></i>
            </button>
            
            <div class="list-group-item py-3 px-4" style="background: rgba(255,255,255,0.03); border-color: rgba(255,255,255,0.05); color: #fff;">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span><i class="fa fa-shield-halved mr-2 text-success"></i> Two-Factor Auth (2FA)</span>
                    <div class="custom-control custom-switch">
                        <input type="checkbox" class="custom-control-input" id="2faToggle" {{ $user->google2fa_secret ? 'checked' : '' }}>
                        <label class="custom-control-label" for="2faToggle"></label>
                    </div>
                </div>
                <p class="small text-white-50 mb-0">Use Google Authenticator or Authy to add an extra layer of security.</p>
            </div>
        </div>

        <form action="{{ route('logout') }}" method="POST">
            @csrf
            <button type="submit" class="btn btn-danger btn-lg w-100 font-weight-bold shadow-sm rounded-4" style="background: rgba(220, 53, 69, 0.1); color: #ff4d4d; border: 1px solid rgba(220, 53, 69, 0.2);">
                <i class="fa fa-power-off mr-2"></i> Logout Session
            </button>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
function changePassword() {
    Swal.fire({
        title: 'Change Password',
        html: `
            <input type="password" id="current_password" class="swal2-input form-control bg-dark text-white border-secondary mb-3" placeholder="Current Password">
            <input type="password" id="new_password" class="swal2-input form-control bg-dark text-white border-secondary mb-3" placeholder="New Password">
            <input type="password" id="new_password_confirmation" class="swal2-input form-control bg-dark text-white border-secondary" placeholder="Confirm New Password">
        `,
        background: '#1a1d29',
        color: '#fff',
        confirmButtonColor: 'var(--clr-primary)',
        confirmButtonText: 'Update Password',
        showCancelButton: true,
        cancelButtonColor: '#444',
        focusConfirm: false,
        preConfirm: () => {
            const current_password = Swal.getPopup().querySelector('#current_password').value;
            const new_password = Swal.getPopup().querySelector('#new_password').value;
            const new_password_confirmation = Swal.getPopup().querySelector('#new_password_confirmation').value;
            
            if (!current_password || !new_password || !new_password_confirmation) {
                Swal.showValidationMessage(`Please enter all fields`);
                return false;
            }
            if (new_password !== new_password_confirmation) {
                Swal.showValidationMessage(`Passwords do not match`);
                return false;
            }
            return { current_password, new_password, new_password_confirmation };
        }
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire({title: 'Updating...', background: '#1a1d29', color: '#fff', allowOutsideClick: false, didOpen: () => { Swal.showLoading(); }});
            $.ajax({
                url: '{{ route("profile.update") ?? "/profile/update" }}',
                method: 'POST',
                data: {
                    ...result.value,
                    _token: '{{ csrf_token() }}'
                },
                success: function(res) {
                    if(res.status) {
                        Swal.fire({icon: 'success', title: 'Success', text: res.message, background: '#1a1d29', color: '#fff'});
                    } else {
                        Swal.fire({icon: 'error', title: 'Error', text: res.message || 'Update failed', background: '#1a1d29', color: '#fff'});
                    }
                },
                error: function(err) {
                    Swal.fire({icon: 'error', title: 'Error', text: err.responseJSON?.message || 'Server error', background: '#1a1d29', color: '#fff'});
                }
            });
        }
    });
}

function changePin() {
    Swal.fire({
        title: 'Change Transaction PIN',
        html: `
            <input type="password" id="current_pin" class="swal2-input form-control bg-dark text-white border-secondary mb-3" maxlength="4" placeholder="Current PIN">
            <input type="password" id="new_pin" class="swal2-input form-control bg-dark text-white border-secondary mb-3" maxlength="4" placeholder="New PIN (4 Digits)">
        `,
        background: '#1a1d29',
        color: '#fff',
        confirmButtonColor: 'var(--clr-primary)',
        confirmButtonText: 'Update PIN',
        showCancelButton: true,
        cancelButtonColor: '#444',
        focusConfirm: false,
        preConfirm: () => {
            const current_pin = Swal.getPopup().querySelector('#current_pin').value;
            const new_pin = Swal.getPopup().querySelector('#new_pin').value;
            
            if (!current_pin || !new_pin) {
                Swal.showValidationMessage(`Please enter all fields`);
                return false;
            }
            return { current_pin, new_pin };
        }
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire({title: 'Updating...', background: '#1a1d29', color: '#fff', allowOutsideClick: false, didOpen: () => { Swal.showLoading(); }});
            $.ajax({
                url: '{{ route("profile.update") ?? "/profile/update" }}', 
                method: 'POST',
                data: {
                    ...result.value,
                    _token: '{{ csrf_token() }}'
                },
                success: function(res) {
                    if(res.status) {
                        Swal.fire({icon: 'success', title: 'Success', text: res.message || 'PIN updated', background: '#1a1d29', color: '#fff'});
                    } else {
                        Swal.fire({icon: 'error', title: 'Error', text: res.message || 'Update failed', background: '#1a1d29', color: '#fff'});
                    }
                },
                error: function(err) {
                    Swal.fire({icon: 'error', title: 'Error', text: err.responseJSON?.message || 'Server error', background: '#1a1d29', color: '#fff'});
                }
            });
        }
    });
}

// 2FA Interaction
const toggle2fa = document.getElementById('2faToggle');
if (toggle2fa) {
    toggle2fa.addEventListener('change', function() {
        if (this.checked) {
            // SHOW SET UP MODAL
            Swal.fire({
                title: 'Set up 2FA',
                html: `
                    <div class="text-center mb-4">
                        <div class="bg-white p-3 d-inline-block rounded-3 mb-3" style="width: 200px; height: 200px;">
                            {!! \PragmaRX\Google2FALaravel\Facade::getQRCodeInline(config('app.name'), $user->email, $google2fa_secret) !!}
                        </div>
                        <p class="small text-white-50 mb-3">Scan this code with Google Authenticator or Authy</p>
                        <div class="p-2 rounded bg-dark border border-secondary mb-3">
                            <code class="text-warning" style="font-size: 1.1rem; letter-spacing: 2px;">{{ $google2fa_secret }}</code>
                        </div>
                        <input type="text" id="otp_code" class="form-control bg-dark text-white border-secondary text-center" maxlength="6" placeholder="Enter 6-digit code" style="font-size: 1.25rem;">
                    </div>
                `,
                background: '#1a1d29',
                color: '#fff',
                confirmButtonColor: 'var(--clr-primary)',
                confirmButtonText: 'Verify & Enable',
                showCancelButton: true,
                cancelButtonColor: '#444',
                preConfirm: () => {
                    const otp = document.getElementById('otp_code').value;
                    if (!otp || otp.length !== 6) {
                        Swal.showValidationMessage('Enter a valid 6-digit code');
                        return false;
                    }
                    return otp;
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    $.post('{{ route("profile.2fa.enable") }}', {
                        secret: '{{ $google2fa_secret }}',
                        one_time_password: result.value,
                        _token: '{{ csrf_token() }}'
                    }, function(res) {
                        if (res.status) {
                            nexusToast(res.message, 'success');
                            setTimeout(() => location.reload(), 1500);
                        } else {
                            toggle2fa.checked = false;
                            Swal.fire('Error', res.message, 'error');
                        }
                    });
                } else {
                    this.checked = false;
                }
            });
        } else {
            // DISABLE 2FA
            Swal.fire({
                title: 'Disable 2FA?',
                text: 'Enter your password to confirm',
                input: 'password',
                inputAttributes: { autocapitalize: 'off', placeholder: 'Current Password' },
                background: '#1a1d29',
                color: '#fff',
                showCancelButton: true,
                confirmButtonText: 'Disable',
                confirmButtonColor: '#ef4444',
                preConfirm: (pass) => {
                    if (!pass) {
                        Swal.showValidationMessage('Password is required');
                    }
                    return pass;
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    $.post('{{ route("profile.2fa.disable") }}', {
                        current_password: result.value,
                        _token: '{{ csrf_token() }}'
                    }, function(res) {
                        if (res.status) {
                            nexusToast(res.message, 'success');
                            setTimeout(() => location.reload(), 1500);
                        } else {
                            toggle2fa.checked = true;
                            Swal.fire('Error', res.message, 'error');
                        }
                    });
                } else {
                    this.checked = true;
                }
            });
        }
    });
}
</script>
@endpush
