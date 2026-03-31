<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckInstallation
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $installedFile = storage_path('app/installed');

        if (empty(config('app.key'))) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json(['error' => 'APP_KEY is not configured.'], 503);
            }
            abort(503, 'APP_KEY is not configured.');
        }

        if (!file_exists($installedFile) && !$request->is('install*')) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json(['error' => 'Application not installed.'], 503);
            }
            return redirect()->route('install.index');
        }

        if (file_exists($installedFile) && $request->is('install*') && !$request->is('install/complete')) {
            return redirect('/');
        }

        return $next($request);
    }
}
