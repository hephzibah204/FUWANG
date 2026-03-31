<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\VerificationResult;
use App\Models\User;
use App\Services\WalletService;
use Illuminate\Http\Request;

class NINModificationAdminController extends Controller
{
    public function index()
    {
        $requests = VerificationResult::where('service_type', 'nin_modification')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('admin.verifications.nin_modifications.index', compact('requests'));
    }

    public function show($id)
    {
        $request = VerificationResult::findOrFail($id);
        return view('admin.verifications.nin_modifications.show', compact('request'));
    }

    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|string|in:pending,successful,failed',
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
            $price = \App\Models\SystemSetting::get('nin_modification_price', 2500);
            $wallet = app(WalletService::class);
            $wallet->failAndRefund($user, (float)$price, 'NIN Modification Rejected', $verification->reference_id);
        }

        return back()->with('success', 'NIN Modification status updated to ' . ucfirst($request->status));
    }
}
