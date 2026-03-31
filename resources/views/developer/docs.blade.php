@extends('layouts.nexus')

@section('title', 'Developer API Documentation & Integration Guide')

@section('content')
<div class="row mb-4">
    <div class="col-12">
        <div class="d-flex align-items-center justify-content-between">
            <div>
                <h3 class="text-white mb-1 font-weight-bold"><i class="fa-solid fa-book text-primary mr-2"></i> Integration Tutorial</h3>
                <p class="text-white-50 mb-0">Learn how to integrate our platform services into your application.</p>
            </div>
            <a href="{{ route('developer.portal') }}" class="btn btn-outline-light rounded-pill px-4">
                <i class="fa fa-arrow-left mr-2"></i>Back to Portal
            </a>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-3 d-none d-lg-block">
        <div class="card border-0 rounded-4 p-4 sticky-top" style="top: 100px; background: rgba(255,255,255,0.02); border: 1px solid rgba(255,255,255,0.07) !important;">
            <h6 class="text-white fw-bold mb-3">Contents</h6>
            <nav class="nav flex-column doc-nav">
                <a class="nav-link text-white-50 small px-0 py-1" href="#auth">1. Authentication Flow</a>
                <a class="nav-link text-white-50 small px-0 py-1" href="#endpoints">2. API Endpoints</a>
                <a class="nav-link text-white-50 small px-0 py-1" href="#examples">3. Code Examples</a>
                <a class="nav-link text-white-50 small px-0 py-1" href="#sdks">4. SDK Availability</a>
                <a class="nav-link text-white-50 small px-0 py-1" href="#best-practices">5. Best Practices</a>
            </nav>
        </div>
    </div>

    <div class="col-lg-9 col-12">
        <div class="card border-0 rounded-4 p-4 mb-4" style="background: rgba(255,255,255,0.02); border: 1px solid rgba(255,255,255,0.07) !important;">
            <div class="prose text-white">
                <p class="text-white-50 lead mb-5">
                    Welcome to the Developer API. This document provides a comprehensive guide to integrating with our platform services, including authentication flows, code examples, and SDK details.
                </p>

                <section id="auth" class="mb-5">
                    <h4 class="fw-bold border-bottom border-white-10 pb-2 mb-4">1. Authentication Flow</h4>
                    <p class="text-white-50">
                        Our API utilizes a <strong>wallet-based authentication system</strong> via Bearer tokens (OAuth 2.0 standard). To access any API endpoint, developers must maintain a minimum balance in their platform wallet.
                    </p>

                    <h5 class="fw-bold mt-4 mb-3 h6">1.1 Generating an API Token</h5>
                    <p class="text-white-50">You can generate a token via the <a href="{{ route('developer.portal') }}" class="text-primary">developer dashboard</a> or using the <code>/auth/token</code> endpoint.</p>
                    
                    <div class="code-block position-relative mb-4">
                        <button class="btn btn-sm btn-outline-light rounded-pill px-3 position-absolute" style="right: 10px; top: 10px;" onclick="copyBlock('curlAuth')">Copy</button>
                        <pre id="curlAuth" class="p-3 rounded-3 small text-white font-monospace" style="background: rgba(0,0,0,0.25); border: 1px solid rgba(255,255,255,0.08); overflow:auto;">curl -X POST https://api.fuwa.ng/api/v1/auth/token \
  -H "Content-Type: application/json" \
  -d '{
    "email": "developer@example.com",
    "password": "your_password",
    "device_name": "server_1"
  }'</pre>
                    </div>

                    <h5 class="fw-bold mt-4 mb-3 h6">1.2 Using the Token</h5>
                    <p class="text-white-50">Pass the token in the <code>Authorization</code> header for all subsequent requests:</p>
                    <pre class="p-3 rounded-3 small text-white font-monospace mb-4" style="background: rgba(0,0,0,0.25); border: 1px solid rgba(255,255,255,0.08);">Authorization: Bearer &lt;your_token&gt;</pre>

                    <h5 class="fw-bold mt-4 mb-3 h6">1.3 Wallet Balance Enforcement</h5>
                    <p class="text-white-50">If your wallet balance falls below the minimum required threshold, your API key will be automatically suspended. The API will respond with HTTP <code>402 Payment Required</code> and the following message:</p>
                    
                    <div class="code-block position-relative">
                        <pre class="p-3 rounded-3 small text-white font-monospace" style="background: rgba(0,0,0,0.25); border: 1px solid rgba(255,255,255,0.08); overflow:auto;">{
  "status": false,
  "message": "fund not sufficient",
  "error": "fund not sufficient"
}</pre>
                    </div>
                </section>

                <section id="endpoints" class="mb-5">
                    <h4 class="fw-bold border-bottom border-white-10 pb-2 mb-4">2. API Endpoints Reference</h4>
                    <p class="text-white-50 mb-4">Our API follows strict RESTful design principles and enforces HTTPS.</p>

                    <h5 class="fw-bold mb-3 h6">2.1 Identity Verification</h5>
                    <ul class="text-white-50 mb-4">
                        <li class="mb-2"><span class="badge badge-primary mr-2">POST</span> <code>/api/v1/verifications/nin</code>: Verify National Identity Number.</li>
                        <li><span class="badge badge-primary mr-2">POST</span> <code>/api/v1/verifications/bvn</code>: Verify Bank Verification Number.</li>
                    </ul>

                    <h5 class="fw-bold mb-3 h6">2.2 VTU & Bill Payments</h5>
                    <ul class="text-white-50 mb-4">
                        <li class="mb-2"><span class="badge badge-primary mr-2">POST</span> <code>/api/v1/vtu/airtime</code>: Top up mobile airtime.</li>
                        <li class="mb-2"><span class="badge badge-primary mr-2">POST</span> <code>/api/v1/vtu/data</code>: Purchase data bundles.</li>
                        <li class="mb-2"><span class="badge badge-primary mr-2">POST</span> <code>/api/v1/vtu/cable</code>: Subscribe to Cable TV (DSTV, GOTV, etc.).</li>
                        <li><span class="badge badge-primary mr-2">POST</span> <code>/api/v1/vtu/electricity</code>: Pay prepaid or postpaid electricity bills.</li>
                    </ul>

                    <div class="alert" style="background: rgba(59, 130, 246, 0.1); border: 1px solid rgba(59, 130, 246, 0.2);">
                        <i class="fa fa-info-circle text-primary mr-2"></i> For detailed request/response schemas, please view our <a href="{{ route('developer.openapi.v1') }}" target="_blank" class="text-primary font-weight-bold">OpenAPI 3.0 specification</a>.
                    </div>
                </section>

                <section id="examples" class="mb-5">
                    <h4 class="fw-bold border-bottom border-white-10 pb-2 mb-4">3. Code Examples</h4>
                    
                    <ul class="nav nav-pills mb-3 custom-tabs" id="codeTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active rounded-pill px-4" id="python-tab" data-toggle="pill" data-target="#python" type="button" role="tab">Python</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link rounded-pill px-4" id="node-tab" data-toggle="pill" data-target="#node" type="button" role="tab">Node.js</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link rounded-pill px-4" id="php-tab" data-toggle="pill" data-target="#php" type="button" role="tab">PHP</button>
                        </li>
                    </ul>

                    <div class="tab-content" id="codeTabsContent">
                        <div class="tab-pane fade show active" id="python" role="tabpanel">
                            <div class="code-block position-relative">
                                <button class="btn btn-sm btn-outline-light rounded-pill px-3 position-absolute" style="right: 10px; top: 10px;" onclick="copyBlock('pythonCode')">Copy</button>
                                <pre id="pythonCode" class="p-3 rounded-3 small text-white font-monospace" style="background: rgba(0,0,0,0.25); border: 1px solid rgba(255,255,255,0.08); overflow:auto;">import requests

