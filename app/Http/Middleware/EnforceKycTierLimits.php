<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Services\KycService;
use App\Models\User;

class EnforceKycTierLimits
{
    protected $kycService;

    public function __construct(KycService $kycService)
    {
        $this->kycService = $kycService;
    }

    public function handle(Request $request, Closure $next)
    {
        /** @var User|null $user */
        $user = Auth::user();

        if ($user) {
            $amount = $this->getTransactionAmount($request);
            
            if ($amount > 0) {
                $result = $this->kycService->canTransact($user, $amount);
                
                if (!$result['allowed']) {
                    // Use a more informative JSON response for API requests
                    if ($request->expectsJson()) {
                        return response()->json([
                            'status' => 'error',
                            'message' => $result['message'] ?? 'Transaction limit exceeded for your verification tier.',
                            'error_code' => 'KYC_LIMIT_EXCEEDED'
                        ], 403);
                    }
                    // For web requests, redirect back with an error
                    return back()->with('error', $result['message'] ?? 'Transaction limit exceeded.');
                }
            }
        }

        return $next($request);
    }

    /**
     * Extract the transaction amount from the request.
     * This needs to be adapted based on how different forms submit amounts.
     */
    protected function getTransactionAmount(Request $request): float
    {
        // Add all possible amount field names from different transaction forms
        $amountFields = ['amount', 'total_amount', 'price']; 
        
        foreach ($amountFields as $field) {
            if ($request->filled($field)) {
                $amount = (float) $request->input($field);
                if ($amount > 0) {
                    return $amount;
                }
            }
        }

        // Fallback for requests where the amount is part of a JSON payload
        $json = $request->json()->all();
        foreach ($amountFields as $field) {
            if (isset($json[$field])) {
                $amount = (float) $json[$field];
                if ($amount > 0) {
                    return $amount;
                }
            }
        }
        
        return 0.0;
    }
}
