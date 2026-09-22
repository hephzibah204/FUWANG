@extends('layouts.app')

@section('title', 'Report Terminal Issue | ' . config('app.name'))

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8 fade-in">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-4">
                <div>
                    <h3 class="text-white fw-bold mb-1"><i class="fa-solid fa-triangle-exclamation text-warning me-2"></i>Report Terminal / Service Fault</h3>
                    <p class="text-white-50 mb-0">Describe the issue and upload error screenshots so administration can resolve it quickly.</p>
                </div>
                <a href="{{ route('agent.issues.index') }}" class="btn btn-outline-light rounded-pill btn-sm">Back to Issues</a>
            </div>

            <div class="card border-0 rounded-4 p-4" style="background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.08) !important;">
                <form action="{{ route('agent.issues.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label text-white small fw-bold">Issue Category</label>
                            <select name="category" class="form-select @error('category') is-invalid @enderror" required>
                                <option value="terminal_hardware" {{ old('category') === 'terminal_hardware' ? 'selected' : '' }}>Terminal / Biometric Hardware Fault</option>
                                <option value="nin_verification" {{ old('category') === 'nin_verification' ? 'selected' : '' }}>NIN Verification Gateway Error</option>
                                <option value="nin_modification" {{ old('category') === 'nin_modification' ? 'selected' : '' }}>NIN Modification / Requery Issue</option>
                                <option value="wallet_funding" {{ old('category') === 'wallet_funding' ? 'selected' : '' }}>Agency Wallet / Funding Discrepancy</option>
                                <option value="account_access" {{ old('category') === 'account_access' ? 'selected' : '' }}>Account / Machine Authorization</option>
                                <option value="other" {{ old('category') === 'other' ? 'selected' : '' }}>Other General Issue</option>
                            </select>
                            @error('category') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label text-white small fw-bold">Priority Level</label>
                            <select name="priority" class="form-select @error('priority') is-invalid @enderror" required>
                                <option value="low" {{ old('priority') === 'low' ? 'selected' : '' }}>Low — Minor inquiry</option>
                                <option value="medium" {{ old('priority', 'medium') === 'medium' ? 'selected' : '' }}>Medium — Standard terminal issue</option>
                                <option value="high" {{ old('priority') === 'high' ? 'selected' : '' }}>High — Enrollment blocked</option>
                                <option value="urgent" {{ old('priority') === 'urgent' ? 'selected' : '' }}>Urgent — Critical system outage</option>
                            </select>
                            @error('priority') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-12">
                            <label class="form-label text-white small fw-bold">Machine IMEI / Terminal Serial</label>
                            <input type="text" name="machine_imei" class="form-control @error('machine_imei') is-invalid @enderror" value="{{ old('machine_imei', $agent?->machine_imei) }}" placeholder="e.g. 864201041234567">
                            <small class="text-white-50">Auto-populated from your registered agency terminal IMEI.</small>
                            @error('machine_imei') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-12">
                            <label class="form-label text-white small fw-bold">Subject / Short Summary</label>
                            <input type="text" name="subject" class="form-control @error('subject') is-invalid @enderror" value="{{ old('subject') }}" required placeholder="e.g. Scanner error code 403 on biometric capture">
                            @error('subject') <div class="invalid-feedback">{{ $message }}</div> @errorEnd
                        </div>

                        <div class="col-md-12">
                            <label class="form-label text-white small fw-bold">Detailed Issue Description</label>
                            <textarea name="message" rows="5" class="form-control @error('message') is-invalid @enderror" required placeholder="Describe what happened, error messages displayed, and steps taken..."></textarea>
                            @error('message') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-12">
                            <label class="form-label text-white small fw-bold">Upload Error Screenshot / Document Proof (Optional)</label>
                            <input type="file" name="attachment" accept="image/png,image/jpeg,image/webp,application/pdf" class="form-control @error('attachment') is-invalid @enderror">
                            <small class="text-white-50">Upload PNG, JPG, WEBP, or PDF file (max 5MB).</small>
                            @error('attachment') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <div class="mt-4 pt-2">
                        <button type="submit" class="btn btn-primary rounded-pill px-5 py-2 text-white fw-bold">
                            <i class="fa-solid fa-paper-plane me-2"></i>Submit Issue for Resolution
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
