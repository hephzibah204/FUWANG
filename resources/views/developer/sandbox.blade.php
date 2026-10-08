@extends('layouts.nexus')

@section('title', 'API Sandbox & Key Tester | Developer')

@section('content')
<div class="container-fluid py-3">
    <!-- Header Breadcrumbs & Controls -->
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-3 mb-4">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <a href="{{ route('developer.portal') }}" class="text-white-50 text-xs text-decoration-none">
                    <i class="fa-solid fa-arrow-left me-1"></i> Developer Portal
                </a>
                <span class="text-white-50 text-xs">/</span>
                <span class="text-info text-xs font-semibold">API Sandbox</span>
            </div>
            <h1 class="h3 text-white font-weight-bold mb-1">
                <i class="fa-solid fa-flask-vial text-info me-2"></i>API Sandbox & Key Tester
            </h1>
            <p class="text-muted small mb-0">Test your API tokens, simulate identity verifications, inspect live HTTP responses, and debug integration payloads in real-time.</p>
        </div>
        <div class="d-flex align-items-center gap-2 flex-wrap">
            <span class="badge bg-info bg-opacity-25 text-info px-3 py-2 rounded-pill font-monospace text-xs">
                <i class="fa-solid fa-shield-halved me-1"></i> Sandbox Testnet
            </span>
            <a href="{{ route('developer.docs') }}" class="btn btn-sm btn-outline-light rounded-pill px-3 py-1.5 text-xs">
                <i class="fa-solid fa-book me-1"></i> Documentation
            </a>
            <a href="{{ route('developer.portal') }}" class="btn btn-sm btn-outline-primary rounded-pill px-3 py-1.5 text-xs">
                <i class="fa-solid fa-key me-1"></i> Manage Keys
            </a>
        </div>
    </div>

    <!-- Account Status Pill Banner -->
    <div class="glass-card p-3 rounded-20 border border-white-10 mb-4 bg-dark bg-opacity-50">
        <div class="row align-items-center g-3 text-xs">
            <div class="col-md-3 col-6">
                <div class="text-white-50 mb-0.5">Developer Account</div>
                <div class="text-white fw-bold text-truncate" title="{{ $user->email }}">{{ $user->email }}</div>
            </div>
            <div class="col-md-3 col-6">
                <div class="text-white-50 mb-0.5">API Access Status</div>
                <div>
                    @if($user->api_access_status === 'approved')
                        <span class="badge bg-success bg-opacity-25 text-success rounded-pill px-2.5 py-0.5 fw-bold">
                            <i class="fa-solid fa-circle-check me-1"></i> Approved
                        </span>
                    @elseif($user->api_access_status === 'pending')
                        <span class="badge bg-warning bg-opacity-25 text-warning rounded-pill px-2.5 py-0.5 fw-bold">
                            <i class="fa-solid fa-clock me-1"></i> Pending Review
                        </span>
                    @else
                        <span class="badge bg-secondary rounded-pill px-2.5 py-0.5">
                            {{ ucfirst($user->api_access_status ?: 'None') }}
                        </span>
                    @endif
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="text-white-50 mb-0.5">Wallet Balance</div>
                <div class="text-white fw-bold">
                    ₦{{ number_format($userBalance, 2) }}
                    @if($userBalance >= $minBalance)
                        <span class="text-success text-3xs ms-1"><i class="fa-solid fa-check"></i> (Ready)</span>
                    @else
                        <span class="text-warning text-3xs ms-1"><i class="fa-solid fa-triangle-exclamation"></i> (&lt; ₦{{ number_format($minBalance, 2) }})</span>
                    @endif
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="text-white-50 mb-0.5">Base API URL</div>
                <div class="text-info font-monospace text-truncate" id="sandboxBaseUrl">{{ $baseUrl }}</div>
            </div>
        </div>
    </div>

    <!-- Main Sandbox Workspace -->
    <div class="row g-4">
        <!-- Left Column: Request Builder & Key Selector -->
        <div class="col-xl-6 col-lg-6 col-12">
            <div class="glass-card rounded-20 border border-white-10 p-4 h-100" style="background: rgba(15, 23, 42, 0.65);">
                <div class="d-flex align-items-center justify-content-between pb-3 mb-3 border-bottom border-white-10">
                    <h5 class="text-white font-weight-bold mb-0">
                        <i class="fa-solid fa-sliders text-primary me-2"></i>Request Builder
                    </h5>
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge bg-primary bg-opacity-25 text-primary rounded-pill px-2.5 py-1 text-2xs font-monospace">HTTP Console</span>
                    </div>
                </div>

                <form id="sandboxForm" onsubmit="event.preventDefault(); runSandboxTest();">
                    <!-- 1. API Key Source Selector -->
                    <div class="mb-3">
                        <label class="form-label text-white-50 text-xs fw-semibold d-flex justify-content-between align-items-center">
                            <span>1. Authentication (API Key)</span>
                            <span id="keyValidationBadge" class="badge bg-secondary text-white-50 text-3xs rounded-pill">Unchecked</span>
                        </label>

                        <!-- Key mode toggle -->
                        <div class="btn-group w-100 mb-2 p-1 bg-dark rounded-12 border border-white-10" role="group">
                            <input type="radio" class="btn-check" name="key_mode" id="keyModeSaved" value="saved" checked onchange="toggleKeyMode('saved')">
                            <label class="btn btn-sm btn-outline-light border-0 rounded-10 text-xs py-1.5" for="keyModeSaved">
                                <i class="fa-solid fa-list-check me-1"></i> Select Saved Token
                            </label>

                            <input type="radio" class="btn-check" name="key_mode" id="keyModeCustom" value="custom" onchange="toggleKeyMode('custom')">
                            <label class="btn btn-sm btn-outline-light border-0 rounded-10 text-xs py-1.5" for="keyModeCustom">
                                <i class="fa-solid fa-terminal me-1"></i> Paste Full Key (<code class="text-warning">nx_...</code>)
                            </label>
                        </div>

                        <!-- Dropdown of saved keys -->
                        <div id="savedKeyGroup">
                            <select id="tokenIdSelect" class="form-control form-control-sm text-white bg-dark border-white-10 rounded-12 font-monospace" onchange="onTokenSelectChange()">
                                @forelse($tokens as $t)
                                    <option value="{{ $t->id }}" data-lastfour="{{ $t->last_four }}" data-revoked="{{ $t->revoked_at ? '1' : '0' }}" {{ $preselectedTokenId == $t->id ? 'selected' : '' }}>
                                        {{ $t->name }} (••••{{ $t->last_four }}) {{ $t->revoked_at ? '[REVOKED]' : '[ACTIVE]' }}
                                    </option>
                                @empty
                                    <option value="" disabled selected>No saved API tokens found. Create one in Developer Portal.</option>
                                @endforelse
                            </select>
                        </div>

                        <!-- Custom plain key input -->
                        <div id="customKeyGroup" class="d-none">
                            <div class="input-group">
                                <span class="input-group-text bg-dark border-white-10 text-white-50 font-monospace text-xs">Bearer</span>
                                <input type="text" id="customApiKeyInput" class="form-control form-control-sm text-white bg-dark border-white-10 font-monospace" placeholder="nx_xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx" value="{{ $preselectedKey ?: '' }}">
                                <button type="button" class="btn btn-sm btn-outline-info rounded-end-12 px-3 text-xs" onclick="validateCurrentKey()">
                                    <i class="fa-solid fa-magnifying-glass me-1"></i> Verify
                                </button>
                            </div>
                            <span class="text-white-50 text-3xs mt-1 d-block">
                                Paste your active secret key starting with <code class="text-warning">nx_</code> to test live authorization headers.
                            </span>
                        </div>
                    </div>

                    <!-- 2. Endpoint Selection -->
                    <div class="mb-3">
                        <label class="form-label text-white-50 text-xs fw-semibold">2. Target Endpoint</label>
                        <select id="endpointPreset" class="form-control form-control-sm text-white bg-dark border-white-10 rounded-12 mb-2" onchange="onPresetEndpointChange()">
                            <optgroup label="Core & Authentication">
                                <option value="me" data-method="GET" data-path="/api/v1/me">GET /api/v1/me - Account & Healthcheck (Free)</option>
                                <option value="legal_catalog" data-method="GET" data-path="/api/v1/legal/catalog">GET /api/v1/legal/catalog - Legal Catalog (Free)</option>
                            </optgroup>
                            <optgroup label="Identity Verification">
                                <option value="nin_verify" data-method="POST" data-path="/api/v1/verifications/nin" selected>POST /api/v1/verifications/nin - NIN Verification (Simulated/Live)</option>
                                <option value="bvn_verify" data-method="POST" data-path="/api/v1/verifications/bvn">POST /api/v1/verifications/bvn - BVN Verification (Simulated/Live)</option>
                            </optgroup>
                            <optgroup label="Value Added Services (VTU)">
                                <option value="vtu_airtime" data-method="POST" data-path="/api/v1/vtu/airtime">POST /api/v1/vtu/airtime - Airtime Purchase</option>
                            </optgroup>
                            <option value="custom" data-method="POST" data-path="/api/v1/">Custom Endpoint...</option>
                        </select>

                        <!-- Method & Path Inputs -->
                        <div class="input-group">
                            <select id="httpMethod" class="form-select text-white bg-dark border-white-10 font-weight-bold" style="max-width: 110px;">
                                <option value="GET">GET</option>
                                <option value="POST" selected>POST</option>
                                <option value="PUT">PUT</option>
                                <option value="PATCH">PATCH</option>
                                <option value="DELETE">DELETE</option>
                            </select>
                            <input type="text" id="endpointPath" class="form-control text-white bg-dark border-white-10 font-monospace text-xs" value="/api/v1/verifications/nin">
                        </div>
                    </div>

                    <!-- 3. Headers Preview -->
                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <label class="form-label text-white-50 text-xs fw-semibold mb-0">3. HTTP Headers</label>
                            <span class="text-white-50 text-3xs">Automatically formatted</span>
                        </div>
                        <div class="p-2.5 rounded-12 bg-dark bg-opacity-75 border border-white-10 font-monospace text-3xs text-white-50">
                            <div><strong class="text-info">Authorization:</strong> Bearer <span id="headerTokenPreview" class="text-warning">nx_••••••••••••••</span></div>
                            <div><strong class="text-info">Content-Type:</strong> application/json</div>
                            <div><strong class="text-info">Accept:</strong> application/json</div>
                            <div><strong class="text-info">X-Client-Agent:</strong> Fuwa-Developer-Sandbox/v1</div>
                        </div>
                    </div>

                    <!-- 4. Request Body JSON Editor -->
                    <div class="mb-3" id="payloadSection">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <label class="form-label text-white-50 text-xs fw-semibold mb-0">4. Request Payload (JSON)</label>
                            <div class="d-flex gap-2">
                                <button type="button" class="btn btn-link text-info p-0 text-3xs text-decoration-none" onclick="loadSamplePayload()">
                                    <i class="fa-solid fa-wand-magic-sparkles me-0.5"></i> Load Sample
                                </button>
                                <button type="button" class="btn btn-link text-white-50 p-0 text-3xs text-decoration-none" onclick="formatJsonEditor()">
                                    <i class="fa-solid fa-code me-0.5"></i> Format
                                </button>
                            </div>
                        </div>
                        <textarea id="jsonPayloadEditor" rows="7" class="form-control font-monospace text-xs text-white bg-dark border-white-10 rounded-12" style="tab-size: 2;" placeholder='{"number": "12345678901", "firstname": "John", "lastname": "Doe"}'></textarea>
                    </div>

                    <!-- 5. Mode Switch & Submission -->
                    <div class="p-3 rounded-16 border border-white-10 bg-dark mb-4">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <div>
                                <strong class="text-white text-xs d-block">Sandbox Simulation Mode</strong>
                                <span class="text-white-50 text-3xs">Safe testnet environment. Simulates responses without charging your real wallet balance.</span>
                            </div>
                            <div class="form-check form-switch m-0 ms-3">
                                <input class="form-check-input" type="checkbox" role="switch" id="simulationModeToggle" checked onchange="onSimulationToggle()">
                            </div>
                        </div>
                        <div id="modeWarning" class="alert alert-warning py-1.5 px-3 mb-0 rounded-10 text-3xs d-none">
                            <i class="fa-solid fa-triangle-exclamation me-1"></i> Live Mode active: Real API dispatch enabled. Requires valid full API key and wallet balance.
                        </div>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" id="sendRequestBtn" class="btn btn-primary rounded-pill px-4 py-2 font-weight-bold flex-grow-1">
                            <i class="fa-solid fa-paper-plane me-1.5"></i> Send Request
                        </button>
                        <button type="button" class="btn btn-outline-light rounded-pill px-3 py-2 text-xs" onclick="validateCurrentKey()" title="Run Quick Key Diagnostic Only">
                            <i class="fa-solid fa-stethoscope me-1"></i> Verify Key
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Right Column: Live Response & Diagnostics Inspector -->
        <div class="col-xl-6 col-lg-6 col-12">
            <div class="glass-card rounded-20 border border-white-10 p-4 h-100 d-flex flex-column" style="background: rgba(15, 23, 42, 0.65);">
                <!-- Response Header Toolbar -->
                <div class="d-flex align-items-center justify-content-between pb-3 mb-3 border-bottom border-white-10 flex-wrap gap-2">
                    <div class="d-flex align-items-center gap-2">
                        <h5 class="text-white font-weight-bold mb-0">
                            <i class="fa-solid fa-terminal text-info me-2"></i>Response Inspector
                        </h5>
                    </div>
                    <div class="d-flex align-items-center gap-2" id="responseStatusBadges">
                        <span id="responseStatusBadge" class="badge bg-secondary text-white-50 rounded-pill px-2.5 py-1 text-2xs font-monospace">Ready</span>
                        <span id="responseLatencyBadge" class="badge bg-dark border border-white-10 text-white-50 rounded-pill px-2.5 py-1 text-2xs font-monospace">-- ms</span>
                        <span id="responseModeBadge" class="badge bg-info bg-opacity-25 text-info rounded-pill px-2 py-1 text-3xs">Sandbox</span>
                    </div>
                </div>

                <!-- Tabs: JSON Response | Diagnostics | Code Snippets -->
                <ul class="nav nav-pills mb-3 gap-2 bg-dark p-1 rounded-14 border border-white-10" id="responseTabs" role="tablist">
                    <li class="nav-item flex-grow-1" role="presentation">
                        <button class="nav-link active rounded-10 text-xs py-1.5 w-100 text-center" id="tab-body" data-bs-toggle="pill" data-bs-target="#panel-body" type="button" role="tab">
                            <i class="fa-solid fa-code me-1"></i> JSON Response
                        </button>
                    </li>
                    <li class="nav-item flex-grow-1" role="presentation">
                        <button class="nav-link rounded-10 text-xs py-1.5 w-100 text-center" id="tab-diag" data-bs-toggle="pill" data-bs-target="#panel-diag" type="button" role="tab">
                            <i class="fa-solid fa-stethoscope me-1"></i> Key Diagnostics
                        </button>
                    </li>
                    <li class="nav-item flex-grow-1" role="presentation">
                        <button class="nav-link rounded-10 text-xs py-1.5 w-100 text-center" id="tab-code" data-bs-toggle="pill" data-bs-target="#panel-code" type="button" role="tab">
                            <i class="fa-solid fa-laptop-code me-1"></i> Snippets
                        </button>
                    </li>
                </ul>

                <!-- Tab Panes -->
                <div class="tab-content flex-grow-1 d-flex flex-column" id="responseTabContent">
                    <!-- 1. JSON Response Pane -->
                    <div class="tab-pane fade show active flex-grow-1 d-flex flex-column" id="panel-body" role="tabpanel">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="text-white-50 text-3xs">HTTP Payload Body</span>
                            <button class="btn btn-sm btn-outline-light rounded-pill px-2.5 py-0.5 text-3xs" onclick="copyResponseBody()">
                                <i class="fa-regular fa-copy me-1"></i> Copy Response
                            </button>
                        </div>
                        <div class="position-relative flex-grow-1">
                            <pre id="responseJsonPre" class="p-3 rounded-14 font-monospace text-xs text-white mb-0 h-100" style="background: rgba(3, 7, 18, 0.95); border: 1px solid rgba(255,255,255,0.08); max-height: 460px; overflow: auto; min-height: 320px;">{
  "notice": "Select an endpoint, choose your API key, and click 'Send Request' to execute a sandbox simulation."
}</pre>
                        </div>
                    </div>

                    <!-- 2. Diagnostics Pane -->
                    <div class="tab-pane fade" id="panel-diag" role="tabpanel">
                        <div class="p-3 rounded-16 bg-dark border border-white-10 mb-3">
                            <h6 class="text-white text-xs fw-bold mb-2">
                                <i class="fa-solid fa-shield-heart text-info me-1.5"></i>API Key Health Diagnostics
                            </h6>
                            <div id="diagnosticsContent" class="text-xs text-white-50">
                                <p class="mb-2">No verification performed yet. Click <strong>Verify Key</strong> or <strong>Send Request</strong> to check your credentials.</p>
                            </div>
                        </div>

                        <div class="p-3 rounded-16 bg-dark bg-opacity-50 border border-white-5">
                            <h6 class="text-white text-xs fw-bold mb-2">Diagnostic Guidelines:</h6>
                            <ul class="text-white-50 text-3xs ps-3 mb-0" style="line-height: 1.7;">
                                <li><strong class="text-success">200 OK:</strong> API Key is valid and authenticated. Request succeeded.</li>
                                <li><strong class="text-danger">401 Unauthorized:</strong> Invalid, expired, or revoked API key. Ensure token starts with <code>nx_</code>.</li>
                                <li><strong class="text-warning">402 Payment Required:</strong> Wallet balance is below minimum threshold (₦{{ number_format($minBalance, 2) }}).</li>
                                <li><strong class="text-warning">403 Forbidden:</strong> Developer account API access is pending or not approved.</li>
                                <li><strong class="text-info">422 Unprocessable:</strong> Missing or incorrectly formatted fields (e.g. 11-digit NIN).</li>
                            </ul>
                        </div>
                    </div>

                    <!-- 3. Code Snippets Pane -->
                    <div class="tab-pane fade" id="panel-code" role="tabpanel">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="text-white-50 text-3xs">Integration Code Snippet</span>
                            <div class="d-flex gap-2">
                                <button class="btn btn-sm btn-outline-info rounded-pill px-2 py-0.5 text-3xs" onclick="setSnippetLang('curl')">cURL</button>
                                <button class="btn btn-sm btn-outline-info rounded-pill px-2 py-0.5 text-3xs" onclick="setSnippetLang('js')">JS (fetch)</button>
                                <button class="btn btn-sm btn-outline-info rounded-pill px-2 py-0.5 text-3xs" onclick="setSnippetLang('python')">Python</button>
                                <button class="btn btn-sm btn-outline-info rounded-pill px-2 py-0.5 text-3xs" onclick="setSnippetLang('php')">PHP</button>
                                <button class="btn btn-sm btn-outline-light rounded-pill px-2 py-0.5 text-3xs ms-2" onclick="copySnippet()">Copy</button>
                            </div>
                        </div>
                        <pre id="codeSnippetPre" class="p-3 rounded-14 font-monospace text-xs text-white mb-0" style="background: rgba(3, 7, 18, 0.95); border: 1px solid rgba(255,255,255,0.08); max-height: 420px; overflow: auto; min-height: 280px;"></pre>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
