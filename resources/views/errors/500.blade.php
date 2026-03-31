@extends('layouts.app')

@section('title', 'Server Error - ' . config('app.name'))

@section('content')
<div class="d-flex align-items-center justify-content-center min-vh-100" style="background-color: #f8f9fc;">
    <div class="text-center px-4">
        <div class="mb-4">
            <h1 class="display-1 font-weight-bold text-danger" style="font-size: 8rem; text-shadow: 2px 2px 4px rgba(0,0,0,0.1);">500</h1>
        </div>
        <h2 class="h3 mb-3 text-dark">Internal Server Error</h2>
        <p class="text-muted mb-5 lead" style="max-width: 500px; margin: 0 auto;">
            Oops, something went wrong on our end. Our team has been notified and we are working to fix the issue.
        </p>
        <div class="d-flex justify-content-center gap-3">
            <button onclick="window.history.back()" class="btn btn-outline-secondary btn-lg rounded-pill px-4 shadow-sm transition-all mr-3" style="transition: all 0.3s ease;">
                <i class="fa fa-arrow-left mr-2"></i> Go Back
            </button>
            <a href="{{ url('/') }}" class="btn btn-primary btn-lg rounded-pill px-4 shadow-sm transition-all" style="transition: all 0.3s ease;">
                <i class="fa fa-home mr-2"></i> Return Home
            </a>
        </div>
    </div>
</div>

@push('styles')
<style>
    .transition-all:hover {
        transform: translateY(-2px);
        box-shadow: 0 .5rem 1rem rgba(0,0,0,.15)!important;
    }
</style>
@endpush
@endsection
