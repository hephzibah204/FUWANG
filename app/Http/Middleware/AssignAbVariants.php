<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AssignAbVariants
{
    public function handle(Request $request, Closure $next, string $experiment): Response
    {
        $experiments = (array) config('ab.experiments', []);
        $definition = $experiments[$experiment] ?? null;
        if (!$definition) {
            return $next($request);
        }

        $cookieName = (string) ($definition['cookie'] ?? ('ab_' . $experiment));
        $variants = (array) ($definition['variants'] ?? ['A' => 100]);

        $existing = (string) $request->cookie($cookieName, '');
        $variant = $existing !== '' ? $existing : $this->pickVariant($variants);

        $ab = (array) $request->attributes->get('ab_variants', []);
        $ab[$experiment] = $variant;
        $request->attributes->set('ab_variants', $ab);

        $response = $next($request);
        if ($existing === '') {
            $response->headers->setCookie(cookie($cookieName, $variant, 60 * 24 * 90, null, null, false, true, false, 'Lax'));
        }

        return $response;
    }

    private function pickVariant(array $weights): string
    {
        $total = 0;
        foreach ($weights as $w) {
            $total += max(0, (int) $w);
        }
        if ($total <= 0) {
            return 'A';
        }

        $roll = random_int(1, $total);
        $acc = 0;
        foreach ($weights as $variant => $w) {
            $acc += max(0, (int) $w);
            if ($roll <= $acc) {
                return (string) $variant;
            }
        }

        return (string) array_key_first($weights) ?: 'A';
    }
}

