@extends('layouts.nexus')

@section('title', 'Create Broadcast')

@section('content')
<div class="container-fluid py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="d-flex align-items-center mb-4">
                <a href="{{ route('admin.broadcasts.index') }}" class="btn btn-outline-light mr-3">
                    <i class="fa-solid fa-arrow-left"></i>
                </a>
                <h1 class="h3 mb-0 text-white">New Broadcast</h1>
            </div>

            <div class="card border-0 shadow-sm" style="background: #1e293b;">
                <div class="card-body p-4">
                    <form action="{{ route('admin.broadcasts.store') }}" method="POST">
                        @csrf
                        
                        <div class="form-group mb-4">
                            <label class="text-white-50 mb-2">Subject</label>
                            <input type="text" name="subject" class="form-control bg-dark text-white border-secondary" required placeholder="e.g. Important System Update">
                        </div>

                        <div class="form-group mb-4">
                            <label class="text-white-50 mb-2">Message Content</label>
                            <textarea name="message" rows="6" class="form-control bg-dark text-white border-secondary" required placeholder="Type your message here..."></textarea>
                            <small class="text-muted">HTML tags are allowed for formatting.</small>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group mb-4">
                                    <label class="text-white-50 mb-2">Target Audience</label>
                                    <select name="target_audience" class="form-control bg-dark text-white border-secondary">
                                        <option value="all">All Users</option>
                                        <option value="active">Active Users (Last 30 days)</option>
                                        <option value="inactive">Inactive Users</option>
                                        <option value="vip">VIP / High Value</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group mb-4">
                                    <label class="text-white-50 mb-2">Schedule (Optional)</label>
                                    <input type="datetime-local" name="scheduled_at" class="form-control bg-dark text-white border-secondary">
                                    <small class="text-muted">Leave blank to send immediately.</small>
                                </div>
                            </div>
                        </div>

                        <div class="border-top border-secondary pt-4 mt-2 d-flex justify-content-end">
                            <button type="submit" name="save_draft" value="1" class="btn btn-outline-light mr-2">
                                <i class="fa-solid fa-save mr-1"></i> Save Draft
                            </button>
                            <button type="submit" name="send_now" value="1" class="btn btn-primary">
                                <i class="fa-solid fa-paper-plane mr-1"></i> Send / Schedule
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
