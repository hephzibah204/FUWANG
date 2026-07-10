<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ApiToken;
use App\Models\DeveloperApiRequestLog;
use App\Models\SystemSetting;
use App\Models\User;
use App\Services\DeveloperApi\DeveloperApiCatalog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DeveloperApiAdminController extends Controller
{
    public function index(Request $request, DeveloperApiCatalog $catalog)
    {
        $catalog->ensureDefaults();

        $days = 30;
        $since = now()->subDays($days);

        $applications = User::query()
            ->whereIn('api_access_status', ['pending', 'approved', 'rejected'])
            ->withCount(['apiTokens as active_api_tokens_count' => function ($query) {
                $query->whereNull('revoked_at');
            }])
            ->latest()
            ->paginate(20, ['*'], 'applications');

        $recentUsage = DeveloperApiRequestLog::query()
            ->with(['user:id,fullname,email', 'token:id,name,last_four'])
            ->where('requested_at', '>=', $since)
            ->latest('requested_at')
            ->limit(25)
            ->get();

        $siteUsage = DeveloperApiRequestLog::query()
            ->selectRaw("
                COALESCE(NULLIF(origin_host, ''), NULLIF(referer_host, ''), NULLIF(declared_website, '')) as site,
                COUNT(*) as total_requests,
                COUNT(DISTINCT user_id) as total_users,
                MAX(requested_at) as last_seen
            ")
            ->where('requested_at', '>=', $since)
            ->where(function ($query) {
                $query->whereNotNull('origin_host')
                    ->orWhereNotNull('referer_host')
                    ->orWhereNotNull('declared_website');
            })
            ->groupBy('site')
            ->orderByDesc('total_requests')
            ->limit(15)
            ->get();

        $websiteCount = (int) $siteUsage->pluck('site')->filter()->unique()->count();
        $declaredWebsites = User::query()
            ->where('api_access_status', 'approved')
            ->get()
            ->pluck('api_application_details.website')
            ->filter()
            ->unique()
            ->values();

        $metrics = [
            'approvedDevelopers' => User::where('api_access_status', 'approved')->count(),
            'pendingApplications' => User::where('api_access_status', 'pending')->count(),
            'activeTokens' => ApiToken::query()->whereNull('revoked_at')->count(),
            'requests30d' => DeveloperApiRequestLog::query()->where('requested_at', '>=', $since)->count(),
            'uniqueUsers30d' => DeveloperApiRequestLog::query()->where('requested_at', '>=', $since)->distinct('user_id')->count('user_id'),
            'websiteCount30d' => max($websiteCount, $declaredWebsites->count()),
        ];

        $pricing = [
            'developer_api_nin_price' => (float) SystemSetting::get('developer_api_nin_price', 200),
            'developer_api_bvn_basic_price' => (float) SystemSetting::get('developer_api_bvn_basic_price', 100),
            'developer_api_bvn_premium_price' => (float) SystemSetting::get('developer_api_bvn_premium_price', 500),
        ];

        $docs = [
            'title' => (string) SystemSetting::get('developer_api_docs_title', 'Developer API Documentation'),
            'intro' => (string) SystemSetting::get('developer_api_docs_intro', 'Integrate your applications with our API using token-based authentication and wallet billing.'),
            'auth' => (string) SystemSetting::get('developer_api_docs_auth', 'Create a token from the developer portal, send it as a Bearer token, and keep sufficient wallet balance for billable endpoints.'),
            'best_practices' => (string) SystemSetting::get('developer_api_docs_best_practices', 'Use server-side requests, rotate tokens periodically, handle 402 and 429 responses, and store your own request references.'),
            'support' => (string) SystemSetting::get('developer_api_docs_support', 'Contact support for approval issues, endpoint access, or commercial onboarding.'),
        ];

        $endpoints = $catalog->all()->groupBy(fn ($endpoint) => $endpoint->group_name ?: 'Other');

        return view('admin.developer_api.index', compact(
            'applications',
            'recentUsage',
            'siteUsage',
            'metrics',
            'pricing',
            'docs',
            'endpoints',
            'days'
        ));
    }

    public function updatePricing(Request $request)
    {
        $data = $request->validate([
            'developer_api_nin_price' => ['required', 'numeric', 'min:0'],
            'developer_api_bvn_basic_price' => ['required', 'numeric', 'min:0'],
            'developer_api_bvn_premium_price' => ['required', 'numeric', 'min:0'],
        ]);

        foreach ($data as $key => $value) {
            SystemSetting::set($key, (string) $value, 'developer_api', 'number', ucwords(str_replace('_', ' ', $key)));
        }

        return back()->with('success', 'Developer API pricing updated successfully.');
    }

    public function updateDocs(Request $request)
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:150'],
            'intro' => ['nullable', 'string', 'max:5000'],
            'auth' => ['nullable', 'string', 'max:5000'],
            'best_practices' => ['nullable', 'string', 'max:5000'],
            'support' => ['nullable', 'string', 'max:5000'],
        ]);

        SystemSetting::set('developer_api_docs_title', $data['title'], 'developer_api', 'string', 'Developer Docs Title');
        SystemSetting::set('developer_api_docs_intro', $data['intro'] ?? '', 'developer_api', 'text', 'Developer Docs Intro');
        SystemSetting::set('developer_api_docs_auth', $data['auth'] ?? '', 'developer_api', 'text', 'Developer Docs Auth');
        SystemSetting::set('developer_api_docs_best_practices', $data['best_practices'] ?? '', 'developer_api', 'text', 'Developer Docs Best Practices');
        SystemSetting::set('developer_api_docs_support', $data['support'] ?? '', 'developer_api', 'text', 'Developer Docs Support');

        return back()->with('success', 'Developer API documentation updated successfully.');
    }

    public function updateEndpoints(Request $request, DeveloperApiCatalog $catalog)
    {
        $catalog->ensureDefaults();

        foreach ($catalog->all() as $endpoint) {
            $enabledKey = 'enabled_' . $endpoint->id;
            $summaryKey = 'summary_' . $endpoint->id;
            $requestKey = 'request_example_' . $endpoint->id;
            $responseKey = 'response_example_' . $endpoint->id;

            $endpoint->is_enabled = $request->boolean($enabledKey);
            $endpoint->docs_summary = $request->input($summaryKey);
            $endpoint->docs_request_example = $request->input($requestKey);
            $endpoint->docs_response_example = $request->input($responseKey);
            $endpoint->save();
        }

        return back()->with('success', 'Developer API endpoints updated successfully.');
    }
}

