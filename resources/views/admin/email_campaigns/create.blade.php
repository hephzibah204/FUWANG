@extends('layouts.nexus')

@section('title', 'New Email Campaign')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-white">New Email Campaign</h1>
            <p class="text-muted mb-0">Use {{'{{name}}'}} to inject recipient name.</p>
        </div>
        <a href="{{ route('admin.email_campaigns.index') }}" class="btn btn-outline-secondary">
            <i class="fa-solid fa-arrow-left mr-2"></i> Back
        </a>
    </div>

    <div class="card border-0 shadow-sm" style="background:#1e293b;">
        <div class="card-body">
            <form method="POST" action="{{ route('admin.email_campaigns.store') }}">
                @csrf
                <div class="row">
                    <div class="col-md-6 form-group">
                        <label class="text-white-50 small">Campaign Name</label>
                        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" required>
                        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6 form-group">
                        <label class="text-white-50 small">Subject</label>
                        <input type="text" name="subject" class="form-control @error('subject') is-invalid @enderror" value="{{ old('subject') }}" required>
                        @error('subject')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>

                <div class="form-group">
                    <label class="text-white-50 small">HTML Body</label>
                    <textarea name="html" class="form-control @error('html') is-invalid @enderror" rows="10" required>{{ old('html') }}</textarea>
                    @error('html')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="row">
                    <div class="col-md-4 form-group">
                        <label class="text-white-50 small">Audience</label>
                        <select name="audience_type" class="form-control @error('audience_type') is-invalid @enderror" required>
                            <option value="all" {{ old('audience_type', 'all') === 'all' ? 'selected' : '' }}>All users</option>
                            <option value="emails" {{ old('audience_type') === 'emails' ? 'selected' : '' }}>Emails list</option>
                            <option value="user_ids" {{ old('audience_type') === 'user_ids' ? 'selected' : '' }}>User IDs list</option>
                            <option value="segment" {{ old('audience_type') === 'segment' ? 'selected' : '' }}>Advanced segment (JSON)</option>
                        </select>
                        @error('audience_type')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-8 form-group">
                        <label class="text-white-50 small">Audience Value (comma or space separated)</label>
                        <input type="text" name="audience_value" class="form-control @error('audience_value') is-invalid @enderror" value="{{ old('audience_value') }}" placeholder="email1@example.com, email2@example.com">
                        @error('audience_value')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
                <div class="form-group">
                    <label class="text-white-50 small">Advanced Segment JSON (optional)</label>
                    <textarea name="segment_json" class="form-control @error('segment') is-invalid @enderror" rows="6" placeholder='{"operator":"AND","rules":[{"field":"reseller_id","op":"eq","value":"R-001"},{"field":"wallet_balance","op":"gte","value":1000},{"field":"signup_date","op":"between","value":{"from":"2026-01-01","to":"2026-03-01"}},{"field":"user_status","op":"in","value":["active"]}] }'>{{ old('segment_json') }}</textarea>
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
