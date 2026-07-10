<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class ContentSecurityPolicy
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        // Vite dev server (when hot file exists) loads scripts from localhost.
        $viteDev = app()->environment('local') && is_file(base_path('hot'));
        $viteScript = $viteDev ? ' http://127.0.0.1:5173 http://localhost:5173' : '';
        $viteConnect = $viteDev ? ' http://127.0.0.1:5173 http://localhost:5173 ws://127.0.0.1:5173 ws://localhost:5173' : '';

        // Allow CDNs used by Nexus (fonts, payments), admin GrapesJS (unpkg), and canvas assets.
        $csp = "default-src 'self'; " .
               "script-src 'self' 'unsafe-inline' https://unpkg.com https://js.paystack.co https://checkout.flutterwave.com https://sdk.monnify.com{$viteScript}; " .
               "style-src 'self' 'unsafe-inline' https://fonts.googleapis.com https://unpkg.com https://cdnjs.cloudflare.com https://stackpath.bootstrapcdn.com https://paystack.com{$viteScript}; " .
               "img-src 'self' data: https: blob:; " .
               "font-src 'self' data: https://fonts.gstatic.com; " .
               "connect-src 'self' https: wss:{$viteConnect}; " .
               "worker-src 'self' blob:; " .
               "frame-src 'self' https: blob:; " .
               "object-src 'none'; " .
               "base-uri 'self'; " .
               "form-action 'self'; " .
               "frame-ancestors 'none'; " .
               "upgrade-insecure-requests; " .
               "block-all-mixed-content;";

        $response->headers->set('Content-Security-Policy', $csp);

        return $response;
    }
}
