<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnforceHttps
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!$request->secure() && app()->environment('production')) {
            if ($request->is('api/*')) {
                return response()->json([
                    'status' => false,
                    'message' => 'HTTPS is required for API access.',
                ], 403);
            }
            return redirect()->secure($request->getRequestUri());
        }

        return $next($request);
    }
}