const SAMPLES = {
    me: {
        method: 'GET',
        path: '/api/v1/me',
        payload: null
    },
    nin_verify: {
        method: 'POST',
        path: '/api/v1/verifications/nin',
        payload: {
            number: '12345678901',
            firstname: 'John',
            lastname: 'Doe',
            dob: '1992-05-18',
            mode: 'nin'
        }
    },
    bvn_verify: {
        method: 'POST',
        path: '/api/v1/verifications/bvn',
        payload: {
            number: '22123456789',
            firstname: 'Jane',
            lastname: 'Doe',
            dob: '1995-11-20',
            tier: 'basic'
        }
    },
    legal_catalog: {
        method: 'GET',
        path: '/api/v1/legal/catalog',
        payload: null
    },
    vtu_airtime: {
        method: 'POST',
        path: '/api/v1/vtu/airtime',
        payload: {
            network: 'MTN',
            amount: 500,
            phone: '08012345678'
        }
    }
};

let currentSnippetLang = 'curl';

document.addEventListener('DOMContentLoaded', function () {
    const presetKey = "{{ $preselectedKey ?: '' }}";
    if (presetKey) {
        document.getElementById('keyModeCustom').checked = true;
        toggleKeyMode('custom');
    }

    onPresetEndpointChange();
    updateHeaderPreview();
    updateSnippet();
});

