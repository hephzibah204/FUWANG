<?php

namespace App\Http\Controllers\Agent;

use App\Http\Controllers\Controller;
use App\Models\EnrollmentAgent;
use App\Models\PreApprovedAgent;
use App\Models\User;
use App\Services\AccountKycIdentityService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class AgentRegistrationController extends Controller
{
    public function showForm(Request $request)
    {
        $user = Auth::user();
        $agent = $user ? $user->enrollmentAgent : null;
        $initialType = $request->query('type', 'new');

        return view('agent.register', compact('agent', 'initialType'));
    }

    public function searchPreApproved(Request $request)
    {
        $q = trim((string) $request->query('q', ''));

        if (strlen($q) < 1) {
            return response()->json(['agents' => []]);
        }

        $tokens = array_filter(preg_split('/\s+/', $q));

        $query = PreApprovedAgent::query()->where('is_claimed', false);

        $query->where(function ($subQuery) use ($tokens, $q) {
            $subQuery->where('full_name', 'LIKE', "%{$q}%")
                ->orWhere('agent_code', 'LIKE', "%{$q}%")
                ->orWhere('email', 'LIKE', "%{$q}%")
                ->orWhere('phone_number', 'LIKE', "%{$q}%");

            if (count($tokens) > 1) {
                $subQuery->orWhere(function ($multiWordQuery) use ($tokens) {
                    foreach ($tokens as $token) {
                        $multiWordQuery->where(function ($tokenQuery) use ($token) {
                            $tokenQuery->where('full_name', 'LIKE', "%{$token}%")
                                ->orWhere('first_name', 'LIKE', "%{$token}%")
                                ->orWhere('last_name', 'LIKE', "%{$token}%")
                                ->orWhere('agent_code', 'LIKE', "%{$token}%")
                                ->orWhere('phone_number', 'LIKE', "%{$token}%");
                        });
                    }
                });
            }
        });

        $agents = $query
            ->orderBy('full_name', 'asc')
            ->limit(15)
            ->get(['id', 'agent_code', 'first_name', 'last_name', 'full_name', 'email', 'phone_number'])
            ->map(function ($agent) {
                $name = $agent->full_name ?: trim($agent->first_name . ' ' . $agent->last_name);
                return [
                    'id' => $agent->id,
                    'agent_code' => $agent->agent_code,
                    'first_name' => $agent->first_name,
                    'last_name' => $agent->last_name,
                    'full_name' => $name,
                    'email' => $agent->email,
                    'phone_number' => $agent->phone_number,
                    'fast_track_eligible' => true,
                ];
            });

        return response()->json(['agents' => $agents]);
    }

    public function store(Request $request, AccountKycIdentityService $kycService)
    {
        $validated = $request->validate([
            'agent_type' => ['required', 'string', 'in:existing,new'],
            'company_agent_code' => ['required_if:agent_type,existing', 'nullable', 'string', 'max:50'],
            'full_name' => ['required', 'string', 'max:255'],
            'phone_number' => ['required', 'string', 'max:20'],
            'email' => ['required', 'email', 'max:255'],
            'bvn' => ['required', 'string', 'digits:11'],
            'nin' => ['required', 'string', 'digits:11'],
            'state' => ['required', 'string', 'max:100'],
            'residential_address' => ['required', 'string', 'max:1000'],
            'office_address' => ['required', 'string', 'max:1000'],
            'has_machine' => ['required', 'boolean'],
            'machine_imei' => ['required_if:has_machine,1', 'nullable', 'string', 'max:100'],
        ]);

        // Find or create User record
        $user = Auth::user();
        if (!$user) {
            $user = User::where('email', $validated['email'])->first();
            if (!$user) {
                $user = User::create([
                    'fullname' => $validated['full_name'],
                    'number' => $validated['phone_number'],
                    'email' => $validated['email'],
                    'username' => Str::slug(explode('@', $validated['email'])[0]) . '_' . rand(1000, 9999),
                    'password' => Hash::make(Str::random(16)),
                    'user_status' => 'active',
                    'email_verified_at' => now(),
                ]);
            }
            Auth::login($user);
        }

        if ($user->enrollmentAgent && $user->enrollmentAgent->isApproved()) {
            return redirect()->route('agent.dashboard')->with('info', 'You are already an approved enrollment agent.');
        }

        $ninVerificationMeta = null;
        $ninVerified = false;
        $ninServerStatus = 'pending_fallback';
        $finalFullName = $validated['full_name'];

        try {
            // Attempt real-time NIN verification using existing KYC stack
            $verificationRes = $kycService->verifyNinForTier(
                $user,
                $validated['nin']
            );

            if (isset($verificationRes['status']) && strtolower((string)$verificationRes['status']) === 'success') {
                $ninVerified = true;
                $ninServerStatus = 'verified';
                $ninVerificationMeta = $verificationRes;

                // Extract official verified NIMC name if returned by provider
                $verifiedName = trim(($verificationRes['data']['firstname'] ?? $verificationRes['firstname'] ?? '') . ' ' . ($verificationRes['data']['lastname'] ?? $verificationRes['lastname'] ?? ''));
                if (!empty($verifiedName)) {
                    $finalFullName = $verifiedName;
                    $user->update(['fullname' => $finalFullName]);
                }
            } else {
                // Non-blocking fallback: if server returns error or unconfigured, allow agent registration to proceed
                $ninServerStatus = 'pending_fallback';
                $ninVerificationMeta = [
                    'status' => 'pending_fallback',
                    'reason' => $verificationRes['message'] ?? 'NIN server gateway unreachable during registration',
                    'timestamp' => now()->toIso8601String(),
                ];
            }
        } catch (\Throwable $e) {
            Log::warning('NIN verification server down during agent pre-login registration', [
                'user_id' => $user->id,
                'nin' => $validated['nin'],
                'error' => $e->getMessage(),
            ]);

            // Server down fallback — DO NOT BLOCK REGISTRATION
            $ninServerStatus = 'pending_fallback';
            $ninVerificationMeta = [
                'status' => 'pending_fallback',
                'reason' => 'Server exception: ' . $e->getMessage(),
                'timestamp' => now()->toIso8601String(),
            ];
        }

        $agent = EnrollmentAgent::updateOrCreate(
            ['user_id' => $user->id],
            [
                'agent_type' => $validated['agent_type'],
                'company_agent_code' => $validated['company_agent_code'] ?? null,
                'is_fast_tracked' => ($validated['agent_type'] === 'existing'),
                'full_name' => $finalFullName,
                'phone_number' => $validated['phone_number'],
                'state' => $validated['state'],
                'residential_address' => $validated['residential_address'],
                'office_address' => $validated['office_address'],
                'bvn' => $validated['bvn'],
                'nin' => $validated['nin'],
                'nin_verified' => $ninVerified,
                'nin_server_status' => $ninServerStatus,
                'nin_verification_meta' => $ninVerificationMeta,
                'has_machine' => (bool)$validated['has_machine'],
                'machine_imei' => $validated['machine_imei'] ?? 'NOT_ASSIGNED',
                'status' => 'pending',
                'onboarding_step' => 'basic_info',
            ]
        );

        // Mark pre-approved record as claimed if agent code or full name matches
        if ($validated['agent_type'] === 'existing') {
            $code = $validated['company_agent_code'] ?? null;
            $name = $finalFullName ?: ($validated['full_name'] ?? null);

            $preApproved = PreApprovedAgent::where('is_claimed', false)
                ->where(function ($q) use ($code, $name) {
                    if (!empty($code)) {
                        $q->where('agent_code', $code);
                    }
                    if (!empty($name)) {
                        $q->orWhere('full_name', $name);
                    }
                })
                ->first();

            if ($preApproved) {
                $preApproved->update([
                    'is_claimed' => true,
                    'claimed_at' => now(),
                    'claimed_by_user_id' => $user->id,
                ]);

                if (empty($agent->company_agent_code)) {
                    $agent->update(['company_agent_code' => $preApproved->agent_code]);
                }
            }
        }

        $msg = $ninServerStatus === 'verified'
            ? 'Basic registration complete! Your NIN was verified. Please upload your Agency KYC documents to complete your application.'
            : 'Basic registration complete! (NIN server is currently offline; your NIN will be verified automatically in background). Please upload your Agency KYC documents.';

        return redirect()->route('agent.onboarding.index')->with('success', $msg);
    }
}
