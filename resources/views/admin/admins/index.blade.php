@extends('layouts.nexus')

@section('title', 'Admin Management - Fuwa.NG Control')

@section('content')
<div class="row mb-4 animate__animated animate__fadeInDown">
    <div class="col-12 d-flex flex-column flex-md-row align-items-start align-items-md-center justify-content-between">
        <div>
            <h3 class="text-white mb-1 font-weight-bold"><i class="fa fa-users-gear text-primary mr-2"></i> Admin Management</h3>
            <p class="text-white-50 mb-0">Manage system administrators and their permissions</p>
        </div>
        <div class="mt-3 mt-md-0">
            <a href="{{ route('admin.admins.create') }}" class="btn btn-primary shadow-sm rounded-pill px-4 py-2 font-weight-bold d-flex align-items-center">
                <i class="fa fa-plus-circle mr-2"></i> New Admin
            </a>
        </div>
    </div>
</div>

<div class="card glass-card border-0 rounded-lg p-0 mb-4 animate__animated animate__fadeInUp" style="background: rgba(255,255,255,0.02); backdrop-filter: blur(10px); border: 1px solid rgba(255,255,255,0.05) !important;">
    <div class="p-4 border-bottom border-secondary" style="border-color: rgba(255,255,255,0.05) !important;">
        <form action="{{ route('admin.admins.index') }}" method="GET" class="d-flex align-items-center">
            <div class="input-group" style="max-width: 400px;">
                <div class="input-group-prepend">
                    <span class="input-group-text bg-transparent border-0 text-white-50"><i class="fa fa-search"></i></span>
                </div>
                <input type="text" name="search" class="form-control form-control-sm bg-transparent border-secondary text-white shadow-none" placeholder="Search admins by name or email..." value="{{ request('search') }}" style="border-radius: 20px; border-color: rgba(255,255,255,0.1) !important;">
            </div>
            @if(request('search'))
                <a href="{{ route('admin.admins.index') }}" class="btn btn-sm btn-outline-secondary ml-3 rounded-pill px-3">Clear</a>
            @endif
        </form>
    </div>

    <div class="table-responsive">
        <table class="table admin-table mb-0 text-white">
            <thead style="background: rgba(255,255,255,0.05);">
                <tr>
                    <th class="border-top-0 text-white-50 font-weight-normal py-3 px-4">Admin</th>
                    <th class="border-top-0 text-white-50 font-weight-normal py-3">Role</th>
                    <th class="border-top-0 text-white-50 font-weight-normal py-3 text-right px-4">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($admins as $admin)
                <tr style="border-bottom: 1px solid rgba(255,255,255,0.05);">
                    <td class="py-3 px-4 align-middle">
                        <div class="d-flex align-items-center">
                            <div class="rounded-circle d-flex align-items-center justify-content-center mr-3 font-weight-bold text-white shadow-sm" style="width: 40px; height: 40px; background: linear-gradient(135deg, var(--clr-primary), var(--clr-primary-hover)); border: 2px solid rgba(255,255,255,0.1);">
                                {{ strtoupper(substr($admin->username, 0, 1)) }}
                            </div>
                            <div>
                                <h6 class="mb-0 font-weight-bold text-white">{{ $admin->username }}</h6>
                                <small class="text-white-50">{{ $admin->email }}</small>
                            </div>
                        </div>
                    </td>
                    <td class="py-3 align-middle">
                        @if($admin->is_super_admin)
                            <span class="badge badge-pill badge-danger shadow-sm px-3 py-1 font-weight-normal">Super Admin</span>
                        @else
                            <span class="badge badge-pill badge-primary shadow-sm px-3 py-1 font-weight-normal">Admin</span>
                        @endif
                    </td>
                    <td class="py-3 px-4 align-middle text-right">
                        <div class="dropdown">
                            <button class="btn btn-sm btn-dark border-0 rounded-circle" type="button" data-toggle="dropdown" style="width: 35px; height: 35px; background: rgba(255,255,255,0.05) !important;">
                                <i class="fa fa-ellipsis-v text-white-50"></i>
                            </button>
                            <div class="dropdown-menu dropdown-menu-right shadow-lg border-0" style="background: rgba(25, 30, 45, 0.95); backdrop-filter: blur(15px); border: 1px solid rgba(255,255,255,0.1) !important; border-radius: 12px;">
                                <a class="dropdown-item text-white py-2" href="{{ route('admin.admins.edit', $admin->id) }}">
                                    <i class="fa fa-edit text-primary mr-2" style="width: 20px;"></i> Edit
                                </a>
                                @if(auth()->guard('admin')->id() !== $admin->id)
                                <div class="dropdown-divider border-secondary" style="opacity: 0.3;"></div>
                                <form action="{{ route('admin.admins.destroy', $admin->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this admin?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="dropdown-item text-danger py-2">
                                        <i class="fa fa-trash-can mr-2" style="width: 20px;"></i> Delete
                                    </button>
                                </form>
                                @endif
                            </div>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="3" class="text-center py-5 text-white-50">
                        <div class="mb-3"><i class="fa fa-user-slash fa-3x text-white-50" style="opacity: 0.5;"></i></div>
                        <h5>No admins found</h5>
                        <p class="mb-0">Try adjusting your search or create a new admin.</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    
    @if($admins->hasPages())
    <div class="p-4 border-top border-secondary d-flex justify-content-center" style="border-color: rgba(255,255,255,0.05) !important;">
        {{ $admins->links('pagination::bootstrap-4') }}
    </div>
    @endif
</div>

@push('styles')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
<style>
    .admin-table th { font-size: 0.85rem; letter-spacing: 0.5px; text-transform: uppercase; }
    .dropdown-item:hover { background: rgba(255,255,255,0.1) !important; color: #fff !important; }
</style>
@endpush
@endsection
