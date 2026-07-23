@extends('layouts.nexus')

@section('title', 'Sandbox Services Testing')

@section('content')
<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12">
            <h1 class="h3 font-weight-bold text-dark">Sandbox Services Testing</h1>
            <p class="text-muted">Test verification and third-party APIs in a safe sandbox environment.</p>
        </div>
    </div>

    <div class="row">
        @foreach($services as $key => $service)
            <div class="col-md-4 mb-4">
                <div class="card h-100 shadow-sm border-0">
                    <div class="card-body d-flex flex-column justify-content-between">
                        <div>
                            <h5 class="card-title text-primary font-weight-bold">{{ $service['title'] }}</h5>
                            <p class="card-text text-muted small">
                                Service Key: <code>{{ $key }}</code><br>
                                Custom API Type: <code>{{ $service['custom_api_service_type'] ?? 'N/A' }}</code>
                            </p>
                        </div>
                        <div class="mt-3">
                            <a href="{{ route('admin.sandbox.services.show', $key) }}" class="btn btn-primary btn-sm w-100">
                                Open Sandbox
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</div>
@endsection
