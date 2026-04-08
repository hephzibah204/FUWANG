@extends('layouts.app')

@section('title', 'Two-Factor Authentication')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">Two-Factor Authentication</div>

            <div class="card-body">
                <p class="text-center">Please enter the 6-digit code from your authenticator app to continue.</p>

                <form method="POST" action="{{ route('login.2fa.verify') }}">
                    @csrf

                    <div class="form-group">
                        <label for="2fa_code">Authentication Code</label>
                        <input id="2fa_code" type="text" class="form-control @error('2fa_code') is-invalid @enderror" name="2fa_code" required autofocus>

                        @error('2fa_code')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>

                    <div class="form-group mb-0">
                        <button type="submit" class="btn btn-primary btn-block">
                            Verify
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
