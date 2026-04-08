@extends('layouts.admin')

@section('title', 'Manage Shipping Providers')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Add New Provider</h3>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.shipping-providers.store') }}" method="POST">
                        @csrf
                        <div class="form-group">
                            <label for="name">Provider Name</label>
                            <input type="text" name="name" id="name" class="form-control" placeholder="e.g. GIG Logistics, DHL" required>
                        </div>
                        <button type="submit" class="btn btn-primary btn-block">Add Provider</button>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Integrated Providers</h3>
                </div>
                <div class="card-body p-0">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>API Status</th>
                                <th>Active</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($providers as $provider)
                            <tr>
                                <td>{{ $provider->name }}</td>
                                <td>
                                    @if($provider->api_key)
                                        <span class="badge badge-success">Configured</span>
                                    @else
                                        <span class="badge badge-warning">Pending Config</span>
                                    @endif
                                </td>
                                <td>
                                    <form action="{{ route('admin.shipping-providers.toggle', $provider) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-{{ $provider->is_active ? 'success' : 'secondary' }}">
                                            {{ $provider->is_active ? 'Enabled' : 'Disabled' }}
                                        </button>
                                    </form>
                                </td>
                                <td>
                                    <button class="btn btn-sm btn-info" data-toggle="modal" data-target="#editModal{{ $provider->id }}">
                                        Edit Config
                                    </button>
                                </td>
                            </tr>

                            <!-- Edit Modal -->
                            <div class="modal fade" id="editModal{{ $provider->id }}" tabindex="-1" role="dialog">
                                <div class="modal-dialog" role="document">
                                    <div class="modal-content">
                                        <form action="{{ route('admin.shipping-providers.update', $provider) }}" method="POST">
                                            @csrf
                                            @method('PUT')
                                            <div class="modal-header">
                                                <h5 class="modal-title">Configure {{ $provider->name }}</h5>
                                                <button type="button" class="close" data-dismiss="modal">&times;</button>
                                            </div>
                                            <div class="modal-body">
                                                <div class="form-group">
                                                    <label>Display Name</label>
                                                    <input type="text" name="name" class="form-control" value="{{ $provider->name }}" required>
                                                </div>
                                                <div class="form-group">
                                                    <label>API Key</label>
                                                    <input type="text" name="api_key" class="form-control" value="{{ $provider->api_key }}">
                                                </div>
                                                <div class="form-group">
                                                    <label>API Secret</label>
                                                    <input type="text" name="api_secret" class="form-control" value="{{ $provider->api_secret }}">
                                                </div>
                                                <div class="form-group">
                                                    <label>API Base URL</label>
                                                    <input type="url" name="api_base_url" class="form-control" value="{{ $provider->api_base_url }}">
                                                </div>
                                                <div class="form-group">
                                                    <label>Status</label>
                                                    <select name="is_active" class="form-control">
                                                        <option value="1" {{ $provider->is_active ? 'selected' : '' }}>Enabled</option>
                                                        <option value="0" {{ !$provider->is_active ? 'selected' : '' }}>Disabled</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                                                <button type="submit" class="btn btn-primary">Save Changes</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
