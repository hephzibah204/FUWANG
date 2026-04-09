<?php

namespace App\Http\Middleware;

use App\Exceptions\ServerException;
use App\Models\ApiToken;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;

class ApiRateLimit
{
    public function handle(Request $request, Closure $next): Response
    {
        /** @var ApiToken|null $token */
        $token = $request->attributes->get('api_token');
        if (!$token) {
            return $next($request);
        }

        $limit = (int) ($token->rate_limit_per_minute ?? 60);
        if ($limit <= 0) {
            $response = $next($request);
            $this->addHeaders($response, $limit, 0, 0);
            return $response;
        }

        $window = now()->format('YmdHi');
        $key = 'api_rl:' . $token->id . ':' . $window;

        // Atomic increment — always set a TTL on first write to prevent immortal keys
        $count = Cache::increment($key);
        if ($count === 1) {
            // 2-minute TTL ensures the window always expires even under concurrent load
            Cache::put($key, 1, 120);
        }

        if ($count > $limit) {
            throw new TooManyRequestsHttpException(60, 'Rate limit exceeded.');
        }

        $response = $next($request);

        $remaining = max(0, $limit - $count);
        $reset = now()->addMinute()->timestamp;

        $this->addHeaders($response, $limit, $remaining, $reset);

        return $response;
    }

    /**
     * Add the rate limit headers to the response.
     */
    protected function addHeaders(Response $response, int $limit, int $remaining, int $reset): void
    {
        $response->headers->add([
            'X-RateLimit-Limit' => $limit,
            'X-RateLimit-Remaining' => $remaining,
            'X-RateLimit-Reset' => $reset,
        ]);
    }
}