function toggleKeyMode(mode) {
    if (mode === 'saved') {
        document.getElementById('savedKeyGroup').classList.remove('d-none');
        document.getElementById('customKeyGroup').classList.add('d-none');
    } else {
        document.getElementById('savedKeyGroup').classList.add('d-none');
        document.getElementById('customKeyGroup').classList.remove('d-none');
    }
    updateHeaderPreview();
    updateSnippet();
}

function onTokenSelectChange() {
    updateHeaderPreview();
    updateSnippet();
}

function updateHeaderPreview() {
    const isCustom = document.getElementById('keyModeCustom').checked;
    const previewSpan = document.getElementById('headerTokenPreview');
    if (isCustom) {
        const customVal = document.getElementById('customApiKeyInput').value.trim();
        previewSpan.textContent = customVal || 'nx_YOUR_SECRET_TOKEN';
    } else {
        const select = document.getElementById('tokenIdSelect');
        const selectedOpt = select.options[select.selectedIndex];
        const lastFour = selectedOpt ? selectedOpt.getAttribute('data-lastfour') : '----';
        previewSpan.textContent = `nx_••••••••••••••••${lastFour}`;
    }
}

function onPresetEndpointChange() {
    const presetKey = document.getElementById('endpointPreset').value;
    const sample = SAMPLES[presetKey];
    if (sample) {
        document.getElementById('httpMethod').value = sample.method;
        document.getElementById('endpointPath').value = sample.path;
        if (sample.payload) {
            document.getElementById('jsonPayloadEditor').value = JSON.stringify(sample.payload, null, 2);
            document.getElementById('payloadSection').classList.remove('d-none');
        } else {
            document.getElementById('jsonPayloadEditor').value = '';
            document.getElementById('payloadSection').classList.add('d-none');
        }
    } else {
        document.getElementById('payloadSection').classList.remove('d-none');
    }
    updateSnippet();
}

