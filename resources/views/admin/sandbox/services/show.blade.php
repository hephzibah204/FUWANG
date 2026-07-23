@extends('layouts.nexus')

@section('title', $serviceConfig['title'])

@section('content')
<div class="container-fluid py-4">
    <div class="row mb-3">
        <div class="col-12">
            <a href="{{ route('admin.sandbox.services.index') }}" class="btn btn-outline-secondary btn-sm mb-3">&larr; Back to Catalog</a>
            <h1 class="h3 font-weight-bold text-dark">{{ $serviceConfig['title'] }}</h1>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-6 mb-4">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white font-weight-bold">
                    Execute Request
                </div>
                <div class="card-body">
                    <form id="sandboxForm" action="{{ route('admin.sandbox.services.run', $service) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        
                        @if($providers->count() > 0)
                            <div class="form-group mb-3">
                                <label for="api_provider_id" class="form-label">Provider (Optional)</label>
                                <select name="api_provider_id" id="api_provider_id" class="form-control">
                                    <option value="">Default Active Provider</option>
                                    @foreach($providers as $provider)
                                        <option value="{{ $provider->id }}">{{ $provider->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        @endif

                        @foreach($serviceConfig['fields'] as $field)
                            <div class="form-group mb-3">
                                <label for="{{ $field['name'] }}" class="form-label">{{ $field['label'] }} @if($field['required']) <span class="text-danger">*</span> @endif</label>
                                
                                @if($field['type'] === 'select')
                                    <select name="{{ $field['name'] }}" id="{{ $field['name'] }}" class="form-control" @if($field['required']) required @endif>
                                        @foreach($field['options'] as $opt)
                                            <option value="{{ $opt['value'] }}">{{ $opt['label'] }}</option>
                                        @endforeach
                                    </select>
                                @elseif($field['type'] === 'textarea')
                                    <textarea name="{{ $field['name'] }}" id="{{ $field['name'] }}" class="form-control" rows="3" @if($field['required']) required @endif></textarea>
                                @elseif($field['type'] === 'file')
                                    <input type="file" name="{{ $field['name'] }}" id="{{ $field['name'] }}" class="form-control" @if($field['required']) required @endif>
                                @else
                                    <input type="{{ $field['type'] }}" name="{{ $field['name'] }}" id="{{ $field['name'] }}" class="form-control" placeholder="{{ $field['placeholder'] ?? '' }}" @if($field['required']) required @endif>
                                @endif
                            </div>
                        @endforeach

                        <button type="submit" class="btn btn-primary w-100">Run Request</button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-6 mb-4">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-white font-weight-bold">
                    Response JSON Output
                </div>
                <div class="card-body">
                    <pre id="jsonResult" class="bg-dark text-light p-3 rounded" style="max-height: 500px; overflow-y: auto;"><code>Submit the form to view API execution result.</code></pre>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
