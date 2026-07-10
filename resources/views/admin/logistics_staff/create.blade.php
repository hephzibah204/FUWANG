@extends('layouts.nexus')

@section('title', 'Create Logistics Staff - Fuwa.NG Control')

@section('content')
<div class="row mb-4 animate__animated animate__fadeInDown">
    <div class="col-12 d-flex flex-column flex-md-row align-items-start align-items-md-center justify-content-between">
        <div>
            <h3 class="text-white mb-1 font-weight-bold"><i class="fa fa-user-plus text-primary mr-2"></i> New Logistics Staff</h3>
            <p class="text-white-50 mb-0">Create a Logistics Manager or Logistics Officer account</p>
        </div>
        <div class="mt-3 mt-md-0">
            <a href="{{ route('admin.logistics-staff.index') }}" class="btn btn-outline-secondary rounded-pill px-4 py-2">Back</a>
        </div>
    </div>
</div>

@if ($errors->any())
    <div class="alert alert-danger border-0" style="background: rgba(220,53,69,0.12); border: 1px solid rgba(220,53,69,0.25) !important; color: #ffd0d7;">
        {{ $errors->first() }}
    </div>
@endif

<div class="card glass-card border-0 rounded-lg p-4 animate__animated animate__fadeInUp" style="background: rgba(255,255,255,0.02); backdrop-filter: blur(10px); border: 1px solid rgba(255,255,255,0.05) !important;">
    <form action="{{ route('admin.logistics-staff.store') }}" method="POST">
        @csrf

        <div class="form-row">
            <div class="form-group col-md-6">
                <label class="text-white-50 small">Full name</label>
                <input type="text" name="fullname" class="form-control" value="{{ old('fullname') }}" placeholder="e.g., Ada Okafor">
            </div>
            <div class="form-group col-md-6">
                <label class="text-white-50 small">Email</label>
                <input type="email" name="email" class="form-control" value="{{ old('email') }}" required placeholder="name@company.com">
            </div>
        </div>

        <div class="form-row">
            <div class="form-group col-md-6">
                <label class="text-white-50 small">Password</label>
                <input type="password" name="password" class="form-control" required>
            </div>
            <div class="form-group col-md-6">
                <label class="text-white-50 small">Confirm password</label>
                <input type="password" name="password_confirmation" class="form-control" required>
            </div>
        </div>

        <div class="form-row">
            <div class="form-group col-md-6">
                <label class="text-white-50 small">Role</label>
                <select name="role" id="roleSelect" class="form-control" required>
                    <option value="logistics_manager" @selected(old('role') === 'logistics_manager')>Logistics Manager</option>
                    <option value="logistics_officer" @selected(old('role') === 'logistics_officer')>Logistics Officer</option>
                </select>
            </div>
            <div class="form-group col-md-6 d-flex align-items-end">
                <div class="custom-control custom-switch">
                    <input type="checkbox" class="custom-control-input" id="is_active" name="is_active" value="1" @checked(old('is_active', true))>
                    <label class="custom-control-label text-white-50 small" for="is_active">Active account</label>
                </div>
            </div>
        </div>

        <div id="officerPerms" class="mt-3" style="display: none;">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <h5 class="text-white mb-0">Officer permissions</h5>
                <span class="text-white-50 small">Permissions apply only to Logistics Officers</span>
            </div>
            <div class="row">
                @foreach($permissions as $perm)
                    <div class="col-md-6 col-lg-4 mb-2">
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input" id="perm_{{ md5($perm->name) }}" name="permissions[]" value="{{ $perm->name }}" @checked(in_array($perm->name, old('permissions', [])))>
                            <label class="custom-control-label text-white-50 small" for="perm_{{ md5($perm->name) }}">{{ $perm->name }}</label>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="mt-4 d-flex flex-column flex-md-row justify-content-between">
            <button type="submit" class="btn btn-primary px-4">
                <i class="fa fa-save mr-2"></i> Create Staff
            </button>
            <a href="{{ route('admin.logistics-staff.index') }}" class="btn btn-outline-secondary mt-3 mt-md-0 px-4">Cancel</a>
        </div>
    </form>
</div>

@push('styles')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
@endpush

@push('scripts')
<script>
    (function () {
        function sync() {
            var role = document.getElementById('roleSelect');
            var panel = document.getElementById('officerPerms');
            if (!role || !panel) return;
            panel.style.display = role.value === 'logistics_officer' ? 'block' : 'none';
        }
        document.addEventListener('DOMContentLoaded', function () {
            var role = document.getElementById('roleSelect');
            if (role) {
                role.addEventListener('change', sync);
            }
            sync();
        });
    })();
</script>
@endpush
@endsection

