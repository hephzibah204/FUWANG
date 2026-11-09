<?php

namespace App\Http\Controllers\Account;

use App\Http\Controllers\Controller;
use App\Models\VerificationResult;
use App\Services\AccountKycIdentityService;
use App\Support\UserKycIdentifiers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class KycVerificationController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $svc = app(AccountKycIdentityService::class);
        $latestNin = $this->latestSuccessful($user->id, AccountKycIdentityService::SERVICE_NIN);
        $latestBvn = $this->latestSuccessful($user->id, AccountKycIdentityService::SERVICE_BVN);
        $hasTier2Identity = $this->hasAnyTier2Identity($user);

        return view('account.kyc.index', [
            'ninPrice' => $svc->ninPrice(),
            'bvnPrice' => $svc->bvnPrice(),
            'hasAccountNin' => (bool) $latestNin || $hasTier2Identity,
            'hasAccountBvn' => (bool) $latestBvn || $hasTier2Identity,
            'ninVerifiedProvider' => $latestNin?->provider_name,
            'bvnVerifiedProvider' => $latestBvn?->provider_name,
            'hasTier2Identity' => $hasTier2Identity,
        ]);
    }

    public function ninForm()
    {
        $user = Auth::user();
        $svc = app(AccountKycIdentityService::class);
        $hasTier2Identity = $this->hasAnyTier2Identity($user);

        return view('account.kyc.nin', [
            'user' => $user,
            'price' => $svc->ninPrice(),
            'hasAccountNin' => $this->hasSuccessful($user->id, AccountKycIdentityService::SERVICE_NIN) || $hasTier2Identity,
            'hasTier2Identity' => $hasTier2Identity,
        ]);
    }

    public function ninSubmit(Request $request)
    {
        $user = Auth::user();
        if ($this->hasAnyTier2Identity($user)) {
            return $request->expectsJson()
                ? response()->json(['status' => false, 'message' => 'Tier 2 is already completed on your account. No further BVN/NIN verification is required.'])
                : back()->withErrors(['nin' => 'Tier 2 is already completed on your account. No further BVN/NIN verification is required.']);
        }

        $rules = [
            'nin' => ['required', 'string', 'regex:/^\d{11}$/'],
        ];
        if (empty($user->number)) {
            $rules['phone_number'] = ['required', 'string', 'regex:/^[0-9]{10,15}$/'];
        } else {
            $rules['phone_number'] = ['nullable', 'string', 'regex:/^[0-9]{10,15}$/'];
        }

        $validated = $request->validate($rules, [
            'phone_number.required' => 'A valid phone number is required by banking partners to issue your Tier 2 virtual account.',
            'phone_number.regex' => 'Please enter a valid phone number (10 to 15 digits).',
        ]);

        if (!empty($validated['phone_number'])) {
            $cleaned = preg_replace('/\D+/', '', (string) $validated['phone_number']);
            if ($user->number !== $cleaned) {
                $user->number = $cleaned;
                $user->save();
            }
        }

        $svc = app(AccountKycIdentityService::class);

        $run = function () use ($svc, $user, $request) {
            return $svc->verifyNinForTier($user, (string) $request->input('nin'));
        };

        try {
            $result = $run();
        } catch (\Throwable $e) {
            return $request->expectsJson()
                ? response()->json(['status' => false, 'message' => $e->getMessage()])
                : back()->withErrors(['nin' => $e->getMessage()])->withInput();
        }

        if ($request->expectsJson()) {
            return response()->json($result);
        }

        return redirect()->route('account.kyc.index')->with('status', $result['message'] ?? 'NIN verified.');
    }

    public function bvnForm()
    {
        $user = Auth::user();
        $svc = app(AccountKycIdentityService::class);
        $hasTier2Identity = $this->hasAnyTier2Identity($user);

        return view('account.kyc.bvn', [
            'user' => $user,
            'price' => $svc->bvnPrice(),
            'hasAccountBvn' => $this->hasSuccessful($user->id, AccountKycIdentityService::SERVICE_BVN) || $hasTier2Identity,
            'hasTier2Identity' => $hasTier2Identity,
        ]);
    }

    public function bvnSubmit(Request $request)
    {
        $user = Auth::user();
        if ($this->hasAnyTier2Identity($user)) {
            return $request->expectsJson()
                ? response()->json(['status' => false, 'message' => 'Tier 2 is already completed on your account. No further BVN/NIN verification is required.'])
                : back()->withErrors(['bvn' => 'Tier 2 is already completed on your account. No further BVN/NIN verification is required.']);
        }

        $rules = [
            'bvn' => ['required', 'string', 'regex:/^\d{11}$/'],
        ];
        if (empty($user->number)) {
            $rules['phone_number'] = ['required', 'string', 'regex:/^[0-9]{10,15}$/'];
        } else {
            $rules['phone_number'] = ['nullable', 'string', 'regex:/^[0-9]{10,15}$/'];
        }

        $validated = $request->validate($rules, [
            'phone_number.required' => 'A valid phone number is required by banking partners to issue your Tier 2 virtual account.',
            'phone_number.regex' => 'Please enter a valid phone number (10 to 15 digits).',
        ]);

        if (!empty($validated['phone_number'])) {
            $cleaned = preg_replace('/\D+/', '', (string) $validated['phone_number']);
            if ($user->number !== $cleaned) {
                $user->number = $cleaned;
                $user->save();
            }
        }

        $svc = app(AccountKycIdentityService::class);

        $run = function () use ($svc, $user, $request) {
            return $svc->verifyBvnForTier($user, (string) $request->input('bvn'));
        };

        try {
            $result = $run();
        } catch (\Throwable $e) {
            return $request->expectsJson()
                ? response()->json(['status' => false, 'message' => $e->getMessage()])
                : back()->withErrors(['bvn' => $e->getMessage()])->withInput();
        }

        if ($request->expectsJson()) {
            return response()->json($result);
        }

        return redirect()->route('account.kyc.index')->with('status', $result['message'] ?? 'BVN verified.');
    }

    private function hasSuccessful(int $userId, string $serviceType): bool
    {
        return VerificationResult::query()
            ->where('user_id', $userId)
            ->where('service_type', $serviceType)
            ->where('status', 'success')
            ->exists();
    }

    private function latestSuccessful(int $userId, string $serviceType): ?VerificationResult
    {
        return VerificationResult::query()
            ->where('user_id', $userId)
            ->where('service_type', $serviceType)
            ->where('status', 'success')
            ->latest('id')
            ->first();
    }

    private function hasAnyTier2Identity($user): bool
    {
        return UserKycIdentifiers::preferredPaymentIdentity($user) !== null;
    }
}
