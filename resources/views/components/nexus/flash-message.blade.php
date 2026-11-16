@if(session('success'))
    <div class="alert alert-success border-0 rounded-4 p-3 mb-4 d-flex align-items-center gap-3 shadow-sm" style="background: rgba(16, 185, 129, 0.15); border: 1px solid rgba(16, 185, 129, 0.3) !important; color: #6ee7b7;">
        <div class="p-2 rounded-circle" style="background: rgba(16, 185, 129, 0.2); width: 36px; height: 36px; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
            <i class="fa-solid fa-circle-check text-success"></i>
        </div>
        <div class="flex-grow-1">
            <div class="fw-bold text-white">{{ session('success') }}</div>
            @if(session('vtu_result.reference'))
                <small class="text-white-50 font-mono">Order Reference: {{ session('vtu_result.reference') }}</small>
            @endif
            @if(session('vtu_result.pin') || session('vtu_result.token'))
                <div class="mt-2 p-2 rounded bg-dark border border-white-10 font-mono text-warning font-weight-bold">
                    PIN / Token: {{ session('vtu_result.pin') ?: session('vtu_result.token') }}
                    @if(session('vtu_result.serial'))
                        | Serial: {{ session('vtu_result.serial') }}
                    @endif
                </div>
            @endif
        </div>
    </div>
@endif

@if(session('error'))
    <div class="alert alert-danger border-0 rounded-4 p-3 mb-4 d-flex align-items-center gap-3 shadow-sm" style="background: rgba(239, 68, 68, 0.15); border: 1px solid rgba(239, 68, 68, 0.3) !important; color: #fca5a5;">
        <div class="p-2 rounded-circle" style="background: rgba(239, 68, 68, 0.2); width: 36px; height: 36px; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
            <i class="fa-solid fa-triangle-exclamation text-danger"></i>
        </div>
        <div class="flex-grow-1">
            <div class="fw-bold text-white">{{ session('error') }}</div>
            @if(session('vtu_result.reference'))
                <small class="text-white-50 font-mono">Reference: {{ session('vtu_result.reference') }}</small>
            @endif
        </div>
    </div>
@endif

@if($errors->any())
    <div class="alert alert-warning border-0 rounded-4 p-3 mb-4 d-flex align-items-center gap-3 shadow-sm" style="background: rgba(245, 158, 11, 0.15); border: 1px solid rgba(245, 158, 11, 0.3) !important; color: #fcd34d;">
        <div class="p-2 rounded-circle" style="background: rgba(245, 158, 11, 0.2); width: 36px; height: 36px; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
            <i class="fa-solid fa-circle-exclamation text-warning"></i>
        </div>
        <div>
            <div class="fw-bold text-white mb-1">Please review the details below:</div>
            <ul class="mb-0 pl-3 small">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    </div>
@endif
