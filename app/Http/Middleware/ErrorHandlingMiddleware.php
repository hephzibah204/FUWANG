<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class ErrorHandlingMiddleware
{
    /**
     * Handle an incoming request.
     * Generates a correlation ID and injects it into the request for tracking.
     * Also catches exceptions in case they bypass the global handler (unlikely, but good for custom wrappers).
     */
    public function handle(Request $request, Closure $next): Response
    {
        // 1. Generate or extract correlation ID
        $correlationId = $request->header('X-Correlation-ID', (string) Str::uuid());
        
        // 2. Inject into request attributes so it's available everywhere (handlers, controllers, logs)
        $request->attributes->set('correlation_id', $correlationId);

        // 3. Process the request
        $response = $next($request);

        // 4. Attach correlation ID to response headers
        if (method_exists($response, 'header')) {
            $response->header('X-Correlation-ID', $correlationId);
        }

        return $response;
    }
}
