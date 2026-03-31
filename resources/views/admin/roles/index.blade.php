@extends('layouts.nexus')

@section('title', 'Manage Roles - Fuwa.NG Control')

@section('content')
<div class="row mb-4 animate__animated animate__fadeInDown">
    <div class="col-12 d-flex flex-column flex-md-row align-items-start align-items-md-center justify-content-between">
        <div>
            <h3 class="text-white mb-1 font-weight-bold"><i class="fa fa-user-shield text-primary mr-2"></i> Roles & Permissions</h3>
            <p class="text-white-50 mb-0">Manage system roles and their access levels</p>
        </div>
        <div class="mt-3 mt-md-0">
            <a href="{{ route('admin.roles.create') }}" class="btn btn-primary shadow-sm rounded-pill px-4 py-2 font-weight-bold d-flex align-items-center">
                <i class="fa fa-plus mr-2"></i> Create Role
            </a>
        </div>
    </div>
</div>

<div class="row animate__animated animate__fadeInUp">
    <div class="col-12">
        <div class="card glass-card border-0 rounded-lg p-4" style="background: rgba(255,255,255,0.02); backdrop-filter: blur(10px); border: 1px solid rgba(255,255,255,0.05) !important;">
            <div class="table-responsive">
                <table class="table table-borderless table-hover text-white mb-0">
                    <thead class="text-white-50 small text-uppercase tracking-wider">
                        <tr>
                            <th class="font-weight-bold">Role Name</th>
                            <th class="font-weight-bold">Permissions Count</th>
                            <th class="font-weight-bold">Permissions Overview</th>
                            <th class="font-weight-bold text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($roles as $role)
                        <tr style="border-bottom: 1px solid rgba(255,255,255,0.05);">
                            <td class="align-middle">
                                <span class="font-weight-bold text-capitalize">{{ str_replace('-', ' ', $role->name) }}</span>
                            </td>
                            <td class="align-middle">
                                <span class="badge badge-primary rounded-pill">{{ $role->permissions->count() }}</span>
                            </td>
                            <td class="align-middle text-white-50 small">
                                @if($role->name === 'super-admin')
                                    <span class="text-success">All Permissions</span>
                                @else
                                    {{ Str::limit($role->permissions->pluck('name')->map(function($n){ return str_replace('_', ' ', $n); })->implode(', '), 50) }}
                                @endif
                            </td>
                            <td class="align-middle text-right">
                                <div class="btn-group">
                                    <a href="{{ route('admin.roles.edit', $role->id) }}" class="btn btn-sm btn-outline-info rounded-left" title="Edit">
                                        <i class="fa fa-edit"></i>
                                    </a>
                                    @if($role->name !== 'super-admin')
                                    <form action="{{ route('admin.roles.destroy', $role->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this role?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger rounded-right" title="Delete">
                                            <i class="fa fa-trash"></i>
                                        </button>
                                    </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="text-center py-4 text-white-50">
                                <div class="mb-2"><i class="fa fa-folder-open fa-2x text-muted"></i></div>
                                No roles found.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@push('styles')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
@endpush
@endsection
