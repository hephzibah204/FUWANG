<?php

namespace App\Services\DeveloperApi;

use App\Models\DeveloperApiEndpoint;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class DeveloperApiCatalog
{
    /**
     * @return array<int, array<string, mixed>>
     */
    public static function defaults(): array
    {
        return [
            ['slug' => 'auth.me', 'group_name' => 'Auth', 'name' => 'Current API User', 'method' => 'GET', 'path_pattern' => 'api/v1/me', 'sort_order' => 10, 'docs_summary' => 'Returns the authenticated developer account profile.'],
            ['slug' => 'auth.revoke_token', 'group_name' => 'Auth', 'name' => 'Revoke Current Token', 'method' => 'DELETE', 'path_pattern' => 'api/v1/auth/token', 'sort_order' => 20, 'docs_summary' => 'Revokes the currently authenticated API token.'],
            ['slug' => 'verifications.nin', 'group_name' => 'Identity', 'name' => 'NIN Verification', 'method' => 'POST', 'path_pattern' => 'api/v1/verifications/nin', 'sort_order' => 30, 'docs_summary' => 'Verifies a NIN or phone lookup through the active verification provider stack.'],
            ['slug' => 'verifications.bvn', 'group_name' => 'Identity', 'name' => 'BVN Verification', 'method' => 'POST', 'path_pattern' => 'api/v1/verifications/bvn', 'sort_order' => 40, 'docs_summary' => 'Verifies a BVN using the configured provider and requested service tier.'],
            ['slug' => 'verifications.result', 'group_name' => 'Identity', 'name' => 'Verification Result Fetch', 'method' => 'GET', 'path_pattern' => 'api/v1/verifications/*', 'sort_order' => 50, 'docs_summary' => 'Fetches a previously created verification result by internal result id.'],
            ['slug' => 'vuvaa.create_user', 'group_name' => 'VUVAA', 'name' => 'Create VUVAA User', 'method' => 'POST', 'path_pattern' => 'api/v1/vuvaa/create_user', 'sort_order' => 60, 'docs_summary' => 'Creates a VUVAA-linked user profile.'],
            ['slug' => 'vuvaa.login', 'group_name' => 'VUVAA', 'name' => 'VUVAA Login', 'method' => 'POST', 'path_pattern' => 'api/v1/vuvaa/login', 'sort_order' => 70, 'docs_summary' => 'Authenticates against VUVAA.'],
            ['slug' => 'vuvaa.verify_nin', 'group_name' => 'VUVAA', 'name' => 'VUVAA Verify NIN', 'method' => 'POST', 'path_pattern' => 'api/v1/vuvaa/verify_nin', 'sort_order' => 80, 'docs_summary' => 'Submits a VUVAA NIN verification request.'],
            ['slug' => 'vuvaa.in_person_verification', 'group_name' => 'VUVAA', 'name' => 'VUVAA In-Person Verification', 'method' => 'POST', 'path_pattern' => 'api/v1/vuvaa/in_person_verification', 'sort_order' => 90, 'docs_summary' => 'Starts an in-person verification request.'],
            ['slug' => 'vuvaa.share_code', 'group_name' => 'VUVAA', 'name' => 'VUVAA Share Code', 'method' => 'POST', 'path_pattern' => 'api/v1/vuvaa/share_code', 'sort_order' => 100, 'docs_summary' => 'Validates a VUVAA share code.'],
            ['slug' => 'vuvaa.requery', 'group_name' => 'VUVAA', 'name' => 'VUVAA Requery', 'method' => 'POST', 'path_pattern' => 'api/v1/vuvaa/requery', 'sort_order' => 110, 'docs_summary' => 'Fetches a prior VUVAA transaction again.'],
            ['slug' => 'vuvaa.wallet', 'group_name' => 'VUVAA', 'name' => 'VUVAA Wallet', 'method' => 'POST', 'path_pattern' => 'api/v1/vuvaa/wallet', 'sort_order' => 120, 'docs_summary' => 'Returns wallet information from VUVAA.'],
            ['slug' => 'vuvaa.transaction_history', 'group_name' => 'VUVAA', 'name' => 'VUVAA Transaction History', 'method' => 'POST', 'path_pattern' => 'api/v1/vuvaa/transaction_history', 'sort_order' => 130, 'docs_summary' => 'Returns transaction history from VUVAA.'],
            ['slug' => 'vuvaa.reasons', 'group_name' => 'VUVAA', 'name' => 'VUVAA Reasons', 'method' => 'POST', 'path_pattern' => 'api/v1/vuvaa/reasons', 'sort_order' => 140, 'docs_summary' => 'Returns available VUVAA reason codes.'],
            ['slug' => 'legal.catalog', 'group_name' => 'Legal', 'name' => 'Legal Catalog', 'method' => 'GET', 'path_pattern' => 'api/v1/legal/catalog', 'sort_order' => 150, 'docs_summary' => 'Lists legal documents available through the API.'],
            ['slug' => 'legal.catalog.show', 'group_name' => 'Legal', 'name' => 'Legal Catalog Detail', 'method' => 'GET', 'path_pattern' => 'api/v1/legal/catalog/*', 'sort_order' => 160, 'docs_summary' => 'Returns a single legal document definition.'],
            ['slug' => 'legal.pricing', 'group_name' => 'Legal', 'name' => 'Legal Pricing', 'method' => 'GET', 'path_pattern' => 'api/v1/legal/pricing/*', 'sort_order' => 170, 'docs_summary' => 'Returns current pricing for a legal document type.'],
            ['slug' => 'vtu.airtime', 'group_name' => 'VTU', 'name' => 'VTU Airtime', 'method' => 'POST', 'path_pattern' => 'api/v1/vtu/airtime', 'sort_order' => 180, 'docs_summary' => 'Purchases airtime through the API wallet.'],
            ['slug' => 'vtu.data', 'group_name' => 'VTU', 'name' => 'VTU Data', 'method' => 'POST', 'path_pattern' => 'api/v1/vtu/data', 'sort_order' => 190, 'docs_summary' => 'Purchases a data plan through the API wallet.'],
            ['slug' => 'vtu.cable', 'group_name' => 'VTU', 'name' => 'VTU Cable', 'method' => 'POST', 'path_pattern' => 'api/v1/vtu/cable', 'sort_order' => 200, 'docs_summary' => 'Pays for cable TV service through the API wallet.'],
            ['slug' => 'vtu.electricity', 'group_name' => 'VTU', 'name' => 'VTU Electricity', 'method' => 'POST', 'path_pattern' => 'api/v1/vtu/electricity', 'sort_order' => 210, 'docs_summary' => 'Pays electricity bills through the API wallet.'],
        ];
    }

    public function ensureDefaults(): void
    {
        if (! Schema::hasTable('developer_api_endpoints')) {
            return;
        }

        foreach (self::defaults() as $row) {
            $endpoint = DeveloperApiEndpoint::query()->firstOrNew(['slug' => $row['slug']]);
            $endpoint->group_name = $row['group_name'];
            $endpoint->name = $row['name'];
            $endpoint->method = strtoupper((string) $row['method']);
            $endpoint->path_pattern = $row['path_pattern'];
            $endpoint->sort_order = (int) $row['sort_order'];
            if (! $endpoint->exists) {
                $endpoint->is_enabled = true;
                $endpoint->docs_summary = $row['docs_summary'] ?? null;
            } elseif (empty($endpoint->docs_summary) && ! empty($row['docs_summary'])) {
                $endpoint->docs_summary = $row['docs_summary'];
            }
            $endpoint->save();
        }
    }

    public function all(): Collection
    {
        $this->ensureDefaults();

        if (! Schema::hasTable('developer_api_endpoints')) {
            return collect();
        }

        return DeveloperApiEndpoint::query()
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();
    }

    public function enabled(): Collection
    {
        return $this->all()->where('is_enabled', true)->values();
    }

    public function match(Request $request): ?DeveloperApiEndpoint
    {
        $method = strtoupper($request->method());
        $path = trim($request->path(), '/');

        foreach ($this->all() as $endpoint) {
            if (strtoupper((string) $endpoint->method) !== $method) {
                continue;
            }

            if (Str::is(trim((string) $endpoint->path_pattern, '/'), $path)) {
                return $endpoint;
            }
        }

        return null;
    }
}

