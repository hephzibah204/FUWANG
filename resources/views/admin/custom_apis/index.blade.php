@extends('layouts.nexus')

@section('title', 'Custom External APIs | Admin ' . config('app.name'))

@section('content')
<div class="dashboard-wrapper fade-in">
    <div class="row align-items-center mb-4">
        <div class="col-md-8">
            <h1 class="h3 font-weight-bold mb-1">Custom APIs</h1>
            <p class="text-muted">Manage dynamic downstream API integrations.</p>
        </div>
        <div class="col-md-4 text-md-right mt-3 mt-md-0">
            <div class="btn-group mr-2">
                <button class="btn btn-outline-light dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <i class="fa-solid fa-wand-magic-sparkles mr-2"></i> Add from Template
                </button>
                <div class="dropdown-menu dropdown-menu-right">
                    <form method="POST" action="{{ route('admin.custom_apis.templates.store', 'dataverify_nin') }}">
                        @csrf
                        <button class="dropdown-item" type="submit">DataVerify (NIN)</button>
                    </form>
                    <form method="POST" action="{{ route('admin.custom_apis.templates.store', 'verifyme_nin') }}">
                        @csrf
                        <button class="dropdown-item" type="submit">VerifyMe (NIN)</button>
                    </form>
                    <form method="POST" action="{{ route('admin.custom_apis.templates.store', 'youverify_nin') }}">
                        @csrf
                        <button class="dropdown-item" type="submit">Youverify (NIN)</button>
                    </form>
                    <form method="POST" action="{{ route('admin.custom_apis.templates.store', 'dojah_nin') }}">
                        @csrf
                        <button class="dropdown-item" type="submit">Dojah (NIN)</button>
                    </form>
                    <form method="POST" action="{{ route('admin.custom_apis.templates.store', 'smileid_nin') }}">
                        @csrf
                        <button class="dropdown-item" type="submit">Smile ID (NIN)</button>
                    </form>
                    <form method="POST" action="{{ route('admin.custom_apis.templates.store', 'vuvaa_nin') }}">
                        @csrf
                        <button class="dropdown-item" type="submit">VUVAA (NIN)</button>
                    </form>
                    <div class="dropdown-divider"></div>
                    <form method="POST" action="{{ route('admin.custom_apis.templates.store', 'generic_sms') }}">
                        @csrf
                        <button class="dropdown-item" type="submit">Generic SMS Gateway</button>
                    </form>
                </div>
            </div>
            <button class="btn btn-primary" data-toggle="modal" data-target="#addApiModal">
                <i class="fa-solid fa-plus mr-2"></i> Add Custom API
            </button>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="card border-0 mb-3" style="background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.08);">
        <div class="card-body">
            <div class="d-flex align-items-center justify-content-between">
                <h6 class="text-white-50 mb-0">Provider Setup Tips</h6>
                <button class="btn btn-sm btn-outline-secondary" type="button" data-toggle="collapse" data-target="#apiHelp">Examples</button>
            </div>
            <div id="apiHelp" class="collapse mt-3 small text-white-50">
                <p class="mb-2">Use Custom Headers for authentication. Examples:</p>
                <pre class="p-3 rounded" style="background: rgba(0,0,0,0.3); border: 1px solid rgba(255,255,255,0.08); white-space: pre-wrap;">VerifyMe:
{"Authorization":"Bearer &lt;SECRET_KEY&gt;","Content-Type":"application/json"}

Youverify:
{"Authorization":"Bearer &lt;SECRET_KEY&gt;","X-API-KEY":"&lt;API_KEY&gt;","Content-Type":"application/json"}

Dojah:
{"AppId":"&lt;APP_ID&gt;","AppKey":"&lt;APP_KEY&gt;","Authorization":"Bearer &lt;SECRET_OR_API_KEY&gt;","Content-Type":"application/json"}

