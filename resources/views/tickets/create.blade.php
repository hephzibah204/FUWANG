@extends('layouts.nexus')

@section('title', 'Open New Ticket | ' . config('app.name'))

@section('content')
<div class="dashboard-wrapper fade-in">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="mb-4">
                <a href="{{ route('tickets.index') }}" class="btn btn-sm btn-link text-white-50 p-0 mb-3">
                    <i class="fa-solid fa-arrow-left"></i> Back to Tickets
                </a>
                <h1 class="h3 font-weight-bold mb-1">Open a New Ticket</h1>
                <p class="text-white-50">Please describe your issue below. Our support team will get back to you shortly.</p>
            </div>

            <div class="panel-card p-4">
                <form action="{{ route('tickets.store') }}" method="POST">
                    @csrf
                    
                    <div class="form-group mb-4">
                        <label class="font-weight-600 mb-2">Subject</label>
                        <input type="text" name="subject" class="form-control" placeholder="Brief summary of your issue" required maxlength="255">
                    </div>

                    <div class="form-group mb-4">
                        <label class="font-weight-600 mb-2">Message</label>
                        <textarea name="message" class="form-control" rows="6" placeholder="Provide as much detail as possible..." required></textarea>
                    </div>

                    <div class="text-right">
                        <button type="submit" class="btn btn-primary px-4 py-2">
                            <i class="fa-solid fa-paper-plane mr-2"></i> Submit Ticket
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