url = "https://api.fuwa.ng/api/v1/vtu/airtime"
headers = {
    "Authorization": "Bearer YOUR_API_TOKEN",
    "Content-Type": "application/json"
}
payload = {
    "network": "MTN",
    "amount": 1000,
    "phone": "08012345678"
}

response = requests.post(url, json=payload, headers=headers)
print(response.json())</pre>
                            </div>
                        </div>

                        <div class="tab-pane fade" id="node" role="tabpanel">
                            <div class="code-block position-relative">
                                <button class="btn btn-sm btn-outline-light rounded-pill px-3 position-absolute" style="right: 10px; top: 10px;" onclick="copyBlock('nodeCode')">Copy</button>
                                <pre id="nodeCode" class="p-3 rounded-3 small text-white font-monospace" style="background: rgba(0,0,0,0.25); border: 1px solid rgba(255,255,255,0.08); overflow:auto;">const axios = require('axios');

async function buyAirtime() {
  try {
    const response = await axios.post('https://api.fuwa.ng/api/v1/vtu/airtime', {
      network: 'MTN',
      amount: 1000,
      phone: '08012345678'
    }, {
      headers: {
        'Authorization': 'Bearer YOUR_API_TOKEN'
      }
    });
    console.log(response.data);
  } catch (error) {
    if (error.response && error.response.status === 402) {
      console.error("Error: fund not sufficient");
    }
  }
}
buyAirtime();</pre>
                            </div>
                        </div>

                        <div class="tab-pane fade" id="php" role="tabpanel">
                            <div class="code-block position-relative">
                                <button class="btn btn-sm btn-outline-light rounded-pill px-3 position-absolute" style="right: 10px; top: 10px;" onclick="copyBlock('phpCode')">Copy</button>
                                <pre id="phpCode" class="p-3 rounded-3 small text-white font-monospace" style="background: rgba(0,0,0,0.25); border: 1px solid rgba(255,255,255,0.08); overflow:auto;">&lt;?php
$ch = curl_init();