function loadSamplePayload() {
    const presetKey = document.getElementById('endpointPreset').value;
    const sample = SAMPLES[presetKey];
    if (sample && sample.payload) {
        document.getElementById('jsonPayloadEditor').value = JSON.stringify(sample.payload, null, 2);
    } else {
        document.getElementById('jsonPayloadEditor').value = JSON.stringify({
            number: '12345678901',
            firstname: 'John',
            lastname: 'Doe'
        }, null, 2);
    }
    updateSnippet();
}

function formatJsonEditor() {
    const editor = document.getElementById('jsonPayloadEditor');
    const val = editor.value.trim();
    if (!val) return;
    try {
        const parsed = JSON.parse(val);
        editor.value = JSON.stringify(parsed, null, 2);
    } catch (e) {
        alert('Invalid JSON: ' + e.message);
    }
}

function onSimulationToggle() {
    const isSim = document.getElementById('simulationModeToggle').checked;
    const warn = document.getElementById('modeWarning');
    const badge = document.getElementById('responseModeBadge');
    if (!isSim) {
        warn.classList.remove('d-none');
        badge.textContent = 'Live API';
        badge.className = 'badge bg-warning bg-opacity-25 text-warning rounded-pill px-2 py-1 text-3xs';
    } else {
        warn.classList.add('d-none');
        badge.textContent = 'Sandbox';
        badge.className = 'badge bg-info bg-opacity-25 text-info rounded-pill px-2 py-1 text-3xs';
    }
}

