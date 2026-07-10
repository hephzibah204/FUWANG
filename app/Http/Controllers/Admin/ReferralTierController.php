<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ReferralTier;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ReferralTierController extends Controller
{
    public function index()
    {
        $tiers = ReferralTier::query()->orderBy('minimum_referrals')->get();

        return view('admin.referrals.tiers.index', compact('tiers'));
    }

    public function create()
    {
        return view('admin.referrals.tiers.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:50', Rule::unique('referral_tiers', 'name')],
            'minimum_referrals' => ['required', 'integer', 'min:0'],
            'commission_rate' => ['required', 'numeric', 'min:0', 'max:100'],
        ]);

        ReferralTier::query()->create([
            'name' => $request->input('name'),
            'minimum_referrals' => (int) $request->input('minimum_referrals'),
            'commission_rate' => (float) $request->input('commission_rate'),
        ]);

        return redirect()->route('admin.referral-tiers.index')->with('success', 'Referral tier created.');
    }

    public function edit(ReferralTier $referralTier)
    {
        return view('admin.referrals.tiers.edit', ['tier' => $referralTier]);
    }

    public function update(Request $request, ReferralTier $referralTier)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:50', Rule::unique('referral_tiers', 'name')->ignore($referralTier->id)],
            'minimum_referrals' => ['required', 'integer', 'min:0'],
            'commission_rate' => ['required', 'numeric', 'min:0', 'max:100'],
        ]);

        $referralTier->update([
            'name' => $request->input('name'),
            'minimum_referrals' => (int) $request->input('minimum_referrals'),
            'commission_rate' => (float) $request->input('commission_rate'),
        ]);

        return redirect()->route('admin.referral-tiers.index')->with('success', 'Referral tier updated.');
    }

    public function destroy(ReferralTier $referralTier)
    {
        $referralTier->delete();

        return redirect()->route('admin.referral-tiers.index')->with('success', 'Referral tier deleted.');
    }
}

