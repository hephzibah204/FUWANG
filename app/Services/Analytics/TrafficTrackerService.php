<?php

namespace App\Services\Analytics;

use App\Models\SearchKeywordLog;
use App\Models\WebsiteTrafficLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class TrafficTrackerService
{
    /**
     * Track an incoming HTTP request.
     */
    public function trackRequest(Request $request): ?string
    {
        // Don't track static assets, webhooks, or health checks
        $path = $request->path();
        if ($this->shouldIgnorePath($path, $request)) {
            return null;
        }

        WebsiteTrafficLog::ensureSchemaIntegrity();
        SearchKeywordLog::ensureSchemaIntegrity();

        $visitorId = $request->cookie('_fuwa_vid');
        if (!$visitorId || strlen($visitorId) < 16) {
            $visitorId = hash('sha256', ($request->ip() ?? '') . ($request->userAgent() ?? '') . Str::random(16));
        }

        $referer = (string) $request->headers->get('referer');
        $refererHost = null;
        if (!empty($referer)) {
            $parsedHost = parse_url($referer, PHP_URL_HOST);
            $refererHost = $parsedHost ? strtolower((string) $parsedHost) : null;
        }

        $origination = $this->resolveOrigination($referer, $refererHost, $request);
        $userAgent = (string) $request->userAgent();
        $device = $this->resolveDevice($userAgent);
        $browser = $this->resolveBrowser($userAgent);
        $platform = $this->resolvePlatform($userAgent);

        $utmSource = $request->query('utm_source');
        $utmMedium = $request->query('utm_medium');
        $utmCampaign = $request->query('utm_campaign');
        $utmTerm = $request->query('utm_term');

        try {
            WebsiteTrafficLog::create([
                'visitor_id' => substr($visitorId, 0, 64),
                'session_id' => $request->hasSession() ? $request->session()->getId() : null,
                'user_id' => Auth::id(),
                'ip' => substr((string) $request->ip(), 0, 45),
                'path' => '/' . ltrim(substr($path, 0, 250), '/'),
                'url' => substr($request->fullUrl(), 0, 1000),
                'method' => substr($request->method(), 0, 10),
                'origination_source' => $origination,
                'referrer' => $referer ? substr($referer, 0, 1000) : null,
                'referrer_host' => $refererHost ? substr($refererHost, 0, 150) : null,
                'device_type' => $device,
                'browser' => $browser,
                'platform' => $platform,
                'country_code' => $request->header('CF-IPCountry') ?: null,
                'utm_source' => $utmSource ? substr($utmSource, 0, 100) : null,
                'utm_medium' => $utmMedium ? substr($utmMedium, 0, 100) : null,
                'utm_campaign' => $utmCampaign ? substr($utmCampaign, 0, 100) : null,
                'utm_term' => $utmTerm ? substr($utmTerm, 0, 100) : null,
                'created_at' => now(),
            ]);

            // Check if search keyword is passed via query params or referer
            $this->detectAndLogSearchKeywords($request, $visitorId, $referer);

        } catch (\Throwable $e) {
            // Silently fail to never break user requests
        }

        return $visitorId;
    }

    /**
     * Log a search keyword (from search hub, URL query, etc.)
     */
    public function logKeyword(string $keyword, string $source = 'search_hub', int $resultsCount = 0, ?Request $request = null): void
    {
        $clean = trim(preg_replace('/\s+/', ' ', $keyword));
        if (mb_strlen($clean) < 2) {
            return;
        }

        SearchKeywordLog::ensureSchemaIntegrity();

        $visitorId = $request ? ($request->cookie('_fuwa_vid') ?: null) : null;
        $userId = Auth::id();
        $page = $request ? ('/' . ltrim($request->path(), '/')) : null;

        try {
            SearchKeywordLog::create([
                'keyword' => mb_substr($clean, 0, 255),
                'source' => substr($source, 0, 50),
                'user_id' => $userId,
                'visitor_id' => $visitorId ? substr($visitorId, 0, 64) : null,
                'results_count' => $resultsCount,
                'page' => $page ? substr($page, 0, 255) : null,
                'created_at' => now(),
            ]);
        } catch (\Throwable $e) {
            // Silently fail
        }
    }

    private function detectAndLogSearchKeywords(Request $request, string $visitorId, string $referer): void
    {
        // 1. From URL queries on the site (?q=..., ?search=..., ?keyword=...)
        $searchKeys = ['q', 'search', 'query', 'keyword', 'k', 'term', 'utm_term'];
        foreach ($searchKeys as $param) {
            $term = $request->query($param);
            if (!empty($term) && is_string($term)) {
                $this->logKeyword($term, $param === 'utm_term' ? 'utm_campaign' : 'url_query', 0, $request);
                break;
            }
        }

        // 2. From search engine referer URL queries (e.g. google.com?q=...)
        if (!empty($referer)) {
            $parsedQuery = parse_url($referer, PHP_URL_QUERY);
            if (!empty($parsedQuery)) {
                parse_str($parsedQuery, $params);
                $refKeyword = $params['q'] ?? $params['query'] ?? $params['p'] ?? null;
                if (!empty($refKeyword) && is_string($refKeyword)) {
                    $this->logKeyword($refKeyword, 'search_engine_referer', 0, $request);
                }
            }
        }
    }

    private function shouldIgnorePath(string $path, Request $request): bool
    {
        // Ignore static files, webhooks, health checks, debugbar
        $ignoredPatterns = [
            'assets/*',
            'build/*',
            'vendor/*',
            'favicon.ico',
            'robots.txt',
            'up',
            'livewire/*',
            '_debugbar/*',
            'webhooks/*',
            '*.php',
            'api/track/*',
        ];

        foreach ($ignoredPatterns as $pattern) {
            if (Str::is($pattern, $path)) {
                return true;
            }
        }

        // Don't track image/css/js requests
        $ext = pathinfo($path, PATHINFO_EXTENSION);
        if (in_array(strtolower($ext), ['png', 'jpg', 'jpeg', 'gif', 'svg', 'webp', 'css', 'js', 'woff', 'woff2', 'ttf', 'ico'])) {
            return true;
        }

        return false;
    }

    private function resolveOrigination(string $referer, ?string $host, Request $request): string
    {
        $utmSource = strtolower((string) $request->query('utm_source', ''));
        if (!empty($utmSource)) {
            if (str_contains($utmSource, 'google')) return 'Google (Ads/Campaign)';
            if (str_contains($utmSource, 'facebook') || str_contains($utmSource, 'fb')) return 'Facebook (Campaign)';
            if (str_contains($utmSource, 'instagram')) return 'Instagram (Campaign)';
            if (str_contains($utmSource, 'twitter') || str_contains($utmSource, 'x')) return 'Twitter / X (Campaign)';
            if (str_contains($utmSource, 'whatsapp')) return 'WhatsApp (Campaign)';
            if (str_contains($utmSource, 'email') || str_contains($utmSource, 'newsletter')) return 'Email Newsletter';
            return 'Campaign: ' . ucfirst($utmSource);
        }

        if (empty($referer) || empty($host)) {
            return 'Direct';
        }

        $appHost = parse_url(config('app.url', ''), PHP_URL_HOST) ?: $request->getHost();
        if ($host === strtolower($appHost) || str_ends_with($host, '.' . strtolower($appHost))) {
            return 'Internal';
        }

        if (str_contains($host, 'google.')) return 'Google';
        if (str_contains($host, 'bing.')) return 'Bing';
        if (str_contains($host, 'yahoo.')) return 'Yahoo';
        if (str_contains($host, 'duckduckgo.')) return 'DuckDuckGo';
        if (str_contains($host, 'facebook.') || str_contains($host, 'fb.me')) return 'Facebook';
        if (str_contains($host, 'twitter.') || str_contains($host, 't.co') || str_contains($host, 'x.com')) return 'Twitter / X';
        if (str_contains($host, 'instagram.')) return 'Instagram';
        if (str_contains($host, 'whatsapp.') || str_contains($host, 'wa.me')) return 'WhatsApp';
        if (str_contains($host, 'tiktok.')) return 'TikTok';
        if (str_contains($host, 'linkedin.') || str_contains($host, 'lnkd.in')) return 'LinkedIn';
        if (str_contains($host, 'youtube.') || str_contains($host, 'youtu.be')) return 'YouTube';
        if (str_contains($host, 't.me') || str_contains($host, 'telegram.')) return 'Telegram';
        if (str_contains($host, 'nairaland.')) return 'Nairaland';
        if (str_contains($host, 'reddit.')) return 'Reddit';

        return 'Referral (' . $host . ')';
    }

    private function resolveDevice(string $ua): string
    {
        if (preg_match('/(bot|crawl|slurp|spider|mediapartners|googlebot|bingbot)/i', $ua)) {
            return 'Bot';
        }
        if (preg_match('/(tablet|ipad|playbook|silk)|(android(?!.*mobi))/i', $ua)) {
            return 'Tablet';
        }
        if (preg_match('/(mobi|iphone|ipod|android.*mobile|blackberry|opera mini|windows phone)/i', $ua)) {
            return 'Mobile';
        }
        return 'Desktop';
    }

    private function resolveBrowser(string $ua): string
    {
        if (preg_match('/edg/i', $ua)) return 'Edge';
        if (preg_match('/opr|opera/i', $ua)) return 'Opera';
        if (preg_match('/chrome|crios/i', $ua)) return 'Chrome';
        if (preg_match('/firefox|fxios/i', $ua)) return 'Firefox';
        if (preg_match('/safari/i', $ua) && !preg_match('/chrome/i', $ua)) return 'Safari';
        if (preg_match('/msie|trident/i', $ua)) return 'Internet Explorer';
        return 'Other';
    }

    private function resolvePlatform(string $ua): string
    {
        if (preg_match('/android/i', $ua)) return 'Android';
        if (preg_match('/iphone|ipad|ipod/i', $ua)) return 'iOS';
        if (preg_match('/windows/i', $ua)) return 'Windows';
        if (preg_match('/macintosh|mac os x/i', $ua)) return 'macOS';
        if (preg_match('/linux/i', $ua)) return 'Linux';
        return 'Other';
    }
}
