@extends('layouts.postoffice')

@section('title', 'Logistics Ops Login')

@section('content')
<div class="row align-items-center" style="min-height: calc(100vh - 180px);">
    <div class="col-lg-6 mb-4 mb-lg-0">
        <div class="mb-3">
            <span class="badge badge-pill px-3 py-2" style="background: rgba(59, 130, 246, 0.15); border: 1px solid rgba(59, 130, 246, 0.25); color: var(--po-accent);">
                Logistics Operations
            </span>
        </div>
        <h1 class="font-weight-bold mb-3" style="font-size: 2.5rem; line-height: 1.1;">
            Staff access to <span style="color:var(--po-primary)">FuwaPost</span>
        </h1>
        <p class="text-white-50 mb-4" style="max-width: 560px;">
            Managers and Officers can sign in here to manage orders, shipments, delivery agents, inventory, and analytics.
        </p>
        <img src="{{ asset('images/hero_visual.png') }}" alt="Logistics illustration" class="img-fluid" style="max-height: 260px; opacity: 0.95;">
    </div>

    <div class="col-lg-6">
        <div class="glass-card p-4 p-md-5">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h4 class="font-weight-bold mb-0">Staff login</h4>
                <a href="{{ route('logistics.home') }}" class="text-white-50 text-decoration-none small">
                    Back to Logistics
                </a>
            </div>

            @if ($errors->any())
                <div class="alert alert-danger border-0 mb-4" style="background: rgba(220, 38, 38, 0.15); color: #fff;">
                    {{ $errors->first() }}
                </div>
            @endif

            <form method="POST" action="{{ route('logistics.ops.login.post') }}">
                @csrf
                <div class="form-group">
                    <label for="email" class="text-white-50 small">Email address</label>
                    <input id="email" name="email" type="email" class="form-control tracking-input" required autofocus value="{{ old('email') }}">
                </div>

                <div class="form-group">
                    <label for="password" class="text-white-50 small">Password</label>
                    <input id="password" name="password" type="password" class="form-control tracking-input" required>
                </div>

                <button type="submit" class="btn btn-po-primary btn-block">
                    <i class="fa fa-arrow-right-to-bracket mr-2"></i> Sign in
                </button>
            </form>
        </div>
    </div>
</div>
@endsection

