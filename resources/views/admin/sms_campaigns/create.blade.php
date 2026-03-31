@extends('layouts.nexus')

@section('title', 'New SMS Campaign')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-white">New SMS Campaign</h1>
            <p class="text-muted mb-0">Requires an active SMS provider configured in Custom APIs (service type: sms_gateway).</p>
        </div>
        <a href="{{ route('admin.sms_campaigns.index') }}" class="btn btn-outline-secondary">
            <i class="fa-solid fa-arrow-left mr-2"></i> Back
        </a>
    </div>

    <div class="card border-0 shadow-sm" style="background:#1e293b;">
        <div class="card-body">
            <form method="POST" action="{{ route('admin.sms_campaigns.store') }}">
                @csrf
                <div class="row">
                    <div class="col-md-6 form-group">
                        <label class="text-white-50 small">Campaign Name</label>
                        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" required>
                        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6 form-group">
                        <label class="text-white-50 small">Provider</label>
                        <select name="custom_api_id" class="form-control @error('custom_api_id') is-invalid @enderror">
                            <option value="">Auto (highest priority active)</option>
                            @foreach($providers as $p)
                                <option value="{{ $p->id }}" {{ (string) old('custom_api_id') === (string) $p->id ? 'selected' : '' }}>
                                    {{ $p->name }} {{ $p->status ? '(Active)' : '(Disabled)' }}
                                </option>
                            @endforeach
                        </select>
                        @error('custom_api_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4 form-group">
                        <label class="text-white-50 small">Sender ID</label>
                        <input type="text" name="sender_id" class="form-control @error('sender_id') is-invalid @enderror" value="{{ old('sender_id') }}" placeholder="GSOFT">
                        @error('sender_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-8 form-group">
                        <label class="text-white-50 small">Message (max 1000 chars)</label>
                        <input type="text" name="message" class="form-control @error('message') is-invalid @enderror" value="{{ old('message') }}" required>
                        @error('message')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4 form-group">
                        <label class="text-white-50 small">Audience</label>
                        <select name="audience_type" class="form-control @error('audience_type') is-invalid @enderror" required>
                            <option value="all" {{ old('audience_type', 'all') === 'all' ? 'selected' : '' }}>All users with phone</option>
                            <option value="phones" {{ old('audience_type') === 'phones' ? 'selected' : '' }}>Phones list</option>
                            <option value="user_ids" {{ old('audience_type') === 'user_ids' ? 'selected' : '' }}>User IDs list</option>
                            <option value="segment" {{ old('audience_type') === 'segment' ? 'selected' : '' }}>Advanced segment (JSON)</option>
                        </select>
                        @error('audience_type')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-8 form-group">
                        <label class="text-white-50 small">Audience Value (comma or space separated)</label>
                        <input type="text" name="audience_value" class="form-control @error('audience_value') is-invalid @enderror" value="{{ old('audience_value') }}" placeholder="08012345678, 08098765432">
                        @error('audience_value')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
                <div class="form-group">
                    <label class="text-white-50 small">Advanced Segment JSON (optional)</label>
                    <textarea name="segment_json" class="form-control @error('segment') is-invalid @enderror" rows="6" placeholder='{"operator":"AND","rules":[{"field":"reseller_id","op":"eq","value":"R-001"},{"field":"wallet_balance","op":"between","value":{"min":0,"max":5000}},{"field":"user_status","op":"in","value":["active"]}] }'>{{ old('segment_json') }}</textarea>
                    @error('segment')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <button class="btn btn-primary" type="submit">
                    <i class="fa-solid fa-floppy-disk mr-2"></i> Save Draft
                </button>
            </form>
        </div>
    </div>
</div>
@endsection