async function validateCurrentKey() {
    const isCustom = document.getElementById('keyModeCustom').checked;
    const keyBadge = document.getElementById('keyValidationBadge');
    const diagBox = document.getElementById('diagnosticsContent');

    keyBadge.textContent = 'Checking…';
    keyBadge.className = 'badge bg-warning text-dark text-3xs rounded-pill';

    const payload = {
        _token: '{{ csrf_token() }}',
    };

    if (isCustom) {
        payload.api_key = document.getElementById('customApiKeyInput').value.trim();
        if (!payload.api_key) {
            keyBadge.textContent = 'Key Required';
            keyBadge.className = 'badge bg-danger text-white text-3xs rounded-pill';
            alert('Please enter an API key to verify.');
            return;
        }
    } else {
        payload.token_id = document.getElementById('tokenIdSelect').value;
    }

    try {
        const res = await fetch('{{ route("developer.sandbox.validate_key") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify(payload)
        });

        const data = await res.json();

        if (data.status && data.valid) {
            keyBadge.textContent = '● Valid & Active';
            keyBadge.className = 'badge bg-success text-white text-3xs rounded-pill';

            diagBox.innerHTML = `
                <div class="alert alert-success py-2 px-3 mb-2 rounded-10 text-xs">
                    <i class="fa-solid fa-circle-check me-1"></i> <strong>Key is Authenticated & Active</strong>
                </div>
                <div class="text-white-50 small" style="line-height: 1.6;">
                    <div>Token Name: <strong class="text-white">${data.token.name}</strong> (••••${data.token.last_four})</div>
                    <div>Account: <span class="text-info">${data.account.email}</span></div>
                    <div>Rate Limit: <strong>${data.token.rate_limit} req/min</strong></div>
                    <div>Wallet Balance: <strong>₦${Number(data.account.wallet_balance).toFixed(2)}</strong> (Min: ₦${Number(data.account.min_balance_required).toFixed(2)})</div>
                    <div>API Access: <span class="badge bg-success bg-opacity-25 text-success rounded-pill px-2 py-0.5">${data.account.api_access_status}</span></div>
                </div>
            `;
        } else {
            keyBadge.textContent = '● Issues Found';
            keyBadge.className = 'badge bg-danger text-white text-3xs rounded-pill';

            const issuesList = (data.diagnostics?.issues || [data.message || 'Key verification failed.']).map(i => `<li>${i}</li>`).join('');
            diagBox.innerHTML = `
                <div class="alert alert-danger py-2 px-3 mb-2 rounded-10 text-xs">
                    <i class="fa-solid fa-circle-xmark me-1"></i> <strong>Authentication Issue Detected</strong>
                </div>
                <ul class="text-danger small mb-0 ps-3">
                    ${issuesList}
                </ul>
            `;
        }

        // Switch to diagnostics tab to show report
        const triggerEl = document.getElementById('tab-diag');
        if (triggerEl) {
            bootstrap.Tab.getOrCreateInstance(triggerEl).show();
        }
    } catch (e) {
        keyBadge.textContent = 'Error';
        keyBadge.className = 'badge bg-danger text-white text-3xs rounded-pill';
        diagBox.innerHTML = `<div class="alert alert-danger py-2 px-3 mb-0 text-xs">Diagnostic error: ${e.message}</div>`;
    }
}

