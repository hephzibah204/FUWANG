<?php

namespace App\Http\Controllers\Agent;

use App\Http\Controllers\Controller;
use App\Models\EnrollmentAgent;
use App\Models\PreApprovedAgent;
use App\Models\User;
use App\Services\AccountKycIdentityService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
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

        if (strlen($q) < 3) {
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

                // Mask email: j***@domain.com
                $maskedEmail = $agent->email;
                if ($agent->email && str_contains($agent->email, '@')) {
                    [$emailUser, $domain] = explode('@', $agent->email, 2);
                    $maskedEmail = (strlen($emailUser) > 1 ? substr($emailUser, 0, 1) : 'a') . '***@' . $domain;
                }

                // Mask phone: 080****5678
                $maskedPhone = $agent->phone_number;
                if ($agent->phone_number && strlen($agent->phone_number) >= 7) {
                    $maskedPhone = substr($agent->phone_number, 0, 3) . '****' . substr($agent->phone_number, -4);
                }

                return [
                    'id' => $agent->id,
                    'agent_code' => $agent->agent_code,
                    'first_name' => $agent->first_name,
                    'last_name' => $agent->last_name,
                    'full_name' => $name,
                    'email' => $maskedEmail,
                    'phone_number' => $maskedPhone,
                    'fast_track_eligible' => true,
                ];
            });

        return response()->json(['agents' => $agents]);
    }

    public function sendClaimOtp(Request $request)
    {
        $validated = $request->validate([
            'company_agent_code' => ['required', 'string', 'max:50'],
        ]);

        $preApproved = PreApprovedAgent::where('agent_code', $validated['company_agent_code'])
            ->where('is_claimed', false)
            ->first();

        if (!$preApproved) {
            return response()->json([
                'ok' => false,
                'message' => 'Profile not found or already claimed.',
            ], 422);
        }

        if (empty($preApproved->email)) {
            return response()->json([
                'ok' => false,
                'message' => 'This profile does not have a registered email address for OTP dispatch.',
            ], 422);
        }

        $otp = (string) random_int(100000, 999999);
        session([
            'claim_otp_code_' . $preApproved->agent_code => $otp,
            'claim_otp_expires_' . $preApproved->agent_code => now()->addMinutes(15),
        ]);

        try {
            \Illuminate\Support\Facades\Mail::to($preApproved->email)->send(new \App\Mail\AgentClaimVerificationMail($preApproved, $otp));
        } catch (\Throwable $e) {
            Log::error('Failed to send claim verification email: ' . $e->getMessage());
            return response()->json([
                'ok' => false,
                'message' => 'Unable to send verification email. Please contact support.',
            ], 500);
        }

        [$emailUser, $domain] = explode('@', $preApproved->email, 2);
        $maskedEmail = (strlen($emailUser) > 1 ? substr($emailUser, 0, 1) : 'a') . '***@' . $domain;

        return response()->json([
            'ok' => true,
            'message' => "Verification OTP sent to {$maskedEmail}.",
        ]);
    }

    public function store(Request $request, AccountKycIdentityService $kycService)
    {
        $agentType = $request->input('agent_type', 'new');
        
        $rules = [
            'agent_type' => ['required', 'string', 'in:existing,new'],
            'company_agent_code' => ['required_if:agent_type,existing', 'nullable', 'string', 'max:50'],
            'claim_otp' => ['required_if:agent_type,existing', 'nullable', 'string', 'digits:6'],
            'full_name' => ['required', 'string', 'max:255'],
            'phone_number' => ['required', 'string', 'max:20'],
            'email' => ['required', 'email', 'max:255'],
            'bvn' => ['required', 'string', 'digits:11'],
            'nin' => ['required', 'string', 'digits:11'],
            'state' => ['required', 'string', 'max:100'],
            'residential_address' => ['required', 'string', 'max:1000'],
            'office_address' => ['required', 'string', 'max:1000'],
            'has_machine' => ['required', 'boolean'],
            'machine_imei' => [$agentType === 'existing' ? 'required' : 'required_if:has_machine,1', 'nullable', 'string', 'max:100'],
        ];

        $validated = $request->validate($rules);

        // All existing agents must have machine set to true
        if ($validated['agent_type'] === 'existing') {
            $validated['has_machine'] = true;
        }

        // If existing agent profile selected, verify OTP and claim atomic pre-approved roster profile
        $preApprovedRecord = null;
        if ($validated['agent_type'] === 'existing') {
            $code = $validated['company_agent_code'] ?? null;
            $email = $validated['email'] ?? null;
            $phone = $validated['phone_number'] ?? null;

            $preApprovedQuery = PreApprovedAgent::where('is_claimed', false);
            if (!empty($code)) {
                $preApprovedRecord = $preApprovedQuery->where('agent_code', $code)->first();
            } elseif (!empty($email) || !empty($phone)) {
                $preApprovedRecord = $preApprovedQuery->where(function ($q) use ($email, $phone) {
                    if (!empty($email)) {
                        $q->where('email', $email);
                    }
                    if (!empty($phone)) {
                        $q->orWhere('phone_number', $phone);
                    }
                })->first();
            }

            if (!$preApprovedRecord) {
                // Check if account was already claimed by someone else
                $claimedQuery = PreApprovedAgent::where('is_claimed', true);
                if (!empty($code)) {
                    $alreadyClaimed = $claimedQuery->where('agent_code', $code)->first();
                } else {
                    $alreadyClaimed = $claimedQuery->where(function ($q) use ($email, $phone) {
                        if (!empty($email)) {
                            $q->where('email', $email);
                        }
                        if (!empty($phone)) {
                            $q->orWhere('phone_number', $phone);
                        }
                    })->first();
                }

                if ($alreadyClaimed) {
                    return back()
                        ->withInput()
                        ->withErrors(['company_agent_code' => 'This pre-approved agent profile has already been claimed by another registered user account. Multi-claiming is prohibited.']);
                }
            }

            // Verify Email Claim OTP if preApprovedRecord exists
            if ($preApprovedRecord) {
                $savedOtp = session('claim_otp_code_' . $preApprovedRecord->agent_code);
                $expiresAt = session('claim_otp_expires_' . $preApprovedRecord->agent_code);

                if (!$savedOtp || !$expiresAt || now()->greaterThan($expiresAt) || $savedOtp !== $validated['claim_otp']) {
                    return back()
                        ->withInput()
                        ->withErrors(['claim_otp' => 'Invalid or expired email verification OTP. Please click "Send Email OTP" to receive a fresh verification code.']);
                }
            }

            // Lock prefilled info from preApprovedRecord if found
            if ($preApprovedRecord) {
                $validated['company_agent_code'] = $preApprovedRecord->agent_code;
                $validated['full_name'] = $preApprovedRecord->full_name ?: trim($preApprovedRecord->first_name . ' ' . $preApprovedRecord->last_name);
                if (!empty($preApprovedRecord->email)) {
                    $validated['email'] = $preApprovedRecord->email;
                }
                if (!empty($preApprovedRecord->phone_number)) {
                    $validated['phone_number'] = $preApprovedRecord->phone_number;
                }
            }
        }

        // Find or create User record
        $user = Auth::user();
        if (!$user) {
            $existingUser = User::where('email', $validated['email'])->first();
            if ($existingUser) {
                $loginUrl = route('login') . '?email=' . urlencode($validated['email']);
                return back()
                    ->withInput()
                    ->withErrors(['email' => "An account with this email address already exists. Please <a href='{$loginUrl}' class='alert-link fw-bold text-decoration-underline'>log in here</a> first to link your enrollment agent profile."]);
            }

            $user = User::create([
                'fullname' => $validated['full_name'],
                'number' => $validated['phone_number'],
                'email' => $validated['email'],
                'username' => User::generateUniqueUsername($validated['email']),
                'password' => Hash::make(Str::random(16)),
                'user_status' => 'active',
                'email_verified_at' => now(),
            ]);
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
                'machine_imei' => (bool)$validated['has_machine'] ? ($validated['machine_imei'] ?? null) : null,
                'status' => 'pending',
                'onboarding_step' => 'basic_info',
            ]
        );

        // Mark pre-approved record as claimed atomically inside transaction
        if ($validated['agent_type'] === 'existing') {
            $code = $validated['company_agent_code'] ?? null;
            $email = $validated['email'] ?? null;
            $phone = $validated['phone_number'] ?? null;

            DB::transaction(function () use ($code, $email, $phone, $user, $agent) {
                $preApprovedQuery = PreApprovedAgent::where('is_claimed', false);

                if (!empty($code)) {
                    $preApproved = $preApprovedQuery->where('agent_code', $code)->lockForUpdate()->first();
                } elseif (!empty($email) || !empty($phone)) {
                    $preApproved = $preApprovedQuery->where(function ($q) use ($email, $phone) {
                        if (!empty($email)) {
                            $q->where('email', $email);
                        }
                        if (!empty($phone)) {
                            $q->orWhere('phone_number', $phone);
                        }
                    })->lockForUpdate()->first();
                } else {
                    $preApproved = null;
                }

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
            });
        }

        $msg = $ninServerStatus === 'verified'
            ? 'Basic registration complete! Your NIN was verified. Please upload your Agency KYC documents to complete your application.'
            : 'Basic registration complete! (NIN server is currently offline; your NIN will be verified automatically in background). Please upload your Agency KYC documents.';

        return redirect()->route('agent.onboarding.index')->with('success', $msg);
    }
}
