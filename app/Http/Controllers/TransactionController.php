<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Transaction;
use App\Models\VtuTransaction;
use Illuminate\Support\Facades\Auth;

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
        $tx = Transaction::where('user_email', Auth::user()->email)
            ->where('transaction_id', $transactionId)
            ->firstOrFail();

        $vtu = VtuTransaction::where('transaction_id', $transactionId)->first();

        return view('history.show', compact('tx', 'vtu'));
    }
}
