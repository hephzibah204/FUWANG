<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class BlockPrototypePollution
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handle(Request $request, Closure $next)
    {
        // Recursively inspect request input keys and values for prototype pollution signatures
        if ($this->hasSuspectKeywords($request->all()) || $this->hasSuspectKeywords($request->query())) {
            return response()->json([
                'status' => false,
                'message' => 'Bad Request: Suspected malicious payload.'
            ], 400);
        }

        return $next($request);
    }

    /**
     * Recursively scan arrays for keywords like '__proto__' or 'constructor'.
     *
     * @param  mixed  $data
     * @return bool
     */
    private function hasSuspectKeywords($data): bool
    {
        if (is_array($data)) {
            foreach ($data as $key => $value) {
                if (is_string($key) && ($key === '__proto__' || $key === 'constructor')) {
                    return true;
                }
                if ($this->hasSuspectKeywords($value)) {
                    return true;
                }
            }
        } elseif (is_string($data)) {
            // Also scan raw string contents for prototype pollution keys if injected into fields
            if (str_contains($data, '__proto__') || str_contains($data, 'constructor.prototype')) {
                return true;
            }
        }

        return false;
    }
}
