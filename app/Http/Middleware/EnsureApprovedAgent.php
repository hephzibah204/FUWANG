<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureApprovedAgent
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        if (! $user || ! $user->enrollmentAgent) {
            if ($request->expectsJson()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Access denied. You must be an enrollment agent.',
                ], 403);
            }

            return redirect()->route('agent.register')->with('info', 'Please submit your agent registration first.');
        }

        if (! session()->has('active_dashboard_mode')) {
            session(['active_dashboard_mode' => 'agency']);
        }

        return $next($request);
    }
}
