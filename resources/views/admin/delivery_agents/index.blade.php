@extends('layouts.admin')

@section('title', 'Manage Delivery Agents')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">All Delivery Agents</h3>
                </div>
                <div class="card-body">
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Name</th>
                                <th>Location</th>
                                <th>Status</th>
                                <th>Joined</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($agents as $agent)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $agent->user->fullname }}</td>
                                <td>{{ $agent->city }}, {{ $agent->state }}</td>
                                <td>
                                    <span class="badge badge-{{ $agent->approval_status == 'approved' ? 'success' : ($agent->approval_status == 'rejected' ? 'danger' : 'warning') }}">
                                        {{ ucfirst($agent->approval_status) }}
                                    </span>
                                </td>
                                <td>{{ $agent->created_at->format('d M, Y') }}</td>
                                <td>
                                    @if($agent->approval_status == 'pending')
                                    <form action="{{ route('admin.delivery-agents.update', $agent) }}" method="POST" class="d-inline-block">
                                        @csrf
                                        @method('PUT')
                                        <input type="hidden" name="approval_status" value="approved">
                                        <button type="submit" class="btn btn-sm btn-success">Approve</button>
                                    </form>
                                    <form action="{{ route('admin.delivery-agents.update', $agent) }}" method="POST" class="d-inline-block">
                                        @csrf
                                        @method('PUT')
                                        <input type="hidden" name="approval_status" value="rejected">
                                        <button type="submit" class="btn btn-sm btn-danger">Reject</button>
                                    </form>
                                    @else
                                    <span class="text-muted">No action required</span>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                    <div class="mt-3">
                        {{ $agents->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
