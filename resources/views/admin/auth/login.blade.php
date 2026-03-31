@extends('layouts.nexus')

@section('title', 'Admin Login | ' . config('app.name'))
@section('public_wrapper_class', 'none')
@section('hide_nav', 'true')

@section('content')
<div class="login-wrapper">
    <div class="login-card">
        <div class="login-header">
            <h1>{{ \App\Models\SystemSetting::get('site_name', 'Fuwa.NG') }} <span class="badge-admin">Admin</span></h1>
            <p>Authorized access only. Please sign in to continue.</p>
        </div>

        @if($errors->any())
            <div class="alert alert-danger mb-4" style="background: rgba(239, 68, 68, 0.1); border: 1px solid rgba(239, 68, 68, 0.2); color: #fca5a5; border-radius: 12px; font-size: 0.9rem;">
                <ul class="mb-0">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('admin.login') }}" method="POST">
            @csrf
            <div class="form-group mb-4">
                <label for="email">Email Address</label>
                <div class="input-icon-wrapper">
                    <i class="fa-regular fa-envelope"></i>
                    <input type="email" id="email" name="email" class="form-control" placeholder="admin@example.com" required autofocus>
                </div>
            </div>

            <div class="form-group mb-4">
                <label for="password">Password</label>
                <div class="input-icon-wrapper">
                    <i class="fa-solid fa-lock"></i>
                    <input type="password" id="password" name="password" class="form-control" placeholder="••••••••" required>
                </div>
            </div>

            <div class="form-check mb-4">
                <input type="checkbox" id="remember" name="remember" class="form-check-input">
                <label for="remember" class="form-check-label text-muted small">Remember this device</label>
            </div>

            <button type="submit" class="btn btn-primary btn-block py-3 font-weight-bold">
                <i class="fa-solid fa-shield-check mr-2"></i> Access Dashboard
            </button>
        </form>

        <div class="login-footer">
            <a href="{{ url('/') }}" class="text-muted small"><i class="fa-solid fa-arrow-left mr-1"></i> Back to main site</a>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .login-wrapper {
        min-height: 100vh;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 20px;
        background: radial-gradient(circle at 15% 50%, rgba(59, 130, 246, 0.1), transparent 25%),
                    radial-gradient(circle at 85% 30%, rgba(139, 92, 246, 0.1), transparent 25%);
    }

    .login-card {
        width: 100%;
        max-width: 440px;
        background: rgba(30, 41, 59, 0.4);
        backdrop-filter: blur(20px);
        -webkit-backdrop-filter: blur(20px);
        border: 1px solid rgba(255, 255, 255, 0.08);
        border-radius: 24px;
        padding: 40px;
        box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
    }

    .login-header {
        text-align: center;
        margin-bottom: 35px;
    }

    .login-header h1 {
        font-size: 1.8rem;
        font-weight: 800;
        color: #fff;
        margin-bottom: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
    }

    .badge-admin {
        font-size: 0.75rem;
        background: var(--clr-primary);
        padding: 4px 10px;
        border-radius: 20px;
        vertical-align: middle;
        text-transform: uppercase;
        letter-spacing: 1px;
    }

    .login-header p {
        color: var(--clr-text-muted);
        font-size: 0.95rem;
    }

    .form-group label {
        display: block;
        font-size: 0.85rem;
        font-weight: 600;
        color: var(--clr-text-muted);
        margin-bottom: 8px;
    }

    .input-icon-wrapper {
        position: relative;
    }

    .input-icon-wrapper i {
        position: absolute;
        left: 15px;
        top: 50%;
        transform: translateY(-50%);
        color: var(--clr-text-muted);
        pointer-events: none;
    }

    .input-icon-wrapper .form-control {
        padding-left: 45px !important;
        background: rgba(15, 23, 42, 0.6);
        border: 1px solid rgba(255, 255, 255, 0.1);
        height: 54px;
    }

    .input-icon-wrapper .form-control:focus {
        border-color: var(--clr-primary);
        box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.15);
    }

    .login-footer {
        margin-top: 30px;
        text-align: center;
        padding-top: 20px;
        border-top: 1px solid rgba(255, 255, 255, 0.05);
    }

    .login-footer a:hover {
        color: var(--clr-primary) !important;
        text-decoration: none;
    }

    .btn-primary {
        height: 54px;
        border-radius: 14px;
        font-size: 1rem;
        transition: all 0.3s ease;
    }

    .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 20px -5px rgba(59, 130, 246, 0.4);
    }
</style>
@endpush

@push('scripts')
<script>
</script>
@endpush