Smile ID:
{"X-Partner-ID":"&lt;PARTNER_ID&gt;","Authorization":"Bearer &lt;SECRET_KEY&gt;","Content-Type":"application/json"}</pre>
                <p class="mb-2">Per “Verification Type”, use Meta to tweak requests (optional):</p>
                <pre class="p-3 rounded" style="background: rgba(0,0,0,0.3); border: 1px solid rgba(255,255,255,0.08); white-space: pre-wrap;">{
  "path_suffix": "nin", 
  "query": { "mode": "basic" }, 
  "payload": { "include_image": true },
  "headers": { "X-Product-Tier": "premium" }
}</pre>
                <p class="mb-0">Endpoint should be the base URL. Use path_suffix in the type Meta to append resource names (e.g., "nin", "nin/phone", "vnin").</p>
            </div>
        </div>
    </div>

    <div class="admin-panel mt-4">
        <div class="table-responsive">
            <table class="table admin-table mb-0">
                <thead>
                    <tr>
                        <th>API Name</th>
                        <th>Service Type</th>
                        <th>Identifier</th>
                        <th>Status</th>
                        <th>Priority</th>
                        <th>Price (₦)</th>
                        <th>Endpoint URL</th>
                        <th class="text-right pr-4">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @php $currentType = null; @endphp
                    @forelse($apis as $api)
                        @if($currentType !== $api->service_type)
                            @php $currentType = $api->service_type; @endphp
                            <tr class="bg-dark-light">
                                <td colspan="8" class="py-2 text-primary font-weight-bold" style="background: rgba(var(--clr-primary-rgb), 0.1);">
                                    <i class="fa-solid fa-layer-group mr-2"></i> {{ strtoupper(str_replace('_', ' ', $api->service_type)) }}
                                </td>
                            </tr>
                        @endif
                        <tr>
                            <td class="align-middle font-weight-bold text-white pl-4">{{ $api->name }}</td>
                            <td class="align-middle">
                                <span class="badge badge-secondary">{{ strtoupper(str_replace('_', ' ', $api->service_type)) }}</span>
                            </td>
                            <td class="align-middle">
                                <span class="badge badge-dark">{{ $api->provider_identifier ?: '—' }}</span>
                            </td>
                             <td class="align-middle">
                                @if($api->status)
                                    <span class="badge badge-success"><i class="fa-solid fa-plug mr-1"></i> Connected</span>
                                @else
                                    <span class="badge badge-danger"><i class="fa-solid fa-plug-circle-xmark mr-1"></i> Disconnected</span>
                                @endif
                            </td>
                            <td class="align-middle">
                                <span class="badge badge-info">{{ $api->priority }}</span>
                            </td>
                            <td class="align-middle font-weight-bold">₦{{ number_format($api->price, 2) }}</td>
                            <td class="align-middle text-muted small"><code class="text-primary">{{ Str::limit($api->endpoint, 60) }}</code></td>
                            <td class="align-middle text-right pr-4">
                                <button class="btn btn-sm btn-outline-primary" data-toggle="modal" data-target="#editApiModal{{ $api->id }}">Manage</button>
                                <form action="{{ route('admin.custom_apis.destroy', $api->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this API configuration?');">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger"><i class="fa fa-trash"></i></button>
                                </form>
                            </td>
                        </tr>

                        <!-- Edit API Modal -->
                        <div class="modal fade" id="editApiModal{{ $api->id }}" tabindex="-1" role="dialog" aria-hidden="true">
                            <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
                                <div class="modal-content">
                                    <div class="modal-header border-bottom-0 pb-0">
                                        <h5 class="modal-title font-weight-bold text-white">Edit {{ $api->name }}</h5>
                                        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                    </div>
                                    <form action="{{ route('admin.custom_apis.update', $api->id) }}" method="POST">
                                        @csrf
                                        @method('PUT')
                                        <div class="modal-body">
                                            <div class="row">
                                                <div class="col-md-6 form-group mb-3">
                                                    <label class="font-weight-bold">API Name</label>
                                                    <input type="text" name="name" class="form-control" value="{{ $api->name }}" placeholder="e.g. Flutterwave, Paystack" required>
                                                </div>
                                                <div class="col-md-6 form-group mb-3">
                                                    <label class="font-weight-bold">Status</label>
                                                    <div class="custom-control custom-switch custom-switch-lg pt-1">
                                                        <input type="checkbox" class="custom-control-input" id="apiStatus{{ $api->id }}" name="status" value="1" {{ $api->status ? 'checked' : '' }}>
                                                        <label class="custom-control-label font-weight-bold text-white pl-4" for="apiStatus{{ $api->id }}">Active Provider</label>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-md-6 form-group mb-3">
                                                    <label class="font-weight-bold">Provider Identifier</label>
                                                    <input type="text" name="provider_identifier" class="form-control" value="{{ $api->provider_identifier }}" placeholder="dataverify, verifyme, youverify">
                                                </div>
                                                <div class="col-md-6 form-group mb-3">
                                                    <label class="font-weight-bold">Priority <span class="text-danger">*</span></label>
                                                    <input type="number" name="priority" class="form-control" value="{{ $api->priority }}" min="1" required>
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-md-6 form-group mb-3">
                                                    <label class="font-weight-bold">Service Type</label>
                                                    <select name="service_type" class="form-control text-white border-secondary" style="background: rgba(255,255,255,0.05);" required>
                                                        <option value="" disabled>Select Target Service</option>
                                                        <optgroup label="Identity Verification (VerifyMe/YouVerify/IdentityPay)">
                                                            <option value="nin_verification" {{ $api->service_type == 'nin_verification' ? 'selected' : '' }}>NIN Verification</option>
                                                            <option value="bvn_verification" {{ $api->service_type == 'bvn_verification' ? 'selected' : '' }}>BVN Verification</option>
                                                            <option value="drivers_license" {{ $api->service_type == 'drivers_license' ? 'selected' : '' }}>Drivers License</option>
                                                            <option value="voters_card_verification" {{ $api->service_type == 'voters_card_verification' ? 'selected' : '' }}>Voters Card</option>
                                                            <option value="passport_verification" {{ $api->service_type == 'passport_verification' ? 'selected' : '' }}>International Passport</option>
                                                            <option value="cac_verification" {{ $api->service_type == 'cac_verification' ? 'selected' : '' }}>CAC (Business Registration)</option>
                                                            <option value="tin_verification" {{ $api->service_type == 'tin_verification' ? 'selected' : '' }}>TIN Verification</option>
                                                            <option value="biometric_verification" {{ $api->service_type == 'biometric_verification' ? 'selected' : '' }}>Biometric Identity</option>
                                                            <option value="nin_face_verification" {{ $api->service_type == 'nin_face_verification' ? 'selected' : '' }}>NIN with Face</option>
                                                            <option value="address_verification" {{ $api->service_type == 'address_verification' ? 'selected' : '' }}>Address Verification</option>
                                                        </optgroup>
                                                        <optgroup label="VTU & Bills (VTUPass/MyIdentityPass)">
                                                            <option value="vtu_airtime" {{ $api->service_type == 'vtu_airtime' ? 'selected' : '' }}>VTU Airtime</option>
                                                            <option value="vtu_data" {{ $api->service_type == 'vtu_data' ? 'selected' : '' }}>VTU Data</option>
                                                            <option value="vtu_cable_tv" {{ $api->service_type == 'vtu_cable_tv' ? 'selected' : '' }}>VTU Cable TV</option>
                                                            <option value="vtu_electricity" {{ $api->service_type == 'vtu_electricity' ? 'selected' : '' }}>VTU Electricity</option>
                                                            <option value="vtu_internet" {{ $api->service_type == 'vtu_internet' ? 'selected' : '' }}>VTU Internet</option>
                                                            <option value="vtu_betting" {{ $api->service_type == 'vtu_betting' ? 'selected' : '' }}>VTU Betting</option>
                                                            <option value="vtu_epin" {{ $api->service_type == 'vtu_epin' ? 'selected' : '' }}>VTU ePINs</option>
                                                            <option value="vtu_airtime_to_cash" {{ $api->service_type == 'vtu_airtime_to_cash' ? 'selected' : '' }}>VTU Airtime to Cash</option>
                                                            <option value="electricity_bills" {{ $api->service_type == 'electricity_bills' ? 'selected' : '' }}>Electricity Bills (Legacy)</option>
                                                            <option value="cable_tv" {{ $api->service_type == 'cable_tv' ? 'selected' : '' }}>Cable TV Subscription (Legacy)</option>
                                                        </optgroup>
                                                        <optgroup label="Other Services">
                                                            <option value="education_waec" {{ $api->service_type == 'education_waec' ? 'selected' : '' }}>WAEC Result Checker</option>
                                                            <option value="education_waec_registration" {{ $api->service_type == 'education_waec_registration' ? 'selected' : '' }}>WAEC Registration</option>
                                                            <option value="education_neco" {{ $api->service_type == 'education_neco' ? 'selected' : '' }}>NECO Result Checker</option>
                                                            <option value="education_nabteb" {{ $api->service_type == 'education_nabteb' ? 'selected' : '' }}>NABTEB Result Checker</option>
                                                            <option value="education_jamb" {{ $api->service_type == 'education_jamb' ? 'selected' : '' }}>JAMB Profile/PIN</option>
                                                            <option value="gemini_ai" {{ $api->service_type == 'gemini_ai' ? 'selected' : '' }}>AI Services (Gemini API)</option>
                                                            <option value="insurance_motor" {{ $api->service_type == 'insurance_motor' ? 'selected' : '' }}>Insurance (Motor)</option>
                                                            <option value="stamp_duty" {{ $api->service_type == 'stamp_duty' ? 'selected' : '' }}>Stamp Duty</option>
                                                            <option value="plate_number_verification" {{ $api->service_type == 'plate_number_verification' ? 'selected' : '' }}>Plate Number</option>
                                                            <option value="sms_gateway" {{ $api->service_type == 'sms_gateway' ? 'selected' : '' }}>SMS Gateway</option>
                                                            <option value="payment" {{ $api->service_type == 'payment' ? 'selected' : '' }}>Payment Gateway</option>
                                                        </optgroup>
                                                    </select>
                                                </div>
                                                <div class="col-md-6 form-group mb-3">
                                                    <label class="font-weight-bold">Base Endpoint URL</label>
                                                    <input type="url" name="endpoint" class="form-control" value="{{ $api->endpoint }}" placeholder="https://api.provider.com/v1" required>
                                                </div>
                                            </div>
                                             <div class="row">
                                                <div class="col-md-4 form-group mb-3">
                                                    <label class="font-weight-bold">Public / API Key</label>
                                                    <input type="text" name="api_key" class="form-control" value="{{ $api->api_key }}">
                                                </div>
                                                <div class="col-md-4 form-group mb-3">
                                                    <label class="font-weight-bold">Secret Key</label>
                                                    <input type="password" name="secret_key" class="form-control" value="{{ $api->secret_key }}">
                                                </div>
                                                <div class="col-md-4 form-group mb-3">
                                                    <label class="font-weight-bold">Price (₦) <span class="text-danger">*</span></label>
                                                    <input type="number" name="price" class="form-control" value="{{ $api->price }}" step="0.01" required>
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-md-3 form-group mb-3">
                                                    <label class="font-weight-bold">Commission Type</label>
                                                    @php $cfg = is_array($api->config) ? $api->config : []; @endphp
                                                    <select name="fee_type" class="form-control">
                                                        <option value="flat" {{ (($cfg['fee_type'] ?? $cfg['commission_type'] ?? 'flat') === 'flat') ? 'selected' : '' }}>Flat</option>
                                                        <option value="percent" {{ (($cfg['fee_type'] ?? $cfg['commission_type'] ?? '') === 'percent') ? 'selected' : '' }}>Percent</option>
                                                    </select>
                                                </div>
                                                <div class="col-md-3 form-group mb-3">
                                                    <label class="font-weight-bold">Commission Value</label>
                                                    <input type="number" name="fee_value" class="form-control" step="0.01" value="{{ $cfg['fee_value'] ?? $cfg['commission_value'] ?? '' }}" placeholder="0.00">
                                                </div>
                                                <div class="col-md-3 form-group mb-3">
                                                    <label class="font-weight-bold">Min Amount</label>
                                                    <input type="number" name="min_amount" class="form-control" step="0.01" value="{{ $cfg['min_amount'] ?? '' }}" placeholder="0.00">
                                                </div>
                                                <div class="col-md-3 form-group mb-3">
                                                    <label class="font-weight-bold">Max Amount</label>
                                                    <input type="number" name="max_amount" class="form-control" step="0.01" value="{{ $cfg['max_amount'] ?? '' }}" placeholder="0.00">
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-md-6 form-group mb-3">
                                                    <label class="font-weight-bold">Service Code (Provider)</label>
                                                    <input type="text" name="service_code" class="form-control" value="{{ $cfg['service_code'] ?? '' }}" placeholder="e.g. mtn, ikeja_electric, dstv">
                                                </div>
                                                <div class="col-md-6 form-group mb-3">
                                                    <label class="font-weight-bold">Supported Modes (NIN only)</label>
                                                    @php $modes = is_array($api->supported_modes) ? $api->supported_modes : []; @endphp
                                                    <select name="supported_modes[]" class="form-control text-white border-secondary" style="background: rgba(255,255,255,0.05);" multiple>
                                                        <option value="nin" {{ in_array('nin', $modes) ? 'selected' : '' }}>By NIN</option>
                                                        <option value="phone" {{ in_array('phone', $modes) ? 'selected' : '' }}>By Phone</option>
                                                        <option value="demographic" {{ in_array('demographic', $modes) ? 'selected' : '' }}>Demographic</option>
                                                        <option value="tracking" {{ in_array('tracking', $modes) ? 'selected' : '' }}>Tracking ID</option>
                                                        <option value="selfie" {{ in_array('selfie', $modes) ? 'selected' : '' }}>Face/Selfie</option>
                                                        <option value="vnin" {{ in_array('vnin', $modes) ? 'selected' : '' }}>vNIN</option>
                                                        <option value="share_code" {{ in_array('share_code', $modes) ? 'selected' : '' }}>Share Code</option>
                                                    </select>
                                                    <small class="text-muted">Hold Ctrl/Cmd to select multiple.</small>
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-md-4 form-group mb-3">
                                                    <label class="font-weight-bold">Timeout (seconds)</label>
                                                    <input type="number" name="timeout_seconds" class="form-control" value="{{ $api->timeout_seconds ?? 60 }}" min="1" max="300">
                                                </div>
                                                <div class="col-md-4 form-group mb-3">
                                                    <label class="font-weight-bold">Retry Count</label>
                                                    <input type="number" name="retry_count" class="form-control" value="{{ $api->retry_count ?? 0 }}" min="0" max="10">
                                                </div>
                                                <div class="col-md-4 form-group mb-3">
                                                    <label class="font-weight-bold">Retry Delay (ms)</label>
                                                    <input type="number" name="retry_delay_ms" class="form-control" value="{{ $api->retry_delay_ms ?? 0 }}" min="0" max="10000">
                                                </div>
                                            </div>
                                            <div class="form-group mb-3">
                                                <label class="font-weight-bold">Custom Headers (JSON format)</label>
                                                <textarea name="headers" class="form-control" rows="4" placeholder='{"Authorization": "Bearer TOKEN", "Content-Type": "application/json"}'>{{ !empty($api->headers) ? json_encode($api->headers, JSON_PRETTY_PRINT) : '' }}</textarea>
                                            </div>
                                            <div class="form-group mb-3">
                                                <label class="font-weight-bold">Config (JSON format)</label>
                                                <textarea name="config" class="form-control" rows="3" placeholder='{"partner_id":"", "app_id":"", "app_key":""}'>{{ !empty($api->config) ? json_encode($api->config, JSON_PRETTY_PRINT) : '' }}</textarea>
                                            </div>
                                            <div class="border-top pt-3 mt-4" style="border-color: rgba(255,255,255,0.08) !important;">
                                                <div class="d-flex align-items-center justify-content-between mb-2">
                                                    <h6 class="text-white font-weight-bold mb-0">Verification Types & Pricing</h6>
                                                </div>
                                                <div class="table-responsive">
                                                    <table class="table table-borderless text-white small mb-2">
                                                        <thead>
                                                            <tr class="text-white-50 border-bottom border-white-10">
                                                                <th>Key</th>
                                                                <th>Label</th>
                                                                <th>Price (₦)</th>
                                                                <th>Status</th>
                                                                <th class="text-right">Action</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            @forelse($api->verificationTypes as $t)
                                                                <tr class="border-bottom border-white-10">
                                                                    <td class="font-monospace text-white-50">{{ $t->type_key }}</td>
                                                                    <td>{{ $t->label }}</td>
                                                                    <td class="font-weight-bold">₦{{ number_format($t->price, 2) }}</td>
                                                                    <td>
                                                                        @if($t->status)
                                                                            <span class="badge badge-success">Active</span>
                                                                        @else
                                                                            <span class="badge badge-secondary">Disabled</span>
                                                                        @endif
                                                                    </td>
                                                                    <td class="text-right">
                                                                        <form action="{{ route('admin.custom_apis.types.destroy', [$api->id, $t->id]) }}" method="POST" class="d-inline" onsubmit="return confirm('Remove this verification type?');">
                                                                            @csrf
                                                                            @method('DELETE')
                                                                            <button class="btn btn-sm btn-outline-danger"><i class="fa fa-trash"></i></button>
                                                                        </form>
                                                                    </td>
                                                                </tr>
                                                            @empty
                                                                <tr><td colspan="5" class="text-center text-white-50 py-3">No types configured for this provider.</td></tr>
                                                            @endforelse
                                                        </tbody>
                                                    </table>
                                                </div>

                                                <form action="{{ route('admin.custom_apis.types.store', $api->id) }}" method="POST" class="mt-3">
                                                    @csrf
                                                    <div class="row">
                                                        <div class="col-md-3 form-group mb-3">
                                                            <label class="font-weight-bold">Type Key</label>
                                                            <input type="text" name="type_key" class="form-control" placeholder="e.g. basic" required>
                                                        </div>
                                                        <div class="col-md-4 form-group mb-3">
                                                            <label class="font-weight-bold">Label</label>
                                                            <input type="text" name="label" class="form-control" placeholder="e.g. Basic Slip" required>
                                                        </div>
                                                        <div class="col-md-3 form-group mb-3">
                                                            <label class="font-weight-bold">Price (₦)</label>
                                                            <input type="number" name="price" class="form-control" step="0.01" min="0" required>
                                                        </div>
                                                        <div class="col-md-2 form-group mb-3">
                                                            <label class="font-weight-bold">Active</label>
                                                            <div class="custom-control custom-switch pt-2">
                                                                <input type="checkbox" class="custom-control-input" id="typeStatus{{ $api->id }}" name="status" value="1" checked>
                                                                <label class="custom-control-label" for="typeStatus{{ $api->id }}"></label>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="row">
                                                        <div class="col-md-3 form-group mb-3">
                                                            <label class="font-weight-bold">Sort Order</label>
                                                            <input type="number" name="sort_order" class="form-control" value="0" min="0" max="10000">
                                                        </div>
                                                        <div class="col-md-9 form-group mb-3">
                                                            <label class="font-weight-bold">Meta (JSON, optional)</label>
                                                            <textarea name="meta" class="form-control" rows="3" placeholder='{"query":{"type":"basic"},"payload":{},"headers":{},"path_suffix":""}'></textarea>
                                                        </div>
                                                    </div>
                                                    <div class="d-flex justify-content-end">
                                                        <button type="submit" class="btn btn-outline-primary">Add Type</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                        <div class="modal-footer border-top-0 pt-0">
                                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                                            <button type="submit" class="btn btn-primary">Save API Configuration</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-5 text-muted">
                                <i class="fa-solid fa-code-merge fa-3x mb-3"></i>
                                <h5>No custom APIs configured</h5>
                                <p class="mb-0">Add your dynamic downstream providers here.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Add API Modal -->
