@extends('layouts.nexus')

@section('title', 'Admin Two-Factor Setup | ' . config('app.name'))
@section('public_wrapper_class', 'none')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card glass-card border-0 p-4" style="background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.06) !important;">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h4 class="text-white mb-0"><i class="fa-solid fa-shield-halved text-primary mr-2"></i> Two-Factor Authentication</h4>
                    <a href="{{ route('admin.dashboard') }}" class="text-primary small">Back to Dashboard</a>
                </div>

                @if(session('success'))
                    <div class="alert alert-success" role="alert" style="background: rgba(34,197,94,.15); border: 1px solid rgba(34,197,94,.35); color: #86efac;">
                        {{ session('success') }}
                    </div>
                @endif

                @if($enabled)
                    <p class="text-white-50">Two-Factor Authentication is currently enabled on your account.</p>
                    <form action="{{ route('admin.settings.security.2fa.disable') }}" method="POST" class="mt-3">
                        @csrf
                        <button class="btn btn-danger"><i class="fa-solid fa-xmark mr-1"></i> Disable 2FA</button>
                    </form>
                @else
                    <p class="text-white-50 mb-3">Enable Two-Factor Authentication to add an extra layer of security to your admin account.</p>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="text-center p-3" style="background: rgba(255,255,255,0.02); border: 1px solid rgba(255,255,255,0.06); border-radius: 12px;">
                                <img src="{{ $qrImage }}" alt="Scan QR Code" width="240" height="240">
                                <p class="small text-white-50 mt-2 mb-0">Scan with Google Authenticator, Authy, or Microsoft Authenticator.</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="text-white-50 small">Secret Key</label>
                            <div class="form-control" style="background: rgba(255,255,255,0.06); color: #fff;">{{ $secret }}</div>

                            <form action="{{ route('admin.settings.security.2fa.enable') }}" method="POST" class="mt-3">
                                @csrf
                                <div class="form-group">
                                    <label for="code">Enter 6-digit code</label>
                                    <input type="text" name="code" id="code" class="form-control" placeholder="000000" required maxlength="6" inputmode="numeric" pattern="[0-9]{6}">
                                    @error('code')
                                        <div class="text-danger small mt-1">{{ $message }}</div>
                                    @enderror
                                </div>
                                <button class="btn btn-primary"><i class="fa-solid fa-check mr-1"></i> Enable 2FA</button>
                            </form>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
