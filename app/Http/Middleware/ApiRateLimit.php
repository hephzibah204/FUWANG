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
            return $next($request);
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

        return $next($request);
    }
}

