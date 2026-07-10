<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RateLimitByService
{
    public function handle(Request $request, Closure $next, string $service): Response
    {
        $key = $this->resolveRequestKey($request, $service);

        $maxAttempts = match($service) {
            'logistics' => 10,
            default => 5,
        };

        $decayMinutes = match($service) {
            'logistics' => 1,
            default => 1,
        };

        if ($this->tooManyAttempts($key, $maxAttempts)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Too many attempts. Please try again later.',
                'retry_after' => $decayMinutes * 60,
            ], 429);
        }

        $this->incrementAttempts($key, $decayMinutes);

        $response = $next($request);

        $response->headers->set('X-RateLimit-Limit', $maxAttempts);
        $response->headers->set('X-RateLimit-Remaining', $this->getRemainingAttempts($key, $maxAttempts));

        return $response;
    }

    private function resolveRequestKey(Request $request, string $service): string
    {
        return $service . ':' . $request->ip() . ':' . $request->input('email', $request->input('username', 'guest'));
    }

    private function tooManyAttempts(string $key, int $maxAttempts): bool
    {
        $attempts = (int) cache()->get($key . ':attempts', 0);
        return $attempts >= $maxAttempts;
    }

    private function incrementAttempts(string $key, int $decayMinutes): void
    {
        $attempts = (int) cache()->get($key . ':attempts', 0);
        cache()->put($key . ':attempts', $attempts + 1, $decayMinutes * 60);
    }

    private function getRemainingAttempts(string $key, int $maxAttempts): int
    {
        $attempts = (int) cache()->get($key . ':attempts', 0);
        return max(0, $maxAttempts - $attempts);
    }
}