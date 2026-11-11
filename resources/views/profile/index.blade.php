@extends('layouts.nexus')

@section('title', 'My Profile - Fuwa.NG')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8 fade-up stagger-1">
        <div class="d-flex align-items-center justify-content-between mb-4">
            <h3 class="mb-0 fw-bold">My Profile</h3>
            <span class="badge px-3 py-2 rounded-pill badge-primary">
                {{ ucfirst($user->role ?? 'User') }}
            </span>
        </div>

        @if(session('success'))
            <div class="alert alert-success rounded-4 mb-4">
                <i class="fa fa-check-circle mr-2"></i>{{ session('success') }}
            </div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger rounded-4 mb-4">
                <i class="fa fa-circle-exclamation mr-2"></i>{{ session('error') }}
            </div>
        @endif

        {{-- Editable Profile Card --}}
        <div class="card glass-card border-0 rounded-4 overflow-hidden mb-4 shadow-sm">
            <form id="profileForm" method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="remove_avatar" id="removeAvatarInput" value="0">

                <div class="p-4 text-center profile-header-banner" style="background: linear-gradient(135deg, rgba(59, 130, 246, 0.15), rgba(139, 92, 246, 0.05)); border-bottom: var(--border-glass);">
                    <div class="position-relative d-inline-block mx-auto mb-3">
                        <div id="avatarContainer" class="avatar-frame shadow-lg rounded-circle overflow-hidden d-flex align-items-center justify-content-center" style="width: 110px; height: 110px; border: 3px solid var(--clr-primary); background: linear-gradient(135deg, var(--clr-primary), var(--clr-primary-hover));">
                            @if($user->avatar_url)
                                <img src="{{ $user->avatar_url }}" alt="{{ $user->fullname ?? $user->username }}" id="avatarPreview" class="w-100 h-100 rounded-circle" style="object-fit: cover;">
                                <div id="avatarFallback" class="text-white font-weight-bold d-none" style="font-size: 2.5rem;">
                                    {{ strtoupper(substr($user->fullname ?: ($user->username ?: 'U'), 0, 1)) }}
                                </div>
                            @else
                                <img src="" alt="Avatar Preview" id="avatarPreview" class="w-100 h-100 rounded-circle d-none" style="object-fit: cover;">
                                <div id="avatarFallback" class="text-white font-weight-bold" style="font-size: 2.5rem;">
                                    {{ strtoupper(substr($user->fullname ?: ($user->username ?: 'U'), 0, 1)) }}
                                </div>
                            @endif
                        </div>

                        {{-- Quick Camera Overlay Trigger --}}
                        <label for="avatarInput" class="avatar-camera-btn shadow" title="Click to upload profile picture" style="position: absolute; bottom: 0; right: 0; width: 36px; height: 36px; border-radius: 50%; background: var(--clr-primary); color: #fff; display: flex; align-items: center; justify-content: center; cursor: pointer; border: 2px solid var(--clr-bg-card); transition: transform 0.2s;">
                            <i class="fa-solid fa-camera" style="font-size: 0.9rem;"></i>
                        </label>
                    </div>

                    {{-- Hidden File Input --}}
                    <input type="file" id="avatarInput" name="avatar" accept="image/png,image/jpeg,image/jpg,image/webp" class="d-none">

                    <div class="d-flex align-items-center justify-content-center gap-2 mb-2" style="gap: 10px;">
                        <button type="button" class="btn btn-xs btn-outline-light rounded-pill px-3 py-1 font-weight-bold" onclick="document.getElementById('avatarInput').click();">
                            <i class="fa-solid fa-cloud-arrow-up mr-1"></i> Upload Photo
                        </button>
                        <button type="button" id="removeAvatarBtn" class="btn btn-xs btn-outline-danger rounded-pill px-3 py-1 font-weight-bold {{ $user->avatar ? '' : 'd-none' }}" onclick="removeCustomAvatar();">
                            <i class="fa-solid fa-trash-can mr-1"></i> Remove
                        </button>
                    </div>
                    <small class="text-muted d-block" style="font-size: 0.75rem;">JPG, PNG, or WEBP up to 2MB</small>

                    <h4 class="mt-2 mb-1 fw-bold" id="displayFullname">{{ $user->fullname ?: $user->username }}</h4>
                    <p class="text-muted small mb-0">{{ $user->email }}</p>
                </div>

                <div class="card-body p-4">
                    <h5 class="fw-bold mb-3"><i class="fa-solid fa-user-pen mr-2 text-primary"></i>Personal Information</h5>

                    <div class="row">
                        <div class="col-md-6 form-group mb-3">
                            <label class="form-label text-muted small fw-bold mb-1">Full Name</label>
                            <input type="text" name="fullname" id="inputFullname" class="form-control" value="{{ old('fullname', $user->fullname) }}" placeholder="e.g. Chukwuebuka Adeleke" maxlength="100">
                        </div>

                        <div class="col-md-6 form-group mb-3">
                            <label class="form-label text-muted small fw-bold mb-1">Username</label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text bg-transparent border-right-0 text-muted">@</span>
                                </div>
                                <input type="text" name="username" class="form-control border-left-0" value="{{ old('username', $user->username) }}" placeholder="username" maxlength="60">
                            </div>
                        </div>

                        <div class="col-md-6 form-group mb-3">
                            <label class="form-label text-muted small fw-bold mb-1">Phone Number</label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text bg-transparent border-right-0 text-muted"><i class="fa-solid fa-phone"></i></span>
                                </div>
                                <input type="tel" name="number" class="form-control border-left-0" value="{{ old('number', $user->number) }}" placeholder="e.g. 08012345678" maxlength="25">
                            </div>
                        </div>

                        <div class="col-md-6 form-group mb-3">
                            <label class="form-label text-muted small fw-bold mb-1">
                                Email Address 
                                <span class="badge badge-success ml-2 py-0.5 px-2" style="font-size: 0.65rem;"><i class="fa-solid fa-check-circle mr-1"></i>Verified</span>
                            </label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text bg-transparent border-right-0 text-muted"><i class="fa-solid fa-envelope"></i></span>
                                </div>
                                <input type="email" class="form-control border-left-0" value="{{ $user->email }}" disabled readonly style="opacity: 0.75; cursor: not-allowed;">
                            </div>
                        </div>

                        <div class="col-12 form-group mb-4">
                            <label class="form-label text-muted small fw-bold mb-1">Referral Code & Link</label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text bg-transparent border-right-0 text-muted"><i class="fa-solid fa-hashtag"></i></span>
                                </div>
                                <input type="text" id="refCodeInput" class="form-control font-weight-bold border-left-0 border-right-0" value="{{ $user->referral_id ?? 'N/A' }}" readonly>
                                <div class="input-group-append">
                                    <button type="button" class="btn btn-outline-primary" onclick="copyReferralCode()" title="Copy referral code">
                                        <i class="fa-regular fa-copy mr-1"></i> Copy Code
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end">
                        <button type="submit" id="saveProfileBtn" class="btn btn-primary rounded-pill px-4 font-weight-bold shadow-sm">
                            <i class="fa-solid fa-floppy-disk mr-1"></i> Save Profile Changes
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <x-nexus.kyc-tier-callout :kyc="$kycSummary" />

        <h5 class="mb-3 font-weight-bold"><i class="fa-solid fa-shield-halved mr-2 text-primary"></i>Security & Credentials</h5>
        <div class="list-group shadow-sm rounded-4 border-0 mb-4 overflow-hidden">
            <button type="button" onclick="changePassword()" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center py-3 px-4" style="background: var(--clr-bg-card); border-color: var(--border-glass); color: inherit;">
                <span class="d-flex align-items-center"><i class="fa fa-lock mr-3 text-warning"></i> <span>Change Account Password</span></span>
                <i class="fa fa-chevron-right text-muted small"></i>
            </button>
            <button type="button" onclick="changePin()" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center py-3 px-4" style="background: var(--clr-bg-card); border-color: var(--border-glass); color: inherit;">
                <span class="d-flex align-items-center"><i class="fa fa-key mr-3 text-info"></i> <span>Change Transaction PIN</span></span>
                <i class="fa fa-chevron-right text-muted small"></i>
            </button>
            
            <div class="list-group-item py-3 px-4" style="background: var(--clr-bg-card); border-color: var(--border-glass); color: inherit;">
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <span class="d-flex align-items-center font-weight-bold"><i class="fa fa-shield-halved mr-3 text-success"></i> Two-Factor Auth (2FA)</span>
                    <div class="custom-control custom-switch">
                        <input type="checkbox" class="custom-control-input" id="2faToggle" {{ $user->google2fa_secret ? 'checked' : '' }}>
                        <label class="custom-control-label" for="2faToggle"></label>
                    </div>
                </div>
                <p class="small text-muted mb-0 ml-4">Use Google Authenticator or Authy to add an extra layer of biometric/time-based security.</p>
            </div>
        </div>

        <form action="{{ route('logout') }}" method="POST" class="mt-4 mb-5">
            @csrf
            <button type="submit" class="logout-btn-pill">
                <i class="fa-solid fa-arrow-right-from-bracket mr-2"></i> End Session & Log Out
            </button>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
