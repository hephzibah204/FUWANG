<?php

namespace App\Http\Controllers;

use App\Models\ApiToken;
use App\Models\SystemSetting;
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
}
