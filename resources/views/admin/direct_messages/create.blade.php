@extends('layouts.nexus')

@section('title', 'New Direct Message')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-white">New Direct Message</h1>
            <p class="text-muted mb-0">Send to all users or specific lists.</p>
        </div>
        <a href="{{ route('admin.direct_messages.index') }}" class="btn btn-outline-secondary">
            <i class="fa-solid fa-arrow-left mr-2"></i> Back
        </a>
    </div>

    <div class="card border-0 shadow-sm" style="background:#1e293b;">
        <div class="card-body">
            <form method="POST" action="{{ route('admin.direct_messages.store') }}">
                @csrf
                <div class="form-group">
                    <label class="text-white-50 small">Title</label>
                    <input type="text" name="title" class="form-control @error('title') is-invalid @enderror" value="{{ old('title', request('title')) }}" required>
                    @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="form-group">
                    <label class="text-white-50 small">Message</label>
                    <textarea name="message" class="form-control @error('message') is-invalid @enderror" rows="6" required>{{ old('message') }}</textarea>
                    @error('message')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="text-white-50 small">Audience</label>
                            @php($audType = old('audience_type', request('audience_type', 'all')))
                            <select name="audience_type" class="form-control @error('audience_type') is-invalid @enderror" required>
                                <option value="all" {{ $audType === 'all' ? 'selected' : '' }}>All users</option>
                                <option value="emails" {{ $audType === 'emails' ? 'selected' : '' }}>Emails list</option>
                                <option value="user_ids" {{ $audType === 'user_ids' ? 'selected' : '' }}>User IDs list</option>
                            </select>
                            @error('audience_type')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                    <div class="col-md-8">
                        <div class="form-group">
                            <label class="text-white-50 small">Audience Value (comma or space separated)</label>
                            <input type="text" name="audience_value" class="form-control @error('audience_value') is-invalid @enderror" value="{{ old('audience_value', request('audience_value')) }}" placeholder="email1@example.com, email2@example.com">
                            @error('audience_value')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label class="text-white-50 small d-block mb-2">Channels</label>
                    <div class="d-flex flex-wrap" style="gap: 16px;">
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input" id="chInApp" name="channel_in_app" value="1" {{ old('channel_in_app', '1') ? 'checked' : '' }}>
                            <label class="custom-control-label text-white-50" for="chInApp">In-app</label>
                        </div>
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input" id="chEmail" name="channel_email" value="1" {{ old('channel_email') ? 'checked' : '' }}>
                            <label class="custom-control-label text-white-50" for="chEmail">Email</label>
                        </div>
                    </div>
                </div>

                <div class="d-flex gap-2" style="gap:10px;">
                    <button class="btn btn-primary px-4" type="submit" name="send_now" value="1">
                        <i class="fa-solid fa-paper-plane mr-2"></i> Send Message
                    </button>
                    <button class="btn btn-outline-secondary px-4" type="submit">
                        <i class="fa-solid fa-floppy-disk mr-2"></i> Save Draft
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