// Avatar client-side preview
const avatarInput = document.getElementById('avatarInput');
const avatarPreview = document.getElementById('avatarPreview');
const avatarFallback = document.getElementById('avatarFallback');
const removeAvatarInput = document.getElementById('removeAvatarInput');
const removeAvatarBtn = document.getElementById('removeAvatarBtn');

if (avatarInput) {
    avatarInput.addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (!file) return;

        if (file.size > 2 * 1024 * 1024) {
            Swal.fire({
                icon: 'error',
                title: 'File Too Large',
                text: 'Profile picture must be under 2MB.',
                background: 'var(--clr-bg-card)',
                color: 'var(--clr-text-main)'
            });
            avatarInput.value = '';
            return;
        }

        const reader = new FileReader();
        reader.onload = function(evt) {
            avatarPreview.src = evt.target.result;
            avatarPreview.classList.remove('d-none');
            avatarFallback.classList.add('d-none');
            removeAvatarInput.value = '0';
            if (removeAvatarBtn) {
                removeAvatarBtn.classList.remove('d-none');
            }
        };
        reader.readAsDataURL(file);
    });
}

function removeCustomAvatar() {
    Swal.fire({
        title: 'Remove photo?',
        text: 'Your avatar will reset to default initials or Google avatar.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Yes, remove',
        confirmButtonColor: '#ef4444',
        cancelButtonColor: '#64748b',
        background: 'var(--clr-bg-card)',
        color: 'var(--clr-text-main)'
    }).then((result) => {
        if (result.isConfirmed) {
            removeAvatarInput.value = '1';
            avatarInput.value = '';
            avatarPreview.src = '';
            avatarPreview.classList.add('d-none');
            avatarFallback.classList.remove('d-none');
            if (removeAvatarBtn) {
                removeAvatarBtn.classList.add('d-none');
            }
        }
    });
}

