@extends('layouts.nexus')

@section('title', 'Forgot Password')
@section('public_wrapper_class', 'none')

@section('content')
<main class="d-flex align-items-center justify-content-center min-vh-100" style="background: var(--clr-bg); position: relative; overflow: hidden; padding: 2rem 0;">
    <div class="bg-glow blob-1"></div>
    <div class="bg-glow blob-2"></div>

    <div class="container position-relative" style="z-index: 10;">
        <div class="row justify-content-center">
            <div class="col-12 col-md-8 col-lg-5">
                <div class="text-center mb-4">
                    <a href="{{ url('/') }}" class="text-decoration-none d-inline-block">
                        @php $logoUrl = \App\Models\SystemSetting::get('site_logo_url'); @endphp
                        @if($logoUrl)
                            <img src="{{ $logoUrl }}" alt="Logo" style="max-height: 48px; margin-bottom: 1rem;">
                        @else
                            <div class="brand-icon mx-auto mb-3" style="background: var(--clr-primary); width: 48px; height: 48px; border-radius: 12px; display: flex; align-items: center; justify-content: center;">
                                <i class="fa-solid fa-bolt text-white" style="font-size: 24px;"></i>
                            </div>
                        @endif
                        <h2 class="text-white font-weight-bold m-0" style="letter-spacing: -0.5px;">{{ \App\Models\SystemSetting::get('site_name', 'Fuwa.NG') }}</h2>
                    </a>
                </div>

                <div class="card border-0 rounded-4 shadow-lg" style="background: rgba(17, 24, 39, 0.65); backdrop-filter: blur(20px); border: 1px solid rgba(255,255,255,0.08) !important;">
                    <div class="card-body p-4 p-md-5">
                        <div class="text-center mb-4">
                            <h4 class="text-white font-weight-bold mb-2">Reset Password</h4>
                            <p class="text-white-50 small mb-0">Enter your email address and we'll send you a link to reset your password.</p>
                        </div>

                        @if (session('status'))
                            <div class="alert alert-success small" style="background: rgba(16, 185, 129, 0.1); border: 1px solid rgba(16, 185, 129, 0.2); color: #bbf7d0;">
                                {{ session('status') }}
                            </div>
                        @endif

                        <form method="POST" action="{{ route('password.email') }}">
                            @csrf
                            <div class="form-group mb-4">
                                <label class="text-white-50 small mb-2 font-weight-bold">Email Address</label>
                                <div class="position-relative">
                                    <i class="fa-regular fa-envelope position-absolute text-white-50" style="left: 15px; top: 50%; transform: translateY(-50%);"></i>
                                    <input type="email" name="email" class="form-control text-white @error('email') is-invalid @enderror" placeholder="Enter your registered email" value="{{ old('email') }}" required autofocus style="background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.1); padding-left: 45px; height: 50px; border-radius: 12px;">
                                </div>
                                @error('email')
                                    <span class="text-danger small mt-1 d-block"><i class="fa-solid fa-circle-exclamation mr-1"></i>{{ $message }}</span>
                                @enderror
                            </div>

                            <button type="submit" class="btn btn-primary btn-block w-100 font-weight-bold py-3 mb-4" style="border-radius: 12px; background: linear-gradient(135deg, var(--clr-primary), var(--clr-primary-hover)); border: none; font-size: 1rem;">
                                Send Reset Link <i class="fa-solid fa-arrow-right ml-2"></i>
                            </button>

                            <div class="text-center">
                                <p class="text-white-50 small mb-0">Remember your password? <a href="{{ route('login') }}" class="text-primary text-decoration-none font-weight-bold">Log in</a></p>
                            </div>
                        </form>
                    </div>
                </div>
                
                <div class="text-center mt-4">
                    <p class="text-white-50 small">© {{ date('Y') }} {{ \App\Models\SystemSetting::get('site_name', 'Fuwa.NG') }}. All rights reserved.</p>
                </div>
            </div>
        </div>
    </div>
</main>
@endsection
