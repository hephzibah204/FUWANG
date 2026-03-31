@extends('layouts.nexus')

@section('title', 'Admin Profile - Fuwa.NG Control')

@section('content')
<div class="row mb-4 animate__animated animate__fadeInDown">
    <div class="col-12 d-flex flex-column flex-md-row align-items-start align-items-md-center justify-content-between">
        <div>
            <h3 class="text-white mb-1 font-weight-bold"><i class="fa fa-user-gear text-primary mr-2"></i> Profile Settings</h3>
            <p class="text-white-50 mb-0">Manage your administrative account</p>
        </div>
    </div>
</div>

<div class="row animate__animated animate__fadeInUp">
    <div class="col-lg-8">
        <!-- Profile Info Form -->
        <div class="card glass-card border-0 rounded-lg p-4 mb-4" style="background: rgba(255,255,255,0.02); backdrop-filter: blur(10px); border: 1px solid rgba(255,255,255,0.05) !important;">
            <h5 class="text-white font-weight-bold mb-4">Personal Information</h5>
            <form action="{{ route('admin.profile.update') }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <div class="d-flex align-items-center mb-4 pb-4" style="border-bottom: 1px solid rgba(255,255,255,0.05);">
                    <div class="position-relative mr-4">
                        @if($admin->avatar)
                            <img src="{{ Storage::url($admin->avatar) }}" alt="Avatar" class="rounded-circle shadow-sm" style="width: 80px; height: 80px; object-fit: cover; border: 2px solid rgba(255,255,255,0.1);">
                        @else
                            <div class="rounded-circle d-flex align-items-center justify-content-center shadow-sm text-white font-weight-bold" style="width: 80px; height: 80px; background: linear-gradient(135deg, var(--clr-primary), var(--clr-primary-hover)); border: 2px solid rgba(255,255,255,0.1); font-size: 1.5rem;">
                                {{ strtoupper(substr($admin->fullname ?? $admin->username, 0, 1)) }}
                            </div>
                        @endif
                    </div>
                    <div>
                        <label class="btn btn-sm btn-outline-primary mb-0 shadow-sm rounded-pill px-3">
                            <i class="fa fa-camera mr-1"></i> Change Photo
                            <input type="file" name="avatar" class="d-none" accept="image/*">
                        </label>
                        <div class="small text-white-50 mt-2">Recommended: 1:1 ratio, max 2MB (JPG, PNG)</div>
                        @error('avatar')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 form-group mb-4">
                        <label class="text-white-50 small font-weight-bold text-uppercase tracking-wider">Username</label>
                        <input type="text" class="form-control bg-transparent text-white" value="{{ $admin->username }}" readonly disabled style="opacity: 0.7;">
                    </div>
                    <div class="col-md-6 form-group mb-4">
                        <label class="text-white-50 small font-weight-bold text-uppercase tracking-wider">Full Name</label>
                        <input type="text" name="fullname" class="form-control bg-transparent text-white @error('fullname') is-invalid @enderror" value="{{ old('fullname', $admin->fullname) }}" placeholder="e.g. John Doe">
                        @error('fullname')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 form-group mb-4">
                        <label class="text-white-50 small font-weight-bold text-uppercase tracking-wider">Email Address</label>
                        <input type="email" name="email" class="form-control bg-transparent text-white @error('email') is-invalid @enderror" value="{{ old('email', $admin->email) }}" required>
                        @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6 form-group mb-4">
                        <label class="text-white-50 small font-weight-bold text-uppercase tracking-wider">Phone Number</label>
                        <input type="text" name="phone" class="form-control bg-transparent text-white @error('phone') is-invalid @enderror" value="{{ old('phone', $admin->phone) }}" placeholder="e.g. 08012345678">
                        @error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>

                <button type="submit" class="btn btn-primary rounded-pill px-4 shadow-sm font-weight-bold">
                    <i class="fa fa-save mr-2"></i> Save Changes
                </button>
            </form>
        </div>

        <!-- Security Form -->
        <div class="card glass-card border-0 rounded-lg p-4" style="background: rgba(255,255,255,0.02); backdrop-filter: blur(10px); border: 1px solid rgba(255,255,255,0.05) !important;">
            <h5 class="text-white font-weight-bold mb-4">Security Settings</h5>
            <form action="{{ route('admin.profile.password') }}" method="POST">
                @csrf
                @method('PUT')

                <div class="form-group mb-4">
                    <label class="text-white-50 small font-weight-bold text-uppercase tracking-wider">Current Password</label>
                    <input type="password" name="current_password" class="form-control bg-transparent text-white @error('current_password') is-invalid @enderror" required>
                    @error('current_password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="row">
                    <div class="col-md-6 form-group mb-4">
                        <label class="text-white-50 small font-weight-bold text-uppercase tracking-wider">New Password</label>
                        <input type="password" name="password" class="form-control bg-transparent text-white @error('password') is-invalid @enderror" required minlength="8">
                        @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6 form-group mb-4">
                        <label class="text-white-50 small font-weight-bold text-uppercase tracking-wider">Confirm New Password</label>
                        <input type="password" name="password_confirmation" class="form-control bg-transparent text-white" required minlength="8">
                    </div>
                </div>

                <button type="submit" class="btn btn-warning rounded-pill px-4 shadow-sm font-weight-bold text-dark">
                    <i class="fa fa-lock mr-2"></i> Update Password
                </button>
            </form>
        </div>
    </div>

    <div class="col-lg-4 mt-4 mt-lg-0">
        <div class="card glass-card border-0 rounded-lg p-4 h-100" style="background: rgba(255,255,255,0.02); backdrop-filter: blur(10px); border: 1px solid rgba(255,255,255,0.05) !important;">
            <h5 class="text-white font-weight-bold mb-4">Account Status</h5>
            
            <div class="d-flex align-items-center mb-4">
                <div class="rounded-circle d-flex align-items-center justify-content-center mr-3" style="width: 45px; height: 45px; background: rgba(59, 130, 246, 0.1); color: #3b82f6;">
                    <i class="fa fa-shield-halved"></i>
                </div>
                <div>
                    <span class="d-block text-white-50 small text-uppercase tracking-wider">Role</span>
                    <span class="font-weight-bold text-white">
                        @if($admin->is_super_admin)
                            Super Administrator
                        @else
                            {{ $admin->roles->pluck('name')->map(fn($r) => str_replace('-', ' ', ucfirst($r)))->implode(', ') ?: 'Sub Administrator' }}
                        @endif
                    </span>
                </div>
            </div>

            <div class="d-flex align-items-center mb-4">
                <div class="rounded-circle d-flex align-items-center justify-content-center mr-3" style="width: 45px; height: 45px; background: rgba(34, 197, 94, 0.1); color: #22c55e;">
                    <i class="fa fa-clock"></i>
                </div>
                <div>
                    <span class="d-block text-white-50 small text-uppercase tracking-wider">Member Since</span>
                    <span class="font-weight-bold text-white">{{ $admin->created_at->format('M d, Y') }}</span>
                </div>
            </div>

            <hr style="border-color: rgba(255,255,255,0.05);">

            <div class="mt-4">
                <h6 class="text-white font-weight-bold mb-3">Two-Factor Authentication</h6>
                @if($admin->two_factor_enabled)
                    <div class="alert alert-success d-flex align-items-center py-2" style="background: rgba(34, 197, 94, 0.1); border: 1px solid rgba(34, 197, 94, 0.2); color: #86efac;">
                        <i class="fa fa-check-circle mr-2"></i> Enabled
                    </div>
                @else
                    <div class="alert alert-warning d-flex align-items-center py-2" style="background: rgba(234, 179, 8, 0.1); border: 1px solid rgba(234, 179, 8, 0.2); color: #fde047;">
                        <i class="fa fa-exclamation-triangle mr-2"></i> Disabled
                    </div>
                @endif
                <a href="{{ route('admin.settings.security.2fa.index') }}" class="btn btn-outline-light btn-sm btn-block rounded-pill mt-2">Manage 2FA</a>
            </div>
        </div>
    </div>
</div>

@push('styles')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
<style>
    .form-control:focus { background: rgba(255,255,255,0.05); border-color: rgba(59, 130, 246, 0.5); color: #fff; }
</style>
@endpush

@push('scripts')
<script>
    // Preview selected image
    document.querySelector('input[name="avatar"]').addEventListener('change', function(e) {
        if (e.target.files && e.target.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                const img = document.querySelector('.position-relative img');
                if (img) {
                    img.src = e.target.result;
                } else {
                    const container = document.querySelector('.position-relative');
                    container.innerHTML = `<img src="${e.target.result}" alt="Avatar" class="rounded-circle shadow-sm" style="width: 80px; height: 80px; object-fit: cover; border: 2px solid rgba(255,255,255,0.1);">`;
                }
            }
            reader.readAsDataURL(e.target.files[0]);
        }
    });
</script>
@endpush
@endsection