curl_setopt_array($ch, [
    CURLOPT_URL => "https://api.fuwa.ng/api/v1/vtu/airtime",
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => json_encode([
        "network" => "MTN",
        "amount" => 1000,
        "phone" => "08012345678"
    ]),
    CURLOPT_HTTPHEADER => [
        "Authorization: Bearer YOUR_API_TOKEN",
        "Content-Type: application/json"
    ],
]);

$response = curl_exec($ch);
curl_close($ch);
echo $response;
?&gt;</pre>
                            </div>
                        </div>
                    </div>
                </section>

                <section id="sdks" class="mb-5">
                    <h4 class="fw-bold border-bottom border-white-10 pb-2 mb-4">4. SDK Availability</h4>
                    <p class="text-white-50 mb-4">We provide official SDKs to accelerate your integration. Each SDK automatically handles token management, retries, and rate limit tracking.</p>

                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <div class="p-3 rounded-3" style="background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.1);">
                                <h6 class="fw-bold text-white"><i class="fa-brands fa-php text-primary mr-2"></i> PHP SDK</h6>
                                <p class="small text-white-50 mb-2">Available via Composer</p>
                                <code class="small text-white">composer require fuwang/sdk-php</code>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <div class="p-3 rounded-3" style="background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.1);">
                                <h6 class="fw-bold text-white"><i class="fa-brands fa-node-js text-success mr-2"></i> Node.js SDK</h6>
                                <p class="small text-white-50 mb-2">Available via NPM</p>
                                <code class="small text-white">npm i @fuwang/sdk-node</code>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <div class="p-3 rounded-3" style="background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.1);">
                                <h6 class="fw-bold text-white"><i class="fa-brands fa-python text-warning mr-2"></i> Python SDK</h6>
                                <p class="small text-white-50 mb-2">Available via Pip</p>
                                <code class="small text-white">pip install fuwang-sdk</code>
                            </div>
                        </div>
                    </div>
                </section>

                <section id="best-practices">
                    <h4 class="fw-bold border-bottom border-white-10 pb-2 mb-4">5. Best Practices & Error Handling</h4>
                    
                    <div class="mb-4">
                        <h6 class="fw-bold text-white">Rate Limiting</h6>
                        <p class="text-white-50 small">Our API allows 60 requests per minute by default. If exceeded, a <code>429 Too Many Requests</code> status is returned.</p>
                    </div>

                    <div class="mb-4">
                        <h6 class="fw-bold text-white">HTTPS Enforcement</h6>
                        <p class="text-white-50 small">All traffic must be sent over HTTPS. Non-HTTPS requests are rejected automatically with a <code>403</code> status.</p>
                    </div>

                    <div>
                        <h6 class="fw-bold text-white mb-3">HTTP Status Codes</h6>
                        <div class="table-responsive">
                            <table class="table table-borderless table-sm small text-white-50 mb-0">
                                <tbody>
                                    <tr><td width="80"><span class="badge badge-success">200 OK</span></td><td>Success</td></tr>
                                    <tr><td><span class="badge badge-warning text-dark">400</span></td><td>Bad Request (Validation error)</td></tr>
                                    <tr><td><span class="badge badge-danger">401</span></td><td>Unauthorized (Invalid or missing token)</td></tr>
                                    <tr><td><span class="badge badge-danger">402</span></td><td>Payment Required ("fund not sufficient")</td></tr>
                                    <tr><td><span class="badge badge-warning text-dark">429</span></td><td>Too Many Requests (Rate limit exceeded)</td></tr>
                                    <tr><td><span class="badge badge-danger">500</span></td><td>Internal Server Error (Platform error)</td></tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </section>

            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .doc-nav .nav-link { transition: all 0.2s ease; border-left: 2px solid transparent; }
    .doc-nav .nav-link:hover, .doc-nav .nav-link.active { color: var(--clr-primary) !important; border-left-color: var(--clr-primary); background: rgba(59, 130, 246, 0.05); }
    .custom-tabs .nav-link { color: rgba(255,255,255,0.5); border: 1px solid transparent; }
    .custom-tabs .nav-link.active { background: rgba(59, 130, 246, 0.1); color: #fff; border-color: rgba(59, 130, 246, 0.3); }
    .custom-tabs .nav-link:hover:not(.active) { color: #fff; background: rgba(255,255,255,0.05); }
    .prose code { color: #fca5a5; background: rgba(239, 68, 68, 0.1); padding: 2px 6px; border-radius: 4px; font-size: 0.85em; }
</style>
@endpush

@push('scripts')
<script>
async function copyBlock(id) {
    const el = document.getElementById(id);
    const value = el.innerText || el.textContent || '';
    try {
        await navigator.clipboard.writeText(value);
        Swal.fire({ icon: 'success', title: 'Copied', text: 'Code copied to clipboard.', background: '#141826', color: '#fff', timer: 1200, showConfirmButton: false });
    } catch (e) {
        Swal.fire({ icon: 'error', title: 'Error', text: 'Copy failed.', background: '#141826', color: '#fff' });
    }
}
</script>
@endpush