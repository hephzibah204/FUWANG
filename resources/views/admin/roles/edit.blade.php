@extends('layouts.nexus')

@section('title', 'Edit Role - Fuwa.NG Control')

@section('content')
<div class="row mb-4 animate__animated animate__fadeInDown">
    <div class="col-12 d-flex flex-column flex-md-row align-items-start align-items-md-center justify-content-between">
        <div>
            <h3 class="text-white mb-1 font-weight-bold"><i class="fa fa-shield-alt text-primary mr-2"></i> Edit Role: <span class="text-capitalize">{{ str_replace('-', ' ', $role->name) }}</span></h3>
            <p class="text-white-50 mb-0">Update role details and permissions</p>
        </div>
        <div class="mt-3 mt-md-0">
            <a href="{{ route('admin.roles.index') }}" class="btn btn-outline-secondary shadow-sm rounded-pill px-4 py-2 font-weight-bold d-flex align-items-center">
                <i class="fa fa-arrow-left mr-2"></i> Back to Roles
            </a>
        </div>
    </div>
</div>

<div class="row animate__animated animate__fadeInUp">
    <div class="col-lg-8 mx-auto">
        <form action="{{ route('admin.roles.update', $role->id) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="card glass-card border-0 rounded-lg p-4 mb-4" style="background: rgba(255,255,255,0.02); backdrop-filter: blur(10px); border: 1px solid rgba(255,255,255,0.05) !important;">
                <div class="form-group mb-4">
                    <label class="text-white-50 small font-weight-bold text-uppercase tracking-wider">Role Name</label>
                    <input type="text" name="name" class="form-control bg-transparent text-white @error('name') is-invalid @enderror" value="{{ old('name', $role->name) }}" required {{ $role->name === 'super-admin' ? 'readonly' : '' }}>
                    @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <h5 class="text-white font-weight-bold mb-3 mt-4">Permission Matrix</h5>
                <p class="text-white-50 small mb-4">Select the specific actions this role can perform.</p>

                <div class="row">
                    @php
                        $rolePermissions = $role->permissions->pluck('name')->toArray();
                        $oldPerms = old('permissions', $rolePermissions);
                    @endphp
                    @foreach($permissions as $permission)
                    <div class="col-md-6 mb-3">
                        <div class="custom-control custom-checkbox px-0 d-flex align-items-center mb-2" style="gap: 10px;">
                            <input type="checkbox" name="permissions[]" value="{{ $permission->name }}" id="perm_{{ $permission->id }}" {{ in_array($permission->name, $oldPerms) ? 'checked' : '' }} class="mr-2" style="width: 18px; height: 18px;" {{ $role->name === 'super-admin' ? 'disabled checked' : '' }}>
                            <label class="text-white mb-0 text-capitalize" for="perm_{{ $permission->id }}">{{ str_replace('_', ' ', $permission->name) }}</label>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>

            <button type="submit" class="btn btn-primary btn-block py-3 font-weight-bold rounded-lg shadow-sm">
                <i class="fa fa-save mr-2"></i> Update Role
            </button>
        </form>
    </div>
</div>

@push('styles')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
<style>
    .form-control:focus { background: rgba(255,255,255,0.05); border-color: rgba(59, 130, 246, 0.5); color: #fff; }
</style>
@endpush
@endsection
