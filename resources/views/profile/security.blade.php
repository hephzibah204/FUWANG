@extends('layouts.app')

@section('title', 'Security Settings')

@section('content')
<div class="row">
    <div class="col-md-8 offset-md-2">
        <div class="card">
            <div class="card-header">Security Settings</div>

            <div class="card-body">
                @if (session('status'))
                    <div class="alert alert-success" role="alert">
                        {{ session('status') }}
                    </div>
                @endif

                <h5 class="card-title">Two-Factor Authentication (2FA)</h5>

                @if ($user->two_factor_secret)
                    <div class="alert alert-success">2FA is currently <strong>enabled</strong> on your account.</div>
                    <p>To disable 2FA, please enter your password below.</p>
                    <form method="POST" action="{{ route('user.2fa.disable') }}">
                        @csrf
                        <div class="form-group">
                            <label for="password">Current Password</label>
                            <input type="password" name="password" id="password" class="form-control" required>
                        </div>
                        <button type="submit" class="btn btn-danger">Disable 2FA</button>
                    </form>
                @else
                    <div class="alert alert-info">2FA is currently <strong>disabled</strong> on your account.</div>
                    <p>To enable 2FA, click the button below. You will be shown a QR code to scan with your authenticator app.</p>
                    <form method="POST" action="{{ route('user.2fa.enable') }}">
                        @csrf
                        <button type="submit" class="btn btn-primary">Enable 2FA</button>
                    </form>
                @endif
            </div>
        </div>
    </div>
</div>

@if (session('2fa_secret'))
<div class="modal fade" id="2fa-setup-modal" tabindex="-1" role="dialog" aria-labelledby="2fa-setup-modal-label" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="2fa-setup-modal-label">Enable Two-Factor Authentication</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body text-center">
                <p>Scan the QR code below with your authenticator app (e.g., Google Authenticator, Authy).</p>
                <img src="{{ session('2fa_qr_code') }}" alt="QR Code" class="img-fluid">
                <p class="mt-3">If you cannot scan the QR code, you can manually enter this secret key:</p>
                <p><strong>{{ session('2fa_secret') }}</strong></p>
                <hr>
                <p>After scanning, enter the 6-digit code from your app to verify the setup.</p>
                <form method="POST" action="{{ route('user.2fa.verify') }}">
                    @csrf
                    <div class="form-group">
                        <input type="text" name="2fa_code" class="form-control" required placeholder="6-digit code">
                    </div>
                    <button type="submit" class="btn btn-primary">Verify &amp; Complete Setup</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endif

@endsection

@push('scripts')
@if (session('2fa_secret'))
<script>
    $(function() {
        $('#2fa-setup-modal').modal('show');
    });
</script>
@endif
@endpush
