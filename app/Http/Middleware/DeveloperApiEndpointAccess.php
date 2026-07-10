<?php

namespace App\Http\Middleware;

use App\Models\DeveloperApiRequestLog;
use App\Services\DeveloperApi\DeveloperApiCatalog;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class DeveloperApiEndpointAccess
{
    public function __construct(private readonly DeveloperApiCatalog $catalog)
    {
    }

    public function handle(Request $request, Closure $next): Response
    {
        $endpoint = $this->catalog->match($request);
        if ($endpoint && ! $endpoint->is_enabled) {
            return response()->json([
                'status' => false,
                'message' => 'This API endpoint is currently unavailable.',
                'error' => 'endpoint_disabled',
            ], 403);
        }

        if ($endpoint) {
            $request->attributes->set('developer_api_endpoint', $endpoint);
        }

        $response = $next($request);

        $this->logRequest($request, $endpoint, $response);

        return $response;
    }

    private function logRequest(Request $request, $endpoint, Response $response): void
    {
        $token = $request->attributes->get('api_token');
        $user = $request->user();

        if (! $token && ! $user) {
            return;
        }

        try {
            DeveloperApiRequestLog::query()->create([
                'api_token_id' => $token?->id,
                'user_id' => $user?->id,
                'endpoint_slug' => $endpoint?->slug,
                'method' => strtoupper($request->method()),
                'path' => '/' . ltrim($request->path(), '/'),
                'status_code' => $response->getStatusCode(),
                'ip_address' => $request->ip(),
                'declared_website' => data_get($user?->api_application_details, 'website'),
                'origin_host' => $this->extractHost((string) $request->headers->get('Origin', '')),
                'referer_host' => $this->extractHost((string) $request->headers->get('Referer', '')),
                'user_agent' => substr((string) $request->userAgent(), 0, 1000),
                'requested_at' => now(),
            ]);
        } catch (\Throwable) {
            // Usage logging must never block API responses.
        }
    }

    private function extractHost(string $url): ?string
    {
        $host = parse_url(trim($url), PHP_URL_HOST);

        return is_string($host) && $host !== '' ? $host : null;
    }
}

