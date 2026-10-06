<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\VerificationResult;
use App\Models\User;
use App\Services\WalletService;
use Illuminate\Http\Request;

class IpeClearanceAdminController extends Controller
{
    public function index(Request $request)
    {
        $query = VerificationResult::whereIn('service_type', ['ipe_clearance', 'clearance', 'clearance_request'])
            ->with(['user:id,fullname,email'])
            ->orderBy('created_at', 'desc');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('q')) {
            $q = trim((string) $request->q);
            $query->where(function ($sub) use ($q) {
                $sub->where('reference_id', 'like', '%' . $q . '%')
                    ->orWhere('identifier', 'like', '%' . $q . '%')
                    ->orWhereHas('user', function ($uq) use ($q) {
                        $uq->where('fullname', 'like', '%' . $q . '%')
                           ->orWhere('email', 'like', '%' . $q . '%');
                    });
            });
        }

        $requests = $query->paginate(20)->withQueryString();

        return view('admin.verifications.ipe_clearance.index', compact('requests'));
    }

    public function show($id)
    {
        $request = VerificationResult::with(['user:id,fullname,email,phone'])->findOrFail($id);
        return view('admin.verifications.ipe_clearance.show', compact('request'));
    }

    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|string|in:pending,waiting_for_review,successful,failed',
            'admin_note' => 'nullable|string|max:1000'
        ]);

        $verification = VerificationResult::findOrFail($id);
        $user = User::findOrFail($verification->user_id);
        $oldStatus = $verification->status;

        $verification->update([
            'status' => $request->status,
            'admin_note' => $request->admin_note,
        ]);

        // Handle failure and refund if necessary
        if ($request->status === 'failed' && $oldStatus !== 'failed') {
            $price = \App\Models\VerificationPrice::first()->ipe_clearance_price ?? 400;
            $wallet = app(WalletService::class);
            $wallet->failAndRefund(
                $user,
                (float) $price,
                'IPE Clearance Rejected: ' . ($request->admin_note ?: 'Request unsuccessful'),
                $verification->reference_id
            );
        }

        return back()->with('success', 'IPE Clearance status updated to ' . ucfirst($request->status));
    }
}