function copyReferralCode() {
    const code = document.getElementById('refCodeInput').value;
    if (!code || code === 'N/A') return;
    navigator.clipboard.writeText(code).then(() => {
        if (typeof window.nexusToast === 'function') {
            window.nexusToast('Referral code copied to clipboard!', 'success');
        } else {
            Swal.fire({ icon: 'success', title: 'Copied!', text: code, timer: 1500, showConfirmButton: false });
        }
    });
}

// Profile AJAX submission with smooth feedback
const profileForm = document.getElementById('profileForm');
if (profileForm) {
    profileForm.addEventListener('submit', function(e) {
        e.preventDefault();
        const submitBtn = document.getElementById('saveProfileBtn');
        const origText = submitBtn.innerHTML;
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fa fa-spinner fa-spin mr-1"></i> Saving...';

        const formData = new FormData(profileForm);

        fetch(profileForm.action, {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(async (response) => {
            const data = await response.json();
            if (response.ok && data.status) {
                if (typeof window.nexusToast === 'function') {
                    window.nexusToast(data.message || 'Profile updated successfully!', 'success');
                } else {
                    Swal.fire({ icon: 'success', title: 'Success', text: data.message });
                }
                if (data.user?.fullname) {
                    document.getElementById('displayFullname').innerText = data.user.fullname;
                }
            } else {
                const msg = data.message || Object.values(data.errors || {}).flat().join('<br>') || 'Failed to update profile.';
                Swal.fire({
                    icon: 'error',
                    title: 'Update Failed',
                    html: msg,
                    background: 'var(--clr-bg-card)',
                    color: 'var(--clr-text-main)'
                });
            }
        })
        .catch((err) => {
            Swal.fire({
                icon: 'error',
                title: 'Network Error',
                text: 'Could not connect to server. Please try again.',
                background: 'var(--clr-bg-card)',
                color: 'var(--clr-text-main)'
            });
        })
        .finally(() => {
            submitBtn.disabled = false;
            submitBtn.innerHTML = origText;
        });
    });
}

function changePassword() {
    Swal.fire({
        title: 'Change Password',
        html: `
            <input type="password" id="current_password" class="swal2-input form-control mb-3" placeholder="Current Password">
            <input type="password" id="new_password" class="swal2-input form-control mb-3" placeholder="New Password">
            <input type="password" id="new_password_confirmation" class="swal2-input form-control" placeholder="Confirm New Password">
        `,
        background: 'var(--clr-bg-card)',
        color: 'var(--clr-text-main)',
        confirmButtonColor: 'var(--clr-primary)',
        confirmButtonText: 'Update Password',
        showCancelButton: true,
        cancelButtonColor: '#64748b',
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
            Swal.fire({title: 'Updating...', background: 'var(--clr-bg-card)', color: 'var(--clr-text-main)', allowOutsideClick: false, didOpen: () => { Swal.showLoading(); }});
            $.ajax({
                url: '{{ route("profile.update") }}',
                method: 'POST',
                data: {
                    ...result.value,
                    _token: '{{ csrf_token() }}'
                },
                success: function(res) {
                    if(res.status) {
                        Swal.fire({icon: 'success', title: 'Success', text: res.message, background: 'var(--clr-bg-card)', color: 'var(--clr-text-main)'});
                    } else {
                        Swal.fire({icon: 'error', title: 'Error', text: res.message || 'Update failed', background: 'var(--clr-bg-card)', color: 'var(--clr-text-main)'});
                    }
                },
                error: function(err) {
                    Swal.fire({icon: 'error', title: 'Error', text: err.responseJSON?.message || 'Server error', background: 'var(--clr-bg-card)', color: 'var(--clr-text-main)'});
                }
            });
        }
    });
}

function changePin() {
    Swal.fire({
        title: 'Change Transaction PIN',
        html: `
            <input type="password" id="current_pin" class="swal2-input form-control mb-3" maxlength="4" placeholder="Current PIN">
            <input type="password" id="new_pin" class="swal2-input form-control mb-3" maxlength="4" placeholder="New PIN (4 Digits)">
        `,
        background: 'var(--clr-bg-card)',
        color: 'var(--clr-text-main)',
        confirmButtonColor: 'var(--clr-primary)',
        confirmButtonText: 'Update PIN',
        showCancelButton: true,
        cancelButtonColor: '#64748b',
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
            Swal.fire({title: 'Updating...', background: 'var(--clr-bg-card)', color: 'var(--clr-text-main)', allowOutsideClick: false, didOpen: () => { Swal.showLoading(); }});
            $.ajax({
                url: '{{ route("profile.update") }}', 
                method: 'POST',
                data: {
                    ...result.value,
                    _token: '{{ csrf_token() }}'
                },
                success: function(res) {
                    if(res.status) {
                        Swal.fire({icon: 'success', title: 'Success', text: res.message || 'PIN updated', background: 'var(--clr-bg-card)', color: 'var(--clr-text-main)'});
                    } else {
                        Swal.fire({icon: 'error', title: 'Error', text: res.message || 'Update failed', background: 'var(--clr-bg-card)', color: 'var(--clr-text-main)'});
                    }
                },
                error: function(err) {
                    Swal.fire({icon: 'error', title: 'Error', text: err.responseJSON?.message || 'Server error', background: 'var(--clr-bg-card)', color: 'var(--clr-text-main)'});
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
                            <img src="{{ $google2fa_qr_url }}" width="200" height="200" alt="2FA setup QR code" style="display:block;margin:0 auto;">
                        </div>
                        <p class="small text-muted mb-3">Scan this code with Google Authenticator or Authy</p>
                        <div class="p-2 rounded bg-dark border border-secondary mb-3">
                            <code class="text-warning" style="font-size: 1.1rem; letter-spacing: 2px;">{{ $google2fa_secret }}</code>
                        </div>
                        <input type="text" id="otp_code" class="form-control text-center font-weight-bold" maxlength="6" placeholder="Enter 6-digit code" style="font-size: 1.25rem;">
                    </div>
                `,
                background: 'var(--clr-bg-card)',
                color: 'var(--clr-text-main)',
                confirmButtonColor: 'var(--clr-primary)',
                confirmButtonText: 'Verify & Enable',
                showCancelButton: true,
                cancelButtonColor: '#64748b',
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
                        secret: @json($google2fa_secret),
                        one_time_password: result.value,
                        _token: '{{ csrf_token() }}'
                    }, function(res) {
                        if (res.status) {
                            if (typeof window.nexusToast === 'function') {
                                window.nexusToast(res.message, 'success');
                            }
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
                background: 'var(--clr-bg-card)',
                color: 'var(--clr-text-main)',
                showCancelButton: true,
                confirmButtonText: 'Disable',
                confirmButtonColor: '#ef4444',
                cancelButtonColor: '#64748b',
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
                            if (typeof window.nexusToast === 'function') {
                                window.nexusToast(res.message, 'success');
                            }
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
