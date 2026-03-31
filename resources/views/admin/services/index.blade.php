@extends('layouts.nexus')

@section('title', 'Services | Admin ' . config('app.name'))

@section('content')
<div class="dashboard-wrapper fade-in">
    <div class="row align-items-center mb-4">
        <div class="col-md-8">
            <h1 class="h3 font-weight-bold mb-1">Services</h1>
            <p class="text-muted mb-0">Control service availability and integrations without mixing user flows.</p>
        </div>
        <div class="col-md-4 text-md-right mt-3 mt-md-0">
            <a class="btn btn-outline-primary mr-2" href="{{ route('admin.custom_apis.index') }}">
                <i class="fa-solid fa-code-branch mr-2"></i> Custom APIs
            </a>
            <a class="btn btn-primary" href="{{ route('admin.settings.index', ['tab' => 'tab-features']) }}">
                <i class="fa fa-toggle-on mr-2"></i> Service Toggles
            </a>
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
                        <th>Service</th>
                        <th>Feature Key</th>
                        <th>Status</th>
                        <th>Providers</th>
                        <th class="text-right pr-4">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($catalog as $item)
                        @php
                            $featureKey = $item['feature_key'];
                            $toggle = $featureKey ? ($featureToggles[$featureKey] ?? null) : null;
                            $isActive = $toggle ? (bool) $toggle->is_active : true;
                            $offlineMessage = $toggle?->offline_message;
                            $serviceType = $item['custom_api_service_type'];
                            $stats = $serviceType ? ($customApiStats[$serviceType] ?? null) : null;
                        @endphp
                        <tr>
                            <td class="align-middle">
                                <div class="font-weight-bold text-white">{{ $item['name'] }}</div>
                                <div class="text-muted small">{{ $item['group'] }}</div>
                            </td>
                            <td class="align-middle">
                                @if($featureKey)
                                    <code class="text-white">{{ $featureKey }}</code>
                                    @if($offlineMessage)
                                        <div class="text-muted small mt-1">{{ Str::limit($offlineMessage, 60) }}</div>
                                    @endif
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td class="align-middle">
                                @if(!$featureKey)
                                    <span class="badge badge-info">Always On</span>
                                @elseif($isActive)
                                    <span class="badge badge-success"><i class="fa-solid fa-circle-check mr-1"></i> Enabled</span>
                                @else
                                    <span class="badge badge-danger"><i class="fa-solid fa-circle-xmark mr-1"></i> Disabled</span>
                                @endif
                            </td>
                            <td class="align-middle">
                                @if($serviceType)
                                    <div class="text-white small">
                                        {{ (int) ($stats->active_count ?? 0) }} active / {{ (int) ($stats->total_count ?? 0) }} total
                                    </div>
                                    <a href="{{ route('admin.custom_apis.index', ['service_type' => $serviceType]) }}" class="small text-primary text-decoration-none">Manage</a>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td class="align-middle text-right pr-4">
                                @if($featureKey)
                                    <form action="{{ route('admin.services.toggles.set', $featureKey) }}" method="POST" class="d-inline">
                                        @csrf
                                        <input type="hidden" name="offline_message" value="{{ $offlineMessage }}">
                                        <input type="hidden" name="is_active" value="{{ $isActive ? 0 : 1 }}">
                                        <button type="submit" class="btn btn-sm {{ $isActive ? 'btn-outline-danger' : 'btn-outline-success' }}">
                                            {{ $isActive ? 'Disable' : 'Enable' }}
                                        </button>
                                    </form>
                                    <a href="{{ route('admin.settings.index', ['tab' => 'tab-features']) }}" class="btn btn-sm btn-outline-primary ml-2">Details</a>
                                @else
                                    <a href="{{ route('admin.settings.index') }}" class="btn btn-sm btn-outline-primary">Settings</a>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
