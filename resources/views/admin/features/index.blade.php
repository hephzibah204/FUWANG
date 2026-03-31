@extends('layouts.nexus')

@section('title', 'Feature Toggles | Admin ' . config('app.name'))

@section('content')
<div class="dashboard-wrapper fade-in">
    <div class="row align-items-center mb-4">
        <div class="col-md-8">
            <h1 class="h3 font-weight-bold mb-1">Feature Toggles</h1>
            <p class="text-muted">Turn website services on or off globally.</p>
        </div>
        <div class="col-md-4 text-md-right mt-3 mt-md-0">
            <button class="btn btn-primary" data-toggle="modal" data-target="#addFeatureModal">
                <i class="fa-solid fa-plus mr-2"></i> Add Feature Toggle
            </button>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="admin-panel mt-4">
        <div class="table-responsive">
            <table class="table admin-table mb-0">
                <thead>
                    <tr>
                        <th>Feature Key</th>
                        <th>Status</th>
                        <th>Offline Message</th>
                        <th class="text-right pr-4">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($features as $feature)
                        <tr>
                            <td class="align-middle font-weight-bold text-white"><code>{{ $feature->feature_name }}</code></td>
                            <td class="align-middle">
                                @if($feature->is_active)
                                    <span class="badge badge-success"><i class="fa-solid fa-circle-check mr-1"></i> Active</span>
                                @else
                                    <span class="badge badge-danger"><i class="fa-solid fa-circle-xmark mr-1"></i> Disabled</span>
                                @endif
                            </td>
                            <td class="align-middle text-muted small">{{ Str::limit($feature->offline_message ?? 'Default offline message', 40) }}</td>
                            <td class="align-middle text-right pr-4">
                                <button class="btn btn-sm btn-outline-primary" data-toggle="modal" data-target="#editFeatureModal{{ $feature->id }}">Edit</button>
                                <form action="{{ route('admin.features.destroy', $feature->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this toggle?');">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger"><i class="fa fa-trash"></i></button>
                                </form>
                            </td>
                        </tr>

                        <!-- Edit Feature Modal -->
                        <div class="modal fade" id="editFeatureModal{{ $feature->id }}" tabindex="-1" role="dialog" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered" role="document">
                                <div class="modal-content">
                                    <div class="modal-header border-bottom-0 pb-0">
                                        <h5 class="modal-title font-weight-bold text-white">Edit {{ $feature->feature_name }}</h5>
                                        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                    </div>
                                    <form action="{{ route('admin.features.update', $feature->id) }}" method="POST">
                                        @csrf
                                        @method('PUT')
                                        <div class="modal-body">
                                            <div class="form-group mb-4">
                                                <div class="custom-control custom-switch custom-switch-lg p-0 m-0">
                                                    <input type="checkbox" class="custom-control-input" id="isActive{{ $feature->id }}" name="is_active" value="1" {{ $feature->is_active ? 'checked' : '' }}>
                                                    <label class="custom-control-label font-weight-bold text-white pl-5" for="isActive{{ $feature->id }}" style="padding-top: 3px;">Feature is Active</label>
                                                    <small class="d-block text-muted mt-2">Uncheck to disable this feature globally.</small>
                                                </div>
                                            </div>
                                            <div class="form-group mb-3">
                                                <label class="font-weight-bold">Offline Message (Optional)</label>
                                                <input type="text" name="offline_message" class="form-control" value="{{ $feature->offline_message }}" placeholder="E.g. Data Purchasing is currently down.">
                                            </div>
                                        </div>
                                        <div class="modal-footer border-top-0 pt-0">
                                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                                            <button type="submit" class="btn btn-primary">Save Changes</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center py-5 text-muted">
                                <i class="fa-solid fa-toggle-off fa-3x mb-3"></i>
                                <h5>No feature toggles configured</h5>
                                <p class="mb-0">Add a feature key to start managing uptime status.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Add Feature Modal -->
<div class="modal fade" id="addFeatureModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header border-bottom-0 pb-0">
                <h5 class="modal-title font-weight-bold text-white">Add Feature Toggle</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="{{ route('admin.features.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="form-group mb-3">
                        <label class="font-weight-bold">Feature Key <span class="text-danger">*</span></label>
                        <input type="text" name="feature_name" class="form-control" placeholder="e.g. data_purchase, airtime, bvn" required>
                        <small class="text-muted mt-1">Use a distinct string name you will use in your code.</small>
                    </div>
                    <div class="form-group mb-4">
                        <div class="custom-control custom-switch custom-switch-lg p-0 m-0">
                            <input type="checkbox" class="custom-control-input" id="isNewActive" name="is_active" value="1" checked>
                            <label class="custom-control-label font-weight-bold text-white pl-5" for="isNewActive" style="padding-top: 3px;">Activate Immediately</label>
                        </div>
                    </div>
                    <div class="form-group mb-3">
                        <label class="font-weight-bold">Offline Message (Optional)</label>
                        <input type="text" name="offline_message" class="form-control" placeholder="Shown to users when disabled">
                    </div>
                </div>
                <div class="modal-footer border-top-0 pt-0">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add Feature</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('styles')
<style>
/* Custom Switch LG Override for Fuwa.NG */
.custom-switch-lg .custom-control-label::before {
    left: 0;
    width: 2.25rem;
    height: 1.25rem;
    border-radius: 1.25rem;
    background-color: rgba(255,255,255,0.1);
    border: none;
}
.custom-switch-lg .custom-control-label::after {
    top: calc(0.25rem + 2px);
    left: calc(0.25rem - 4px);
    width: calc(1.25rem - 4px);
    height: calc(1.25rem - 4px);
    border-radius: 50%;
    background-color: #fff;
    transition: transform 0.15s ease-in-out;
}
.custom-switch-lg .custom-control-input:checked ~ .custom-control-label::before {
    background-color: var(--clr-primary);
}
.custom-switch-lg .custom-control-input:checked ~ .custom-control-label::after {
    transform: translateX(1rem);
}
</style>
@endpush
@endsection
