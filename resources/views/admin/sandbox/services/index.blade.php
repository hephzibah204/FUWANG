@extends('layouts.nexus')

@section('title', 'Sandbox Services | Admin ' . config('app.name'))

@section('content')
<div class="dashboard-wrapper fade-in">
    <div class="row align-items-center mb-4">
        <div class="col-md-8">
            <h1 class="h3 font-weight-bold mb-1">Sandbox Services</h1>
            <p class="text-muted mb-0">Admin-only entrypoints for testing. No user wallet deductions.</p>
        </div>
        <div class="col-md-4 text-md-right mt-3 mt-md-0">
            <a class="btn btn-outline-primary mr-2" href="{{ route('admin.services.index') }}">
                <i class="fa-solid fa-layer-group mr-2"></i> Services
            </a>
            <a class="btn btn-primary" href="{{ route('admin.settings.index') }}">
                <i class="fa-solid fa-sliders mr-2"></i> Settings
            </a>
        </div>
    </div>

    <div class="row">
        @foreach($services as $key => $svc)
            <div class="col-lg-4 col-md-6 col-12 mb-4">
                <a href="{{ route('admin.sandbox.services.show', $key) }}" class="text-decoration-none d-block">
                    <div class="p-4 rounded-lg" style="background: rgba(255,255,255,0.02); border: 1px solid rgba(255,255,255,0.07);">
                        <div class="text-white font-weight-bold">{{ $svc['title'] }}</div>
                        <div class="text-white-50 small mt-1">
                            Key: <code class="text-white">{{ $key }}</code>
                        </div>
                    </div>
                </a>
            </div>
        @endforeach
    </div>
</div>
@endsection