<div class="modal fade" id="addApiModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header border-bottom-0 pb-0">
                <h5 class="modal-title font-weight-bold text-white">Add External API Provider</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="{{ route('admin.custom_apis.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 form-group mb-3">
                            <label class="font-weight-bold">API Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control" placeholder="e.g. Flutterwave, Termii" required>
                        </div>
                        <div class="col-md-6 form-group mb-3">
                            <label class="font-weight-bold">Status</label>
                            <div class="custom-control custom-switch custom-switch-lg pt-1">
                                <input type="checkbox" class="custom-control-input" id="newApiStatus" name="status" value="1" checked>
                                <label class="custom-control-label font-weight-bold text-white pl-4" for="newApiStatus">Active Provider</label>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4 form-group mb-3">
                            <label class="font-weight-bold">Provider Identifier</label>
                            <input type="text" name="provider_identifier" class="form-control" placeholder="dataverify, verifyme, youverify">
                        </div>
                        <div class="col-md-6 form-group mb-3">
                            <label class="font-weight-bold">Service Type <span class="text-danger">*</span></label>
                            <select name="service_type" class="form-control text-white border-secondary" style="background: rgba(255,255,255,0.05);" required>
                                <option value="" disabled selected>Select Target Service</option>
                                <option value="nin_verification">NIN Verification</option>
                                <option value="bvn_verification">BVN Verification</option>
                                <option value="vtu_airtime">VTU Airtime</option>
                                <option value="vtu_data">VTU Data</option>
                                <option value="vtu_cable_tv">VTU Cable TV</option>
                                <option value="vtu_electricity">VTU Electricity</option>
                                <option value="vtu_internet">VTU Internet</option>
                                <option value="vtu_betting">VTU Betting</option>
                                <option value="vtu_epin">VTU ePINs</option>
                                <option value="vtu_airtime_to_cash">VTU Airtime to Cash</option>
                                <option value="education_waec">Education (WAEC Result Checker)</option>
                                <option value="education_waec_registration">Education (WAEC Registration)</option>
                                <option value="education_neco">Education (NECO Result Checker)</option>
                                <option value="education_nabteb">Education (NABTEB Result Checker)</option>
                                <option value="education_jamb">Education (JAMB Profile/PIN)</option>
                                <option value="gemini_ai">AI Services (Gemini API)</option>
                                <option value="insurance_motor">Insurance (Third Party Motor)</option>
                                <option value="drivers_license">Drivers License Verification</option>
                                <option value="biometric_verification">Biometric Identity Verification</option>
                                <option value="stamp_duty">Stamp Duty Verification</option>
                                <option value="plate_number_verification">Plate Number Verification</option>
                                <option value="cac_verification">CAC (Business Registration)</option>
                                <option value="tin_verification">TIN Verification</option>
                                <option value="nin_face_verification">NIN with Face Verification</option>
                                <option value="credit_bureau_advance">Credit Bureau Advance</option>
                                <option value="passport_verification">International Passport Verification</option>
                                <option value="bvn_nin_phone_verification">Combined BVN/NIN/Phone</option>
                                <option value="voters_card_verification">Voters Card Verification</option>
                                <option value="bvn_matching">BVN Identity Matching</option>
                                <option value="address_verification">Address Verification</option>
                                <option value="sms_gateway">SMS Gateway</option>
                                <option value="payment">Payment Gateway</option>
                            </select>
                        </div>
                        <div class="col-md-2 form-group mb-3">
                            <label class="font-weight-bold">Priority <span class="text-danger">*</span></label>
                            <input type="number" name="priority" class="form-control" value="10" min="1" required>
                        </div>
                        <div class="col-md-12 form-group mb-3">
                            <label class="font-weight-bold">Base Endpoint URL <span class="text-danger">*</span></label>
                            <input type="url" name="endpoint" class="form-control" placeholder="https://api.provider.com/v1" required>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4 form-group mb-3">
                            <label class="font-weight-bold">Public Key</label>
                            <input type="text" name="api_key" class="form-control" placeholder="pk_live_...">
                        </div>
                        <div class="col-md-4 form-group mb-3">
                            <label class="font-weight-bold">Secret Key</label>
                            <input type="password" name="secret_key" class="form-control" placeholder="sk_live_...">
                        </div>
                        <div class="col-md-4 form-group mb-3">
                            <label class="font-weight-bold">Price (₦) <span class="text-danger">*</span></label>
                            <input type="number" name="price" class="form-control" placeholder="0.00" step="0.01" required>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-3 form-group mb-3">
                            <label class="font-weight-bold">Commission Type</label>
                            <select name="fee_type" class="form-control">
                                <option value="flat">Flat</option>
                                <option value="percent">Percent</option>
                            </select>
                        </div>
                        <div class="col-md-3 form-group mb-3">
                            <label class="font-weight-bold">Commission Value</label>
                            <input type="number" name="fee_value" class="form-control" step="0.01" placeholder="0.00">
                        </div>
                        <div class="col-md-3 form-group mb-3">
                            <label class="font-weight-bold">Min Amount</label>
                            <input type="number" name="min_amount" class="form-control" step="0.01" placeholder="0.00">
                        </div>
                        <div class="col-md-3 form-group mb-3">
                            <label class="font-weight-bold">Max Amount</label>
                            <input type="number" name="max_amount" class="form-control" step="0.01" placeholder="0.00">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 form-group mb-3">
                            <label class="font-weight-bold">Service Code (Provider)</label>
                            <input type="text" name="service_code" class="form-control" placeholder="e.g. mtn, ikeja_electric, dstv">
                        </div>
                        <div class="col-md-6 form-group mb-3">
                            <label class="font-weight-bold">Credit Amount Path (Airtime to Cash)</label>
                            <input type="text" name="credit_amount_path" class="form-control" placeholder="e.g. data.amount">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4 form-group mb-3">
                            <label class="font-weight-bold">Timeout (seconds)</label>
                            <input type="number" name="timeout_seconds" class="form-control" value="60" min="1" max="300">
                        </div>
                        <div class="col-md-4 form-group mb-3">
                            <label class="font-weight-bold">Retry Count</label>
                            <input type="number" name="retry_count" class="form-control" value="0" min="0" max="10">
                        </div>
                        <div class="col-md-4 form-group mb-3">
                            <label class="font-weight-bold">Retry Delay (ms)</label>
                            <input type="number" name="retry_delay_ms" class="form-control" value="0" min="0" max="10000">
                        </div>
                    </div>
                    <div class="form-group mb-3">
                        <label class="font-weight-bold">Custom Headers (JSON Format)</label>
                        <textarea name="headers" class="form-control" rows="4" placeholder='{"Authorization": "Bearer TOKEN", "Content-Type": "application/json"}'></textarea>
                    </div>
                    <div class="form-group mb-3">
                        <label class="font-weight-bold">Config (JSON Format)</label>
                        <textarea name="config" class="form-control" rows="3" placeholder='{"partner_id":"", "app_id":"", "app_key":""}'></textarea>
                    </div>
                </div>
                <!-- ... -->
                <div class="modal-footer border-top-0 pt-0">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add API</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('styles')
