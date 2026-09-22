<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureApprovedParcelAgent
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        // 1. Must be logged in
        if (!$user) {
            return redirect()->route('login');
        }

        $agent = $user->parcelAgent;

        // 2. Must be an agent
        if (!$agent) {
            if ($request->routeIs('parcels.register') || $request->routeIs('parcels.register.submit')) {
                return $next($request);
            }
            return redirect()->route('parcels.register')->with('error', 'You must register as a Parcel Agent first.');
        }

        // 3. Must be approved
        if ($agent->status !== 'approved') {
            // Allow them to see the dashboard to check status, but block actionable routes
            if ($request->routeIs('parcels.dashboard') || $request->routeIs('parcels.register') || $request->routeIs('parcels.register.submit')) {
                return $next($request);
            }
            return redirect()->route('parcels.dashboard')->with('error', 'Your Parcel Agent account is pending approval or suspended.');
        }

        return $next($request);
    }
}
