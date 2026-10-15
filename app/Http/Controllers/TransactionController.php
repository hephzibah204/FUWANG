<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Transaction;
use App\Models\VerificationResult;
use App\Models\VtuTransaction;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;

class TransactionController extends Controller
{
    /**
     * Paginated HTML view of transaction history.
     */
    public function index()
    {
        $transactions = Transaction::where('user_email', Auth::user()->email)
            ->latest()
            ->paginate(20);

        return view('history.index', compact('transactions'));
    }

    /**
     * JSON endpoint used by the dashboard's AJAX recent-transactions widget.
     */
    public function json()
    {
        $transactions = Transaction::where('user_email', Auth::user()->email)
            ->latest()
            ->take(10)
            ->get()
            ->map(fn($t) => [
                'id'             => $t->id,
                'order_type'     => $t->order_type,
                'status'         => $t->status,
                'balance_before' => number_format($t->balance_before, 2),
                'balance_after'  => number_format($t->balance_after, 2),
                'transaction_id' => $t->transaction_id,
                'created_at'     => $t->created_at?->diffForHumans(),
            ]);

        return response()->json(['transactions' => $transactions]);
    }

    public function show(string $transactionId)
    {
        $user = Auth::user();
        $tx = Transaction::where('user_email', $user->email)
            ->where('transaction_id', $transactionId)
            ->firstOrFail();

        $vtu = null;
        try {
            if (Schema::hasTable('vtu_transactions')) {
                $vtu = VtuTransaction::where('transaction_id', $transactionId)->first();
            }
        } catch (\Throwable $e) {
            $vtu = null;
        }

        $verification = null;
        try {
            if (Schema::hasTable('verification_results')) {
                $verification = VerificationResult::where('user_id', $user->id)
                    ->where(function ($q) use ($transactionId) {
                        $q->where('reference_id', $transactionId)
                          ->orWhere('response_data->_transaction_id', $transactionId)
                          ->orWhere('response_data->transaction_id', $transactionId)
                          ->orWhere('response_data->_tx_id', $transactionId);
                    })->first();

                if (!$verification) {
                    $orderLower = strtolower($tx->order_type);
                    $serviceType = match (true) {
                        str_contains($orderLower, 'personalization') => 'personalization',
                        str_contains($orderLower, 'clearance') || str_contains($orderLower, 'ipe') => 'ipe_clearance',
                        str_contains($orderLower, 'modification') => 'nin_modification',
                        str_contains($orderLower, 'nin') => 'nin',
                        str_contains($orderLower, 'bvn') => 'bvn',
                        str_contains($orderLower, 'cac') => 'cac_verification',
                        str_contains($orderLower, 'passport') => 'passport_verification',
                        default => null,
                    };

                    if ($serviceType) {
                        $verification = VerificationResult::where('user_id', $user->id)
                            ->where('service_type', $serviceType)
                            ->whereBetween('created_at', [
                                $tx->created_at->copy()->subMinutes(15),
                                $tx->created_at->copy()->addMinutes(15)
                            ])
                            ->latest()
                            ->first();
                    }
                }
            }
        } catch (\Throwable $e) {
            $verification = null;
        }

        $reportUrl = null;
        if ($verification) {
            if ($verification->service_type === 'personalization') {
                $reportUrl = route('services.personalization.report', $verification->id);
            } elseif ($verification->service_type === 'ipe_clearance') {
                $reportUrl = route('services.clearance.report', $verification->id);
            } else {
                $reportUrl = route('services.verification.report', $verification->id);
            }
        }

        return view('history.show', compact('tx', 'vtu', 'verification', 'reportUrl'));
    }
}