<style>
.modal select.form-control,
.modal .custom-select,
.modal select.form-control.text-white {
    background-color: rgba(255,255,255,0.05) !important;
    color: #fff !important;
    border-color: rgba(255,255,255,0.18) !important;
    color-scheme: dark;
}

.modal select.form-control option,
.modal select.form-control optgroup {
    background-color: #0f172a;
    color: #fff;
}

.modal select.form-control:focus,
.modal .custom-select:focus {
    border-color: var(--clr-primary) !important;
    box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.15);
}

/* Custom Switch LG Override for Fuwa.NG */
.custom-switch-lg .custom-control-label::before {
    left: 0;
    width: 2.25rem;
    height: 1.25rem;
    border-radius: 1.25rem;
    background-color: rgba(255,255,255,0.1);
    border: none;
}
.custom-switch-lg .custom-control-label::after {
    top: calc(0.25rem + 2px);
    left: calc(0.25rem - 4px);
    width: calc(1.25rem - 4px);
    height: calc(1.25rem - 4px);
    border-radius: 50%;
    background-color: #fff;
    transition: transform 0.15s ease-in-out;
}
.custom-switch-lg .custom-control-input:checked ~ .custom-control-label::before {
    background-color: var(--clr-primary);
}
.custom-switch-lg .custom-control-input:checked ~ .custom-control-label::after {
    transform: translateX(1rem);
}
</style>
@endpush
@endsection