async function runSandboxTest() {
    const btn = document.getElementById('sendRequestBtn');
    const isCustom = document.getElementById('keyModeCustom').checked;
    const isSim = document.getElementById('simulationModeToggle').checked;
    const originalText = btn.innerHTML;

    btn.disabled = true;
    btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin me-1.5"></i> Sending…';

    const payload = {
        _token: '{{ csrf_token() }}',
        method: document.getElementById('httpMethod').value,
        endpoint: document.getElementById('endpointPath').value.trim(),
        simulation_mode: isSim,
    };

    if (isCustom) {
        payload.api_key = document.getElementById('customApiKeyInput').value.trim();
    } else {
        payload.token_id = document.getElementById('tokenIdSelect').value;
    }

    const jsonVal = document.getElementById('jsonPayloadEditor').value.trim();
    if (jsonVal && payload.method !== 'GET') {
        try {
            payload.payload = JSON.parse(jsonVal);
        } catch (e) {
            alert('JSON Payload syntax error: ' + e.message);
            btn.disabled = false;
            btn.innerHTML = originalText;
            return;
        }
    }

    try {
        const res = await fetch('{{ route("developer.sandbox.execute") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify(payload)
        });

        const data = await res.json();

        // Update Status Badge
        const statusBadge = document.getElementById('responseStatusBadge');
        const latencyBadge = document.getElementById('responseLatencyBadge');
        const statusCode = data.http_status || res.status;

        statusBadge.textContent = `${statusCode} ${getStatusText(statusCode)}`;
        if (statusCode >= 200 && statusCode < 300) {
            statusBadge.className = 'badge bg-success text-white rounded-pill px-2.5 py-1 text-2xs font-monospace';
        } else if (statusCode === 401 || statusCode === 403) {
            statusBadge.className = 'badge bg-danger text-white rounded-pill px-2.5 py-1 text-2xs font-monospace';
        } else if (statusCode === 402) {
            statusBadge.className = 'badge bg-warning text-dark rounded-pill px-2.5 py-1 text-2xs font-monospace';
        } else {
            statusBadge.className = 'badge bg-warning text-dark rounded-pill px-2.5 py-1 text-2xs font-monospace';
        }

        latencyBadge.textContent = `⚡ ${data.latency_ms || 0} ms`;

        // Update JSON response
        const jsonPre = document.getElementById('responseJsonPre');
        const bodyContent = data.response_body !== undefined ? data.response_body : data;
        jsonPre.textContent = JSON.stringify(bodyContent, null, 2);

        // Update diagnostics
        const diagBox = document.getElementById('diagnosticsContent');
        if (data.diagnostics) {
            const diag = data.diagnostics;
            const isSuccess = statusCode >= 200 && statusCode < 300;
            diagBox.innerHTML = `
                <div class="alert ${isSuccess ? 'alert-success' : 'alert-danger'} py-2 px-3 mb-2 rounded-10 text-xs">
                    <i class="fa-solid ${isSuccess ? 'fa-circle-check' : 'fa-circle-xmark'} me-1"></i>
                    <strong>${diag.verdict || 'Verdict Reported'}</strong>
                </div>
                <div class="text-white-50 text-xs" style="line-height: 1.6;">
                    ${diag.reason ? `<div class="mb-1 text-danger"><strong>Reason:</strong> ${diag.reason}</div>` : ''}
                    ${diag.suggestion ? `<div class="mb-1 text-info"><strong>Suggestion:</strong> ${diag.suggestion}</div>` : ''}
                    ${diag.wallet_charged ? `<div>Wallet Charged: <span class="text-white font-monospace">${diag.wallet_charged}</span></div>` : ''}
                    ${diag.token_last_four ? `<div>Token Used: <span class="text-warning font-monospace">••••${diag.token_last_four}</span></div>` : ''}
                </div>
            `;
        }

        // Switch to Body Tab
        const triggerEl = document.getElementById('tab-body');
        if (triggerEl) {
            bootstrap.Tab.getOrCreateInstance(triggerEl).show();
        }

        updateSnippet();
    } catch (e) {
        document.getElementById('responseJsonPre').textContent = JSON.stringify({
            error: 'Network or client exception',
            message: e.message
        }, null, 2);
    } finally {
        btn.disabled = false;
        btn.innerHTML = originalText;
    }
}

