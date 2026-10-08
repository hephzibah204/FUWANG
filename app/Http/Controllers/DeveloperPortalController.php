<?php

namespace App\Http\Controllers;

use App\Models\ApiToken;
use App\Models\SystemSetting;
use App\Models\User;
use App\Services\DeveloperApi\DeveloperApiCatalog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DeveloperPortalController extends Controller
{
    public function index(Request $request, DeveloperApiCatalog $catalog)
    {
        $user = Auth::user();
        $tokens = ApiToken::query()
            ->where('user_id', $user->id)
            ->latest()
            ->get();

        $baseUrl = $request->getSchemeAndHttpHost() . '/api/v1';
        $endpoints = $catalog->enabled();
        $developerPricing = [
            'developer_api_nin_price' => (float) SystemSetting::get('developer_api_nin_price', 100),
            'developer_api_bvn_basic_price' => (float) SystemSetting::get('developer_api_bvn_basic_price', 100),
            'developer_api_bvn_premium_price' => (float) SystemSetting::get('developer_api_bvn_premium_price', 500),
        ];

        return view('developer.portal', compact('tokens', 'baseUrl', 'endpoints', 'developerPricing'));
    }
    public function createToken(Request $request)
    {
        $request->validate([
            'name' => ['nullable', 'string', 'max:120'],
        ]);

        $user = $request->user();
        $plain = rtrim(strtr(base64_encode(random_bytes(32)), '+/', '-_'), '=');
        $hash = hash('sha256', $plain);
        $lastFour = substr($plain, -4);

        $name = $request->name ?: 'API Key - ' . now()->format('Y-m-d H:i');

        $token = ApiToken::create([
            'user_id' => $user->id,
            'name' => $name,
            'token_hash' => $hash,
            'last_four' => $lastFour,
            'abilities' => ['*'],
            'rate_limit_per_minute' => 60,
        ]);

        return response()->json([
            'status' => true,
            'message' => 'API token created. Copy it now; it will not be shown again.',
            'token' => 'nx_' . $plain,
            'token_id' => $token->id,
        ]);
    }

    public function revokeToken(Request $request, int $id)
    {
        $user = $request->user();
        $token = ApiToken::query()
            ->where('user_id', $user->id)
            ->where('id', $id)
            ->firstOrFail();

        if ($token->revoked_at) {
            return response()->json(['status' => true, 'message' => 'Token already revoked.']);
        }

        $token->forceFill(['revoked_at' => now()])->save();

        return response()->json(['status' => true, 'message' => 'Token revoked.']);
    }

    public function openapiV1()
    {
        $path = public_path('api-docs/openapi.yaml');
        if (!file_exists($path)) {
            abort(404);
        }

        return response()->file($path, [
            'Content-Type' => 'application/yaml; charset=utf-8',
        ]);
    }

    public function postmanV1(Request $request, DeveloperApiCatalog $catalog)
    {
        $endpoints = $catalog->enabled();
        $baseUrl = url('/api/v1');

        $collection = [
            'info' => [
                '_postman_id' => \Illuminate\Support\Str::uuid()->toString(),
                'name' => config('app.name', 'Fuwa.NG') . ' API',
                'description' => 'Dynamic API collection for ' . config('app.name', 'Fuwa.NG') . ' developer integrations.',
                'schema' => 'https://schema.getpostman.com/json/collection/v2.1.0/collection.json',
            ],
            'item' => [],
            'event' => [
                [
                    'listen' => 'prerequest',
                    'script' => [
                        'type' => 'text/javascript',
                        'exec' => ['']
                    ]
                ]
            ],
            'variable' => [
                [
                    'key' => 'baseUrl',
                    'value' => $baseUrl,
                    'type' => 'string'
                ],
                [
                    'key' => 'api_token',
                    'value' => 'nx_YOUR_TOKEN_HERE',
                    'type' => 'string'
                ]
            ]
        ];

        // Group endpoints by group_name
        $grouped = $endpoints->groupBy(fn ($e) => $e->group_name ?: 'Other');

        foreach ($grouped as $groupName => $groupEndpoints) {
            $groupItem = [
                'name' => $groupName,
                'item' => []
            ];

            foreach ($groupEndpoints as $endpoint) {
                $pattern = ltrim((string)$endpoint->path_pattern, '/');
                $path = $pattern;
                if (str_contains($path, '*')) {
                    $path = str_replace('*', ':id', $path);
                }
                
                $relative = str_replace('api/v1/', '', $path);
                $pathSegments = explode('/', $relative);

                $requestItem = [
                    'name' => $endpoint->name,
                    'request' => [
                        'method' => strtoupper((string)$endpoint->method),
                        'header' => [
                            [
                                'key' => 'Authorization',
                                'value' => 'Bearer {{api_token}}',
                                'type' => 'text'
                            ],
                            [
                                'key' => 'Accept',
                                'value' => 'application/json',
                                'type' => 'text'
                            ],
                            [
                                'key' => 'Content-Type',
                                'value' => 'application/json',
                                'type' => 'text'
                            ]
                        ],
                        'url' => [
                            'raw' => '{{baseUrl}}/' . implode('/', $pathSegments),
                            'host' => ['{{baseUrl}}'],
                            'path' => $pathSegments
                        ],
                        'description' => $endpoint->docs_summary ?: ''
                    ]
                ];

                if (in_array($requestItem['request']['method'], ['POST', 'PUT', 'PATCH'])) {
                    $exampleJson = $endpoint->docs_request_example;
                    if ($exampleJson) {
                        $requestItem['request']['body'] = [
                            'mode' => 'raw',
                            'raw' => $exampleJson,
                            'options' => [
                                'raw' => [
                                    'language' => 'json'
                                ]
                            ]
                        ];
                    }
                }

                if (str_contains($path, ':id')) {
                    $requestItem['request']['url']['variable'] = [
                        [
                            'key' => 'id',
                            'value' => '1',
                            'description' => 'The identifier'
                        ]
                    ];
                }

                $groupItem['item'][] = $requestItem;
            }

            $collection['item'][] = $groupItem;
        }

        return response()->json($collection, 200, [
            'Content-Disposition' => 'attachment; filename="fuwa_postman_collection.json"',
        ]);
    }

    public function docs(DeveloperApiCatalog $catalog)
    {
        $docs = [
            'title' => (string) SystemSetting::get('developer_api_docs_title', 'Developer API Documentation & Integration Guide'),
            'intro' => (string) SystemSetting::get('developer_api_docs_intro', 'Welcome to the Developer API. This document provides a practical guide to authentication, endpoint usage, and wallet-backed billing.'),
            'auth' => (string) SystemSetting::get('developer_api_docs_auth', 'Generate a token from the developer dashboard, send it as a Bearer token, and maintain enough wallet balance for billable endpoints.'),
            'best_practices' => (string) SystemSetting::get('developer_api_docs_best_practices', 'Send requests from your backend, protect tokens, track your own request references, and handle 402/429 responses gracefully.'),
            'support' => (string) SystemSetting::get('developer_api_docs_support', 'Need more access or commercial support? Contact the platform team with your use case and website details.'),
        ];
        $endpoints = $catalog->enabled()->groupBy(fn ($endpoint) => $endpoint->group_name ?: 'Other');
        $baseUrl = url('/api/v1');
        $developerPricing = [
            'developer_api_nin_price' => (float) SystemSetting::get('developer_api_nin_price', 100),
            'developer_api_bvn_basic_price' => (float) SystemSetting::get('developer_api_bvn_basic_price', 100),
            'developer_api_bvn_premium_price' => (float) SystemSetting::get('developer_api_bvn_premium_price', 500),
        ];

        return view('developer.docs', compact('docs', 'endpoints', 'baseUrl', 'developerPricing'));
    }

    public function sandbox(Request $request, DeveloperApiCatalog $catalog)
    {
        $user = Auth::user();
        $tokens = ApiToken::query()
            ->where('user_id', $user->id)
            ->latest()
            ->get();

        $baseUrl = url('/api/v1');
        $endpoints = $catalog->enabled();

        $developerPricing = [
            'developer_api_nin_price' => (float) SystemSetting::get('developer_api_nin_price', 100),
            'developer_api_bvn_basic_price' => (float) SystemSetting::get('developer_api_bvn_basic_price', 100),
            'developer_api_bvn_premium_price' => (float) SystemSetting::get('developer_api_bvn_premium_price', 500),
        ];

        $minBalance = (float) SystemSetting::get('api_min_wallet_balance', 100.0);
        $userBalance = (float) ($user->balance->user_balance ?? 0.0);
        $preselectedTokenId = $request->query('token_id');
        $preselectedKey = $request->query('api_key');

        return view('developer.sandbox', compact(
            'tokens',
            'endpoints',
            'baseUrl',
            'developerPricing',
            'minBalance',
            'userBalance',
            'preselectedTokenId',
            'preselectedKey',
            'user'
        ));
    }

    public function validateKey(Request $request)
    {
        $request->validate([
            'api_key' => ['nullable', 'string'],
            'token_id' => ['nullable', 'integer'],
        ]);

        $user = $request->user();
        $apiKey = trim((string) $request->input('api_key', ''));
        $tokenId = $request->input('token_id');
        $token = null;

        if ($apiKey !== '') {
            $plain = str_starts_with($apiKey, 'nx_') ? substr($apiKey, 3) : $apiKey;
            $tokenHash = hash('sha256', $plain);
            $token = ApiToken::where('token_hash', $tokenHash)->first();
        } elseif ($tokenId) {
            $token = ApiToken::where('id', $tokenId)->where('user_id', $user->id)->first();
        }

        if (! $token) {
            return response()->json([
                'status' => false,
                'valid' => false,
                'error_code' => 'key_not_found',
                'message' => 'API Key not found. Please ensure you copied the key correctly including the "nx_" prefix.',
            ], 404);
        }

        $isOwnToken = ($token->user_id === $user->id);
        $owner = $token->user;
        $isRevoked = (bool) $token->revoked_at;
        $isExpired = (bool) ($token->expires_at && $token->expires_at->isPast());
        $minBalance = (float) SystemSetting::get('api_min_wallet_balance', 100.0);
        $userBalance = (float) ($owner?->balance->user_balance ?? 0.0);
        $accessStatus = $owner?->api_access_status ?: 'none';
        $hasSufficientBalance = ($userBalance >= $minBalance);

        $isValid = ! $isRevoked && ! $isExpired && ($accessStatus === 'approved');

        return response()->json([
            'status' => true,
            'valid' => $isValid,
            'token' => [
                'id' => $token->id,
                'name' => $token->name,
                'last_four' => $token->last_four,
                'rate_limit' => $token->rate_limit_per_minute ?? 60,
                'created_at' => $token->created_at?->format('Y-m-d H:i:s'),
                'last_used_at' => $token->last_used_at ? $token->last_used_at->diffForHumans() : 'Never',
                'is_revoked' => $isRevoked,
                'revoked_at' => $token->revoked_at?->format('Y-m-d H:i:s'),
                'is_expired' => $isExpired,
                'is_own_token' => $isOwnToken,
            ],
            'account' => [
                'email' => $owner?->email,
                'api_access_status' => $accessStatus,
                'wallet_balance' => $userBalance,
                'min_balance_required' => $minBalance,
                'has_sufficient_balance' => $hasSufficientBalance,
            ],
            'diagnostics' => [
                'auth_status' => $isValid ? 'HEALTHY' : 'ISSUE_DETECTED',
                'issues' => array_values(array_filter([
                    $isRevoked ? 'Token is marked as revoked.' : null,
                    $isExpired ? 'Token has expired.' : null,
                    $accessStatus !== 'approved' ? "API Access is {$accessStatus}. Only approved accounts can execute live requests." : null,
                    ! $hasSufficientBalance ? 'Wallet balance (₦' . number_format($userBalance, 2) . ') is below the minimum required balance (₦' . number_format($minBalance, 2) . ').' : null,
                ])),
            ],
        ]);
    }

    public function executeSandbox(Request $request, DeveloperApiCatalog $catalog)
    {
        $request->validate([
            'api_key' => ['nullable', 'string'],
            'token_id' => ['nullable', 'integer'],
            'endpoint' => ['required', 'string'],
            'method' => ['required', 'string', 'in:GET,POST,PUT,PATCH,DELETE'],
            'payload' => ['nullable'],
            'simulation_mode' => ['nullable'],
        ]);

        $user = $request->user();
        $apiKey = trim((string) $request->input('api_key', ''));
        $tokenId = $request->input('token_id');
        $endpoint = '/' . ltrim($request->input('endpoint'), '/');
        $method = strtoupper($request->input('method'));
        $simulationMode = filter_var($request->input('simulation_mode', true), FILTER_VALIDATE_BOOLEAN);

        $payload = $request->input('payload');
        if (is_string($payload)) {
            $decoded = json_decode($payload, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                $payload = $decoded;
            }
        }
        if (! is_array($payload)) {
            $payload = [];
        }

        $token = null;
        $plainToken = null;

        if ($apiKey !== '') {
            $plainToken = str_starts_with($apiKey, 'nx_') ? substr($apiKey, 3) : $apiKey;
            $tokenHash = hash('sha256', $plainToken);
            $token = ApiToken::where('token_hash', $tokenHash)->first();
        } elseif ($tokenId) {
            $token = ApiToken::where('id', $tokenId)->where('user_id', $user->id)->first();
        }

        $startTime = hrtime(true);

        // 1. Verify Token
        if (! $token) {
            $latency = round((hrtime(true) - $startTime) / 1e6, 2);
            return response()->json([
                'status' => false,
                'http_status' => 401,
                'latency_ms' => $latency,
                'mode' => $simulationMode ? 'sandbox' : 'live',
                'error' => 'Unauthenticated',
                'message' => 'Invalid or missing API key.',
                'response_body' => [
                    'status' => false,
                    'message' => 'Missing or invalid API token.',
                    'error' => 'unauthenticated',
                ],
                'headers' => [
                    'content-type' => 'application/json',
                    'www-authenticate' => 'Bearer error="invalid_token"',
                ],
                'diagnostics' => [
                    'verdict' => 'FAILED (401 Unauthorized)',
                    'reason' => 'The provided API key could not be authenticated against the platform database.',
                    'suggestion' => 'Check your token string for typos or select an existing active token from your account.',
                ],
            ]);
        }

        if ($token->revoked_at) {
            $latency = round((hrtime(true) - $startTime) / 1e6, 2);
            return response()->json([
                'status' => false,
                'http_status' => 401,
                'latency_ms' => $latency,
                'mode' => $simulationMode ? 'sandbox' : 'live',
                'error' => 'Token Revoked',
                'message' => 'This API token has been revoked and cannot be used.',
                'response_body' => [
                    'status' => false,
                    'message' => 'API token revoked.',
                    'error' => 'token_revoked',
                ],
                'headers' => ['content-type' => 'application/json'],
                'diagnostics' => [
                    'verdict' => 'FAILED (401 Unauthorized)',
                    'reason' => 'This key was permanently deactivated on ' . $token->revoked_at->format('Y-m-d H:i'),
                    'suggestion' => 'Generate a new API key from the Developer Portal.',
                ],
            ]);
        }

        if ($token->expires_at && $token->expires_at->isPast()) {
            $latency = round((hrtime(true) - $startTime) / 1e6, 2);
            return response()->json([
                'status' => false,
                'http_status' => 401,
                'latency_ms' => $latency,
                'mode' => $simulationMode ? 'sandbox' : 'live',
                'error' => 'Token Expired',
                'message' => 'This API token has expired.',
                'response_body' => [
                    'status' => false,
                    'message' => 'API token expired.',
                    'error' => 'token_expired',
                ],
                'headers' => ['content-type' => 'application/json'],
                'diagnostics' => [
                    'verdict' => 'FAILED (401 Unauthorized)',
                    'reason' => 'This key expired on ' . $token->expires_at->format('Y-m-d H:i'),
                    'suggestion' => 'Generate a new active key.',
                ],
            ]);
        }

        $owner = $token->user;
        if ($owner?->api_access_status !== 'approved') {
            $latency = round((hrtime(true) - $startTime) / 1e6, 2);
            return response()->json([
                'status' => false,
                'http_status' => 403,
                'latency_ms' => $latency,
                'mode' => $simulationMode ? 'sandbox' : 'live',
                'error' => 'Access Denied',
                'message' => 'Your API access is ' . ($owner?->api_access_status ?: 'not approved') . '.',
                'response_body' => [
                    'status' => false,
                    'message' => 'API access is not approved. Please contact support.',
                    'error' => 'api_access_denied',
                    'access_status' => $owner?->api_access_status ?: 'none',
                ],
                'headers' => ['content-type' => 'application/json'],
                'diagnostics' => [
                    'verdict' => 'FAILED (403 Forbidden)',
                    'reason' => 'Account API access status is not approved.',
                    'suggestion' => 'Submit an API application from the Developer Portal.',
                ],
            ]);
        }

        // Billable balance check
        $minBalance = (float) SystemSetting::get('api_min_wallet_balance', 100.0);
        $userBalance = (float) ($owner?->balance->user_balance ?? 0.0);
        $isBillable = str_contains($endpoint, 'verifications') || str_contains($endpoint, 'vtu');

        if ($isBillable && $userBalance < $minBalance && ! $simulationMode) {
            $latency = round((hrtime(true) - $startTime) / 1e6, 2);
            return response()->json([
                'status' => false,
                'http_status' => 402,
                'latency_ms' => $latency,
                'mode' => 'live',
                'error' => 'Payment Required',
                'message' => 'Insufficient wallet balance for live execution.',
                'response_body' => [
                    'status' => false,
                    'message' => 'Insufficient wallet balance. Please fund your account to continue using the API.',
                    'error' => 'fund_not_sufficient',
                    'minimum' => $minBalance,
                    'current_balance' => $userBalance,
                ],
                'headers' => ['content-type' => 'application/json'],
                'diagnostics' => [
                    'verdict' => 'FAILED (402 Payment Required)',
                    'reason' => 'Wallet balance is ₦' . number_format($userBalance, 2) . ', which is below the minimum required balance of ₦' . number_format($minBalance, 2) . '.',
                    'suggestion' => 'Top up your account wallet or switch to Sandbox/Mock mode to test for free.',
                ],
            ]);
        }

        // 2. Simulation Mode (Mocked / Safe Test)
        if ($simulationMode) {
            $simulated = $this->generateSimulatedResponse($endpoint, $method, $payload, $owner, $token);
            $latency = round((hrtime(true) - $startTime) / 1e6, 2);

            return response()->json([
                'status' => true,
                'http_status' => $simulated['status_code'] ?? 200,
                'latency_ms' => $latency,
                'mode' => 'sandbox',
                'response_body' => $simulated['data'],
                'headers' => [
                    'content-type' => 'application/json; charset=utf-8',
                    'x-fuwa-sandbox' => 'simulated',
                    'x-ratelimit-limit' => (string) ($token->rate_limit_per_minute ?? 60),
                    'x-ratelimit-remaining' => (string) max(0, ($token->rate_limit_per_minute ?? 60) - 1),
                ],
                'diagnostics' => [
                    'verdict' => 'SUCCESS (' . ($simulated['status_code'] ?? 200) . ' - Sandbox Simulation)',
                    'token_authenticated' => true,
                    'token_name' => $token->name,
                    'token_last_four' => $token->last_four,
                    'wallet_charged' => '₦0.00 (Simulated)',
                    'notice' => 'Safe test run. Your API key authentication succeeded and the request payload was processed.',
                ],
            ]);
        }

        // 3. Live Dispatch
        if (! $plainToken) {
            return response()->json([
                'status' => false,
                'http_status' => 400,
                'latency_ms' => 5,
                'mode' => 'live',
                'error' => 'Plain API Key Required',
                'message' => 'To run a live API request, please paste your full API key starting with "nx_". When selecting a key from the saved list, Sandbox/Mock mode is used because plain keys are securely hashed on creation.',
                'response_body' => [
                    'status' => false,
                    'message' => 'Full plain API key required for live Bearer authorization dispatch.',
                ],
                'headers' => ['content-type' => 'application/json'],
                'diagnostics' => [
                    'verdict' => 'INPUT NEEDED',
                    'reason' => 'Plain token string is required for live Bearer header dispatch.',
                    'suggestion' => 'Paste your "nx_..." API key into the key field, or switch to Sandbox Mode.',
                ],
            ]);
        }

        $liveResponse = $this->dispatchLiveRequest($endpoint, $method, $payload, 'nx_' . $plainToken);
        $latency = round((hrtime(true) - $startTime) / 1e6, 2);

        $decodedBody = json_decode($liveResponse->getContent(), true);

        return response()->json([
            'status' => $liveResponse->isSuccessful(),
            'http_status' => $liveResponse->getStatusCode(),
            'latency_ms' => $latency,
            'mode' => 'live',
            'response_body' => $decodedBody ?: $liveResponse->getContent(),
            'headers' => [
                'content-type' => $liveResponse->headers->get('content-type', 'application/json'),
                'x-ratelimit-limit' => (string) ($token->rate_limit_per_minute ?? 60),
            ],
            'diagnostics' => [
                'verdict' => $liveResponse->isSuccessful() ? 'SUCCESS (' . $liveResponse->getStatusCode() . ' Live)' : 'HTTP ' . $liveResponse->getStatusCode(),
                'token_authenticated' => true,
                'token_name' => $token->name,
                'token_last_four' => $token->last_four,
            ],
        ]);
    }

    protected function generateSimulatedResponse(string $endpoint, string $method, array $payload, ?User $user, ApiToken $token): array
    {
        $cleanPath = trim($endpoint, '/');

        if ($cleanPath === 'api/v1/me' || $cleanPath === 'me') {
            return [
                'status_code' => 200,
                'data' => [
                    'status' => true,
                    'sandbox' => true,
                    'data' => [
                        'id' => $user?->id ?? 1,
                        'email' => $user?->email ?? 'dev@example.com',
                        'fullname' => $user?->fullname ?? 'Developer User',
                        'username' => $user?->username ?? 'devuser',
                        'api_access_status' => $user?->api_access_status ?? 'approved',
                        'wallet_balance' => (float) ($user?->balance->user_balance ?? 0.0),
                        'active_token_last_four' => $token->last_four,
                    ],
                ],
            ];
        }

        if (str_contains($cleanPath, 'verifications/nin')) {
            $number = (string) ($payload['number'] ?? '');
            if (strlen($number) !== 11 || ! ctype_digit($number)) {
                return [
                    'status_code' => 422,
                    'data' => [
                        'status' => false,
                        'message' => 'The number field must be an 11-digit NIN string.',
                        'errors' => [
                            'number' => ['The number must be 11 digits.'],
                        ],
                    ],
                ];
            }

            return [
                'status_code' => 200,
                'data' => [
                    'status' => true,
                    'sandbox' => true,
                    'message' => 'NIN verification successful (Sandbox Simulation)',
                    'data' => [
                        'nin' => $number,
                        'firstname' => $payload['firstname'] ?? 'John',
                        'middlename' => 'Emeka',
                        'lastname' => $payload['lastname'] ?? 'Doe',
                        'gender' => 'm',
                        'birthdate' => $payload['dob'] ?? '1992-05-18',
                        'phone' => '08012345678',
                        'residence_state' => 'Lagos',
                        'title' => 'Mr',
                        'verification_id' => 'VRF-SBX-' . strtoupper(substr(md5($number), 0, 8)),
                        'status' => 'verified',
                    ],
                ],
            ];
        }

        if (str_contains($cleanPath, 'verifications/bvn')) {
            $number = (string) ($payload['number'] ?? '');
            if (strlen($number) !== 11 || ! ctype_digit($number)) {
                return [
                    'status_code' => 422,
                    'data' => [
                        'status' => false,
                        'message' => 'The number field must be an 11-digit BVN string.',
                        'errors' => [
                            'number' => ['The number must be 11 digits.'],
                        ],
                    ],
                ];
            }

            return [
                'status_code' => 200,
                'data' => [
                    'status' => true,
                    'sandbox' => true,
                    'message' => 'BVN verification successful (Sandbox Simulation)',
                    'data' => [
                        'bvn' => $number,
                        'firstname' => $payload['firstname'] ?? 'Jane',
                        'lastname' => $payload['lastname'] ?? 'Doe',
                        'date_of_birth' => $payload['dob'] ?? '1995-11-20',
                        'phone_number' => '08098765432',
                        'enrollment_bank' => '058',
                        'verification_id' => 'VRF-SBX-' . strtoupper(substr(md5($number), 0, 8)),
                        'status' => 'verified',
                    ],
                ],
            ];
        }

        if (str_contains($cleanPath, 'legal/catalog')) {
            return [
                'status_code' => 200,
                'data' => [
                    'status' => true,
                    'sandbox' => true,
                    'data' => [
                        [
                            'document_type' => 'power_of_attorney',
                            'name' => 'Power of Attorney',
                            'category' => 'Legal & Authorization',
                            'price' => 5000.0,
                        ],
                        [
                            'document_type' => 'tenancy_agreement',
                            'name' => 'Tenancy Agreement',
                            'category' => 'Property & Real Estate',
                            'price' => 7500.0,
                        ],
                        [
                            'document_type' => 'affidavit_of_loss',
                            'name' => 'Affidavit of Loss',
                            'category' => 'Court & Notary Affidavits',
                            'price' => 3000.0,
                        ],
                    ],
                ],
            ];
        }

        if (str_contains($cleanPath, 'vtu/airtime')) {
            return [
                'status_code' => 200,
                'data' => [
                    'status' => true,
                    'sandbox' => true,
                    'message' => 'Airtime purchase simulated successfully',
                    'reference' => 'VTU-SBX-' . strtoupper(substr(md5(uniqid()), 0, 10)),
                    'network' => $payload['network'] ?? 'MTN',
                    'amount' => (float) ($payload['amount'] ?? 500),
                    'phone' => $payload['phone'] ?? '08011223344',
                ],
            ];
        }

        return [
            'status_code' => 200,
            'data' => [
                'status' => true,
                'sandbox' => true,
                'message' => 'Sandbox request executed successfully.',
                'endpoint' => $endpoint,
                'method' => $method,
                'received_payload' => $payload,
                'timestamp' => now()->toIso8601String(),
            ],
        ];
    }

    protected function dispatchLiveRequest(string $endpoint, string $method, array $payload, string $bearerToken)
    {
        $uri = '/' . ltrim($endpoint, '/');

        $server = [
            'HTTP_AUTHORIZATION' => 'Bearer ' . $bearerToken,
            'HTTP_ACCEPT' => 'application/json',
            'CONTENT_TYPE' => 'application/json',
        ];

        $content = in_array($method, ['POST', 'PUT', 'PATCH']) ? json_encode($payload) : null;

        $subRequest = Request::create(
            $uri,
            $method,
            $method === 'GET' ? $payload : [],
            [],
            [],
            $server,
            $content
        );

        return app()->handle($subRequest);
    }
}
