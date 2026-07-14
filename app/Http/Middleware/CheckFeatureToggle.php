<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\FeatureToggle;
use Illuminate\Support\Facades\Cache;

class CheckFeatureToggle
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $featureName): Response
    {
        $featureKey = strtolower($featureName);
        
        // Define explicitly allowed features
        $allowedFeatures = ['nin_verification', 'bvn_verification'];

        if (!in_array($featureKey, $allowedFeatures)) {
            $message = 'This feature is currently locked.';
            
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json(['success' => false, 'message' => $message], 403);
            }

            return redirect()->route('dashboard')->with('error', $message);
        }

        // Additional path checking to explicitly block logistics, auctions, legal hub, and other extended services
        $path = $request->path();
        if (
            preg_match('/^(logistics|auction|services\/legal|services\/legal-hub|explore\/logistics|explore\/auctions)/i', $path)
        ) {
            $message = 'This service is currently unavailable.';
            
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json(['success' => false, 'message' => $message], 403);
            }

            return redirect()->route('dashboard')->with('error', $message);
        }

        $feature = Cache::remember('feature_toggle:' . $featureKey, now()->addSeconds(30), function () use ($featureKey) {
            return FeatureToggle::where('feature_name', $featureKey)->first();
        });

        if (!$feature) {
            return $next($request);
        }

        if (!$feature->is_active) {
            $message = $feature->offline_message ?: 'This feature is currently unavailable.';
            
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json(['success' => false, 'message' => $message], 403);
            }

            return redirect()->route('dashboard')->with('error', $message);
        }

        return $next($request);
    }
}
