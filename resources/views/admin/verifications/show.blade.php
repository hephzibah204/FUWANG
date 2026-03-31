@extends('layouts.nexus')

@section('title', 'Verification Details | Admin ' . config('app.name'))

@section('content')
<div class="dashboard-wrapper fade-in">
    <div class="row align-items-center mb-4">
        <div class="col-md-8">
            <h1 class="h3 font-weight-bold mb-1">Verification Details</h1>
            <p class="text-muted mb-0">Reference: <code class="text-primary">{{ $result->reference_id }}</code></p>
        </div>
        <div class="col-md-4 text-md-right mt-3 mt-md-0">
            <a href="{{ route('admin.verifications.index') }}" class="btn btn-outline-primary mr-2">Back</a>
            <a href="{{ route('admin.verifications.report', $result->id) }}" class="btn btn-success"><i class="fa fa-file-pdf mr-2"></i>Download PDF</a>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-5 col-12 mb-4">
            <div class="admin-panel p-4" style="background: rgba(255,255,255,0.02); border: 1px solid rgba(255,255,255,0.07); border-radius: 18px;">
                <div class="d-flex justify-content-between mb-3">
                    <span class="text-white-50">User</span>
                    <span class="text-white">{{ $result->user?->fullname ?? '—' }} ({{ $result->user?->email ?? '—' }})</span>
                </div>
                <div class="d-flex justify-content-between mb-3">
                    <span class="text-white-50">Service</span>
                    <span class="text-white">{{ strtoupper($result->service_type) }}</span>
                </div>
                <div class="d-flex justify-content-between mb-3">
                    <span class="text-white-50">Identifier</span>
                    <span class="text-white">{{ $result->identifier }}</span>
                </div>
                <div class="d-flex justify-content-between mb-3">
                    <span class="text-white-50">Provider</span>
                    <span class="text-white">{{ $result->provider_name }}</span>
                </div>
                <div class="d-flex justify-content-between mb-3">
                    <span class="text-white-50">Status</span>
                    <span class="badge badge-pill {{ $result->status === 'success' ? 'badge-success' : ($result->status === 'pending' ? 'badge-warning' : 'badge-danger') }}">
                        {{ $result->status }}
                    </span>
                </div>
                <div class="d-flex justify-content-between">
                    <span class="text-white-50">Date</span>
                    <span class="text-white">{{ $result->created_at->format('M d, Y H:i') }}</span>
                </div>
            </div>
        </div>

        <div class="col-lg-7 col-12">
            <div class="admin-panel p-4" style="background: rgba(255,255,255,0.02); border: 1px solid rgba(255,255,255,0.07); border-radius: 18px;">
                <h5 class="text-white font-weight-bold mb-3">Raw Response</h5>
                <pre class="mb-0" style="white-space: pre-wrap; word-break: break-word; color: #e5e7eb;">{{ json_encode($result->response_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre>
            </div>
        </div>
    </div>
</div>
@endsection

