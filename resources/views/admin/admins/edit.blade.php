@extends('layouts.nexus')

@section('title', 'Edit Admin - Fuwa.NG Control')

@section('content')
<div class="row mb-4 animate__animated animate__fadeInDown">
    <div class="col-12 d-flex flex-column flex-md-row align-items-start align-items-md-center justify-content-between">
        <div>
            <h3 class="text-white mb-1 font-weight-bold"><i class="fa fa-user-pen text-primary mr-2"></i> Edit Admin</h3>
            <p class="text-white-50 mb-0">Modify permissions and details for {{ $admin->username }}</p>
        </div>
        <div class="mt-3 mt-md-0">
            <a href="{{ route('admin.admins.index') }}" class="btn btn-outline-secondary shadow-sm rounded-pill px-4 py-2 font-weight-bold d-flex align-items-center">
                <i class="fa fa-arrow-left mr-2"></i> Back to List
            </a>
        </div>
    </div>
</div>

<div class="row animate__animated animate__fadeInUp">
    <div class="col-lg-8 mb-4">
        <form action="{{ route('admin.admins.update', $admin->id) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="card glass-card border-0 rounded-lg p-4 mb-4" style="background: rgba(255,255,255,0.02); backdrop-filter: blur(10px); border: 1px solid rgba(255,255,255,0.05) !important;">
                <h5 class="text-white font-weight-bold mb-4">Account Information</h5>
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group mb-4">
                            <label class="text-white-50 small font-weight-bold text-uppercase tracking-wider">Username</label>
                            <input type="text" name="username" class="form-control bg-transparent text-white @error('username') is-invalid @enderror" value="{{ old('username', $admin->username) }}" required>
                            @error('username')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group mb-4">
                            <label class="text-white-50 small font-weight-bold text-uppercase tracking-wider">Email Address</label>
                            <input type="email" name="email" class="form-control bg-transparent text-white @error('email') is-invalid @enderror" value="{{ old('email', $admin->email) }}" required>
                            @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group mb-4">
                            <label class="text-white-50 small font-weight-bold text-uppercase tracking-wider">Password <span class="text-muted text-lowercase font-weight-normal">(leave blank to keep current)</span></label>
                            <input type="password" name="password" class="form-control bg-transparent text-white @error('password') is-invalid @enderror" placeholder="Min. 8 characters">
                            @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group mb-4">
                            <label class="text-white-50 small font-weight-bold text-uppercase tracking-wider">Confirm Password</label>
                            <input type="password" name="password_confirmation" class="form-control bg-transparent text-white">
                        </div>
                    </div>
                </div>
            </div>

            <div class="card glass-card border-0 rounded-lg p-4 mb-4" style="background: rgba(255,255,255,0.02); backdrop-filter: blur(10px); border: 1px solid rgba(255,255,255,0.05) !important;">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h5 class="text-white font-weight-bold mb-0">Role Assignment</h5>
                    
                    <div class="custom-control custom-switch">
                        <input type="checkbox" class="custom-control-input" name="is_super_admin" id="superAdminSwitch" value="1" {{ old('is_super_admin', $admin->is_super_admin) ? 'checked' : '' }}>
                        <label class="custom-control-label font-weight-bold text-danger" for="superAdminSwitch">Super Admin Role</label>
                    </div>
                </div>

                <div id="rolesContainer">
                    <p class="text-white-50 small mb-4">Select the roles to assign to this administrator.</p>

                    <div class="row">
                        @php
                            $assignedRoles = $admin->roles->pluck('name')->toArray();
                            $oldRoles = old('roles', $assignedRoles);
                        @endphp
                        @foreach($roles as $role)
                        <div class="col-md-6 mb-3">
                            <div class="custom-control custom-checkbox px-0 d-flex align-items-center mb-2" style="gap: 10px;">
                                <input type="checkbox" name="roles[]" value="{{ $role->name }}" id="role_{{ $role->id }}" {{ in_array($role->name, $oldRoles) ? 'checked' : '' }} class="mr-2" style="width: 18px; height: 18px;">
                                <label class="text-white mb-0 text-capitalize" for="role_{{ $role->id }}">{{ str_replace('-', ' ', $role->name) }}</label>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <button type="submit" class="btn btn-primary btn-block py-3 font-weight-bold rounded-lg shadow-sm">
                <i class="fa fa-save mr-2"></i> Update Admin Account
            </button>
        </form>
    </div>

    <div class="col-lg-4">
        <div class="card glass-card border-0 rounded-lg p-4" style="background: rgba(59, 130, 246, 0.05); border: 1px solid rgba(59, 130, 246, 0.1) !important;">
            <h5 class="text-white font-weight-bold mb-3"><i class="fa fa-info-circle text-primary mr-2"></i> Role Guide</h5>
            <ul class="list-unstyled text-white-50 small mb-0">
                <li class="mb-3">
                    <strong class="text-white d-block mb-1">Super Admin Role:</strong>
                    Toggling this on grants the user unrestricted access to all modules, including managing other admins.
                </li>
                <li class="mb-3">
                    <strong class="text-white d-block mb-1">Sub-Admin (Default):</strong>
                    If Super Admin is off, you must explicitly select which modules this user can access.
                </li>
                <li>
                    <strong class="text-white d-block mb-1">Security:</strong>
                    If you change the password, inform the user securely. They can change it themselves if they have access to settings (if implemented).
                </li>
            </ul>
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
    document.addEventListener('DOMContentLoaded', function() {
        const superAdminSwitch = document.getElementById('superAdminSwitch');
        const rolesContainer = document.getElementById('rolesContainer');
        const roleCheckboxes = document.querySelectorAll('input[name="roles[]"]');

        function toggleRoles() {
            if (superAdminSwitch.checked) {
                rolesContainer.style.opacity = '0.5';
                roleCheckboxes.forEach(cb => {
                    cb.disabled = true;
                });
            } else {
                rolesContainer.style.opacity = '1';
                roleCheckboxes.forEach(cb => {
                    cb.disabled = false;
                });
            }
        }

        superAdminSwitch.addEventListener('change', toggleRoles);
        toggleRoles(); // initial state
    });
</script>
@endpush
@endsection