function getStatusText(code) {
    const map = {
        200: 'OK',
        201: 'Created',
        400: 'Bad Request',
        401: 'Unauthorized',
        402: 'Payment Required',
        403: 'Forbidden',
        404: 'Not Found',
        422: 'Unprocessable Entity',
        429: 'Too Many Requests',
        500: 'Server Error'
    };
    return map[code] || '';
}

function copyResponseBody() {
    const text = document.getElementById('responseJsonPre').textContent;
    navigator.clipboard.writeText(text).then(() => {
        alert('Response copied to clipboard!');
    });
}

function setSnippetLang(lang) {
    currentSnippetLang = lang;
    updateSnippet();
}

function updateSnippet() {
    const method = document.getElementById('httpMethod').value;
    const path = document.getElementById('endpointPath').value.trim();
    const isCustom = document.getElementById('keyModeCustom').checked;
    let tokenStr = 'nx_YOUR_SECRET_TOKEN';

    if (isCustom) {
        tokenStr = document.getElementById('customApiKeyInput').value.trim() || 'nx_YOUR_SECRET_TOKEN';
    } else {
        const select = document.getElementById('tokenIdSelect');
        const selectedOpt = select.options[select.selectedIndex];
        const lastFour = selectedOpt ? selectedOpt.getAttribute('data-lastfour') : 'XXXX';
        tokenStr = `nx_YOUR_SECRET_KEY_${lastFour}`;
    }

    const host = window.location.origin;
    const fullUrl = `${host}${path}`;
    const payload = document.getElementById('jsonPayloadEditor').value.trim();
    const snippetPre = document.getElementById('codeSnippetPre');

    if (currentSnippetLang === 'curl') {
        let code = `curl -X ${method} "${fullUrl}" \\\n  -H "Authorization: Bearer ${tokenStr}" \\\n  -H "Content-Type: application/json" \\\n  -H "Accept: application/json"`;
        if (method !== 'GET' && payload) {
            code += ` \\\n  -d '${payload.replace(/'/g, "\\'")}'`;
        }
        snippetPre.textContent = code;
    } else if (currentSnippetLang === 'js') {
        let code = `const response = await fetch("${fullUrl}", {\n  method: "${method}",\n  headers: {\n    "Authorization": "Bearer ${tokenStr}",\n    "Content-Type": "application/json",\n    "Accept": "application/json"\n  }`;
        if (method !== 'GET' && payload) {
            code += `,\n  body: JSON.stringify(${payload})`;
        }
        code += `\n});\n\nconst data = await response.json();\nconsole.log(data);`;
        snippetPre.textContent = code;
    } else if (currentSnippetLang === 'python') {
        let code = `import requests\n\nurl = "${fullUrl}"\nheaders = {\n    "Authorization": "Bearer ${tokenStr}",\n    "Content-Type": "application/json",\n    "Accept": "application/json"\n}\n`;
        if (method !== 'GET' && payload) {
            code += `payload = ${payload}\nresponse = requests.${method.toLowerCase()}(url, json=payload, headers=headers)\n`;
        } else {
            code += `response = requests.${method.toLowerCase()}(url, headers=headers)\n`;
        }
        code += `print(response.status_code)\nprint(response.json())`;
        snippetPre.textContent = code;
    } else if (currentSnippetLang === 'php') {
        let code = `<?php\n\n$client = new \\GuzzleHttp\\Client();\n$response = $client->request('${method}', '${fullUrl}', [\n  'headers' => [\n    'Authorization' => 'Bearer ${tokenStr}',\n    'Accept' => 'application/json',\n    'Content-Type' => 'application/json',\n  ]`;
        if (method !== 'GET' && payload) {
            code += `,\n  'body' => '${payload}'`;
        }
        code += `\n]);\n\necho $response->getBody();`;
        snippetPre.textContent = code;
    }
}

function copySnippet() {
    const text = document.getElementById('codeSnippetPre').textContent;
    navigator.clipboard.writeText(text).then(() => {
        alert('Code snippet copied to clipboard!');
    });
}
</script>

<style>
.text-3xs {
    font-size: 0.68rem;
}
.rounded-10 {
    border-radius: 10px;
}
.rounded-12 {
    border-radius: 12px;
}
.rounded-14 {
    border-radius: 14px;
}
.rounded-16 {
    border-radius: 16px;
}
.rounded-20 {
    border-radius: 20px;
}
.glass-card {
    background: rgba(255, 255, 255, 0.02);
    backdrop-filter: blur(16px);
}
#responseTabs .nav-link {
    color: #94a3b8;
    background: transparent;
    transition: all 0.2s ease;
}
#responseTabs .nav-link.active {
    color: #fff;
    background: var(--clr-primary, #3b82f6);
}
</style>
@endsection
