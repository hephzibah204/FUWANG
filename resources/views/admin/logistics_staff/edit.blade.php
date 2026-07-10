@extends('layouts.nexus')

@section('title', 'Edit Logistics Staff - Fuwa.NG Control')

@section('content')
<div class="row mb-4 animate__animated animate__fadeInDown">
    <div class="col-12 d-flex flex-column flex-md-row align-items-start align-items-md-center justify-content-between">
        <div>
            <h3 class="text-white mb-1 font-weight-bold"><i class="fa fa-user-pen text-primary mr-2"></i> Edit Logistics Staff</h3>
            <p class="text-white-50 mb-0">{{ $staff->email }}</p>
        </div>
        <div class="mt-3 mt-md-0">
            <a href="{{ route('admin.logistics-staff.index') }}" class="btn btn-outline-secondary rounded-pill px-4 py-2">Back</a>
        </div>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success border-0" style="background: rgba(34,197,94,0.12); border: 1px solid rgba(34,197,94,0.25) !important; color: #d1fae5;">
        {{ session('success') }}
    </div>
@endif
@if ($errors->any())
    <div class="alert alert-danger border-0" style="background: rgba(220,53,69,0.12); border: 1px solid rgba(220,53,69,0.25) !important; color: #ffd0d7;">
        {{ $errors->first() }}
    </div>
@endif

<div class="card glass-card border-0 rounded-lg p-4 animate__animated animate__fadeInUp" style="background: rgba(255,255,255,0.02); backdrop-filter: blur(10px); border: 1px solid rgba(255,255,255,0.05) !important;">
    <form action="{{ route('admin.logistics-staff.update', $staff->id) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="form-row">
            <div class="form-group col-md-6">
                <label class="text-white-50 small">Full name</label>
                <input type="text" name="fullname" class="form-control" value="{{ old('fullname', $staff->fullname) }}">
            </div>
            <div class="form-group col-md-6">
                <label class="text-white-50 small">Email</label>
                <input type="email" name="email" class="form-control" value="{{ old('email', $staff->email) }}" required>
            </div>
        </div>

        <div class="form-row">
            <div class="form-group col-md-6">
                <label class="text-white-50 small">New password (optional)</label>
                <input type="password" name="password" class="form-control">
            </div>
            <div class="form-group col-md-6">
                <label class="text-white-50 small">Confirm new password</label>
                <input type="password" name="password_confirmation" class="form-control">
            </div>
        </div>

        @php $currentRole = $staff->roles->first()?->name; @endphp
        <div class="form-row">
            <div class="form-group col-md-6">
                <label class="text-white-50 small">Role</label>
                <select name="role" id="roleSelect" class="form-control" required>
                    <option value="logistics_manager" @selected(old('role', $currentRole) === 'logistics_manager')>Logistics Manager</option>
                    <option value="logistics_officer" @selected(old('role', $currentRole) === 'logistics_officer')>Logistics Officer</option>
                </select>
            </div>
            <div class="form-group col-md-6 d-flex align-items-end">
                <div class="custom-control custom-switch">
                    <input type="checkbox" class="custom-control-input" id="is_active" name="is_active" value="1" @checked(old('is_active', $staff->is_active))>
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
                @php $selected = old('permissions', $staff->permissions->pluck('name')->all()); @endphp
                @foreach($permissions as $perm)
                    <div class="col-md-6 col-lg-4 mb-2">
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input" id="perm_{{ md5($perm->name) }}" name="permissions[]" value="{{ $perm->name }}" @checked(in_array($perm->name, $selected))>
                            <label class="custom-control-label text-white-50 small" for="perm_{{ md5($perm->name) }}">{{ $perm->name }}</label>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="mt-4 d-flex flex-column flex-md-row justify-content-between">
            <button type="submit" class="btn btn-primary px-4">
                <i class="fa fa-save mr-2"></i> Save Changes
            </button>
            <form action="{{ route('admin.logistics-staff.destroy', $staff->id) }}" method="POST" class="mt-3 mt-md-0" onsubmit="return confirm('Delete this staff account?');">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-outline-danger px-4">
                    <i class="fa fa-trash-can mr-2"></i> Delete
                </button>
            </form>
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

