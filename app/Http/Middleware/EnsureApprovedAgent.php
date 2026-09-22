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

        if (! $user || ! $user->isApprovedEnrollmentAgent() || empty($user->enrollmentAgent->picture_path)) {
            if ($request->expectsJson()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Access denied. You must be an approved enrollment agent with an uploaded profile picture.',
                ], 403);
            }

            return redirect()->route('agent.onboarding.index')->with('error', 'Please upload your profile picture to fully activate your agent account.');
        }

        if (! session()->has('active_dashboard_mode')) {
            session(['active_dashboard_mode' => 'agency']);
        }

        return $next($request);
    }
}
