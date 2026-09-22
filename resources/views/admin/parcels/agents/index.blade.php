@extends('layouts.admin')

@section('content')
<div class="container-fluid py-4">
    <h2 class="mb-4">Parcel Agents Management</h2>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th>Agent ID / Name</th>
                            <th>NIN Number</th>
                            <th>Shop Details</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($agents as $agent)
                            <tr>
                                <td>
                                    <strong>#{{ $agent->id }}</strong><br>
                                    {{ $agent->user->name ?? 'N/A' }}<br>
                                    <small class="text-muted">{{ $agent->user->email ?? '' }}</small>
                                </td>
                                <td>{{ $agent->nin_number }}</td>
                                <td>
                                    <strong>{{ $agent->shop->name ?? 'N/A' }}</strong><br>
                                    <small>{{ $agent->shop->address ?? '' }}, {{ $agent->shop->city ?? '' }}</small>
                                </td>
                                <td>
                                    @if($agent->status === 'approved')
                                        <span class="badge bg-success">Approved</span>
                                    @elseif($agent->status === 'pending')
                                        <span class="badge bg-warning text-dark">Pending</span>
                                    @else
                                        <span class="badge bg-danger">Suspended</span>
                                    @endif
                                </td>
                                <td>
                                    <form action="{{ route('admin.parcels.agents.status', $agent->id) }}" method="POST" class="d-flex align-items-center">
                                        @csrf
                                        @method('PUT')
                                        <select name="status" class="form-select form-select-sm me-2" style="width: 120px;">
                                            <option value="pending" {{ $agent->status == 'pending' ? 'selected' : '' }}>Pending</option>
                                            <option value="approved" {{ $agent->status == 'approved' ? 'selected' : '' }}>Approve</option>
                                            <option value="suspended" {{ $agent->status == 'suspended' ? 'selected' : '' }}>Suspend</option>
                                        </select>
                                        <button type="submit" class="btn btn-primary btn-sm">Update</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-4">No agents found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer bg-white">
            {{ $agents->links() }}
        </div>
    </div>
</div>
@endsection
