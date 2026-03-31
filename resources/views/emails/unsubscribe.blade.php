@extends('layouts.nexus')

@section('title', 'Email Preferences | ' . config('app.name'))
@section('public_wrapper_class', 'none')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card" style="background: rgba(30,41,59,0.45); border: 1px solid rgba(255,255,255,0.08); border-radius: 18px;">
                <div class="card-body p-4 p-md-5">
                    <h3 class="text-white mb-2">Email preferences updated</h3>

                    @if($updated)
                        <p class="text-white-50 mb-4">
                            Your preferences have been saved for <span class="text-white">{{ $user->email }}</span>.
                        </p>
                    @else
                        <p class="text-warning mb-4">
                            Preferences could not be saved because the email preferences table is not available.
                        </p>
                    @endif

                    <div class="text-white-50 small">
                        <div>Scope: <span class="text-white">{{ $scope }}</span></div>
                        <div class="mt-2">You can still manage notifications from your account settings after logging in.</div>
                    </div>

                    <div class="mt-4">
                        <a href="{{ url('/') }}" class="btn btn-primary">Back to homepage</a>
                        <a href="{{ url('/dashboard') }}" class="btn btn-outline-light ml-2">Go to dashboard</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

