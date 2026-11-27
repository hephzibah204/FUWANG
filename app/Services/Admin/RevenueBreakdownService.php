<?php

namespace App\Services\Admin;

use App\Models\AgentLicenseTransaction;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Revenue split by source (owner request 2026-11-27): the admin must be
 * able to differentiate money made from agent license sales from VTU
 * sales, verification services and everything else.
 *
 * Sources of truth:
 *  - License sales come ONLY from agent_license_transactions. Wallet
 *    license payments also leave a wallet Transaction row (order_type
 *    'agent_license'); those rows are excluded from the wallet side so a
 *    license is never counted twice.
 *  - Everything else comes from wallet Transactions using the same
 *    successful-debit rules as the admin dashboard (money that actually
 *    left a wallet, funding/credit/refund rows excluded).
 */
class RevenueBreakdownService
{
    private array $statuses = ['success', 'successful', 'completed', 'approved', 'paid'];

    private string $debitExpr = "CASE
        WHEN balance_before > balance_after THEN (balance_before - balance_after)
        WHEN order_type NOT LIKE '%Funding%' AND order_type NOT LIKE '%Credit%' AND order_type NOT LIKE '%Refund%'
        THEN ABS(COALESCE(balance_after, 0) - COALESCE(balance_before, 0))
        ELSE 0
    END";

    private array $vtuKeywords = [
        'airtime', 'data purchase', 'data card', 'cable', 'electricity', 'betting',
        'internet subscription', 'waec', 'neco', 'nabteb', 'jamb', 'recharge',
        'epin', 'e-pin', 'bulk sms', 'vtu', 'result checker', 'registration pin',
    ];

    private array $verificationKeywords = [
        'nin', 'bvn', 'verification', 'verify', 'cac', 'passport', 'voter',
        'tin', 'plate', 'identity', 'clearance', 'validation', 'personalization',
        'driver', 'licence', 'birth certificate', 'attestation',
    ];

    public function categoryFor(string $orderType): string
    {
        $type = strtolower($orderType);

        foreach ($this->vtuKeywords as $keyword) {
            if (str_contains($type, $keyword)) {
                return 'vtu';
            }
        }

        foreach ($this->verificationKeywords as $keyword) {
            if (str_contains($type, $keyword)) {
                return 'verification';
            }
        }

        return 'other';
    }

    private function walletCategorySums(?Carbon $from): array
    {
        $sums = ['vtu' => 0.0, 'verification' => 0.0, 'other' => 0.0];

        if (!Schema::hasTable('transactions')) {
            return $sums;
        }

        $query = Transaction::whereIn(DB::raw('LOWER(status)'), $this->statuses)
            ->where('order_type', 'not like', '%License%')
            ->where(function ($q) {
                $q->where('balance_before', '>', DB::raw('balance_after'))
                    ->orWhere(function ($q2) {
                        $q2->where('order_type', 'not like', '%Funding%')
                            ->where('order_type', 'not like', '%Credit%')
                            ->where('order_type', 'not like', '%Refund%')
                            ->whereRaw('ABS(COALESCE(balance_after, 0) - COALESCE(balance_before, 0)) > 0');
                    });
            })
            ->selectRaw("order_type as service, SUM({$this->debitExpr}) as total")
            ->groupBy('order_type');

        if ($from) {
            $query->where('created_at', '>=', $from);
        }

        foreach ($query->get() as $row) {
            $sums[$this->categoryFor((string) $row->service)] += (float) $row->total;
        }

        return $sums;
    }

    private function licenseSums(?Carbon $from): float
    {
        if (!Schema::hasTable('agent_license_transactions')) {
            return 0.0;
        }

        $query = AgentLicenseTransaction::whereIn(DB::raw('LOWER(status)'), $this->statuses);
        if ($from) {
            $query->where('created_at', '>=', $from);
        }

        return (float) $query->sum('amount');
    }

    public function summary(): array
    {
        $periods = [
            'today' => Carbon::today(),
            'month' => Carbon::now()->startOfMonth(),
            'total' => null,
        ];

        $categories = [
            'licenses' => ['label' => 'Agent License Sales', 'today' => 0.0, 'month' => 0.0, 'total' => 0.0],
            'vtu' => ['label' => 'VTU Sales', 'today' => 0.0, 'month' => 0.0, 'total' => 0.0],
            'verification' => ['label' => 'Verification & Identity Services', 'today' => 0.0, 'month' => 0.0, 'total' => 0.0],
            'other' => ['label' => 'Other Platform Sales', 'today' => 0.0, 'month' => 0.0, 'total' => 0.0],
        ];

        foreach ($periods as $period => $from) {
            $categories['licenses'][$period] = $this->licenseSums($from);
            foreach ($this->walletCategorySums($from) as $key => $value) {
                $categories[$key][$period] = $value;
            }
        }

        // License detail: how the license money came in, and how many licenses.
        $licenseMethods = ['wallet' => 0.0, 'paystack' => 0.0, 'offline' => 0.0];
        $licenseCount = 0;
        if (Schema::hasTable('agent_license_transactions')) {
            $rows = AgentLicenseTransaction::whereIn(DB::raw('LOWER(status)'), $this->statuses)
                ->selectRaw('payment_method, SUM(amount) as total, COUNT(*) as cnt')
                ->groupBy('payment_method')
                ->get();
            foreach ($rows as $row) {
                $method = strtolower((string) $row->payment_method);
                $key = array_key_exists($method, $licenseMethods) ? $method : 'offline';
                $licenseMethods[$key] += (float) $row->total;
                $licenseCount += (int) $row->cnt;
            }
        }
        $categories['licenses']['methods'] = $licenseMethods;
        $categories['licenses']['count'] = $licenseCount;

        $totals = ['today' => 0.0, 'month' => 0.0, 'total' => 0.0];
        foreach ($categories as $category) {
            foreach (['today', 'month', 'total'] as $period) {
                $totals[$period] += $category[$period];
            }
        }

        $grandTotal = $totals['total'];
        foreach ($categories as $key => $category) {
            $categories[$key]['share'] = $grandTotal > 0 ? round(($category['total'] / $grandTotal) * 100, 1) : 0.0;
        }

        return ['categories' => $categories, 'totals' => $totals];
    }
}
