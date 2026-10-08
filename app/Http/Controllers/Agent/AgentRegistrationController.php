<?php

namespace App\Http\Controllers\Agent;

use App\Http\Controllers\Controller;
use App\Models\EmailLog;
use App\Models\EnrollmentAgent;
use App\Models\PreApprovedAgent;
use App\Models\User;
use App\Services\AccountKycIdentityService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class AgentRegistrationController extends Controller
{
    public function showForm(Request $request)
    {
        $user = Auth::user();
        $agent = $user ? $user->enrollmentAgent : null;
        $initialType = $request->query('type', 'new');

        $preApprovedAgents = PreApprovedAgent::where('is_claimed', false)
            ->select('agent_code', 'full_name', 'email', 'phone_number')
            ->get()
            ->map(function($a) {
                $maskedEmail = $this->maskEmail($a->email);
                $maskedPhone = $this->maskPhone($a->phone_number);

                return [
                    'agent_code' => $a->agent_code,
                    'full_name' => $a->full_name,
                    'email' => $maskedEmail,
                    'phone_number' => $maskedPhone,
                    'masked_email' => $maskedEmail,
                    'masked_phone' => $maskedPhone
                ];
            });

        return view('agent.register', compact('agent', 'initialType', 'preApprovedAgents'));
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
                $maskedEmail = $this->maskEmail($agent->email);
                $maskedPhone = $this->maskPhone($agent->phone_number);

                return [
                    'id' => $agent->id,
                    'agent_code' => $agent->agent_code,
                    'first_name' => $agent->first_name,
                    'last_name' => $agent->last_name,
                    'full_name' => $name,
                    'email' => $maskedEmail,
                    'phone_number' => $maskedPhone,
                    'masked_email' => $maskedEmail,
                    'masked_phone' => $maskedPhone,
                    'fast_track_eligible' => true,
                ];
            });

        return response()->json(['agents' => $agents]);
    }

    private function maskEmail(?string $email): string
    {
        if (empty($email) || !str_contains($email, '@')) {
            return '***@***.***';
        }
        [$user, $domain] = explode('@', $email, 2);
        $len = strlen($user);
        if ($len <= 2) {
            $maskedUser = substr($user, 0, 1) . '***';
        } else {
            $maskedUser = substr($user, 0, 1) . '***' . substr($user, -1);
        }
        return $maskedUser . '@' . $domain;
    }

    private function maskPhone(?string $phone): string
    {
        if (empty($phone)) {
            return '**********';
        }
        $clean = preg_replace('/\s+/', '', $phone);
        $len = strlen($clean);
        if ($len < 7) {
            return substr($clean, 0, 2) . '****';
        }
        return substr($clean, 0, 3) . '****' . substr($clean, -4);
    }

    public function sendClaimOtp(Request $request)
    {
        $validated = $request->validate([
            'company_agent_code' => ['required', 'string', 'max:50'],
        ]);

        $code = strtoupper(trim($validated['company_agent_code']));

        $preApproved = PreApprovedAgent::where('agent_code', $code)
            ->where('is_claimed', false)
            ->first();

        if (!$preApproved) {
            return response()->json([
                'ok' => false,
                'message' => 'Profile not found or already claimed.',
            ], 422);
        }

        $targetEmail = trim((string)$preApproved->email);
        if (empty($targetEmail)) {
            return response()->json([
                'ok' => false,
                'message' => 'This profile does not have a registered email address for OTP dispatch.',
            ], 422);
        }

        $otp = (string) random_int(100000, 999999);
        $expiresAt = now()->addMinutes(15);

        // Store OTP in both Session and Cache to prevent session-loss issues
        session([
            'claim_otp_code_' . $preApproved->agent_code => $otp,
            'claim_otp_expires_' . $preApproved->agent_code => $expiresAt,
        ]);
        Cache::put('agent_claim_otp_' . $preApproved->agent_code, [
            'code' => $otp,
            'expires_at' => $expiresAt,
        ], 900); // 15 minutes

        $mailSent = false;
        $activeMailerUsed = config('mail.default', 'resend_smtp');
        $dispatchError = null;

        // In testing environment, use standard Mail facade so unit tests with Mail::fake() work seamlessly
        if (app()->environment('testing')) {
            try {
                Mail::to($targetEmail)->send(new \App\Mail\AgentClaimVerificationMail($preApproved, $otp));
                $mailSent = true;
                $activeMailerUsed = 'testing_mailer';
            } catch (\Throwable $e) {
                $dispatchError = $e->getMessage();
            }
        } else {
            // Attempt 1: Direct Resend HTTPS API (Fastest ~200ms, completely immune to SMTP port blocks/delays)
            $resendKey = null;
            if (Schema::hasTable('api_centers')) {
                $resendKey = DB::table('api_centers')->value('resend_api_key');
            }
            if (empty($resendKey)) {
                $resendKey = env('MAIL_PASSWORD');
            }

            if (!empty($resendKey) && str_starts_with($resendKey, 're_')) {
                try {
                    $htmlBody = view('emails.agent.claim_verification', [
                        'agent' => $preApproved,
                        'otp' => $otp,
                    ])->render();

                    $textBody = view('emails.agent.claim_verification_text', [
                        'agent' => $preApproved,
                        'otp' => $otp,
                    ])->render();

                    $fromAddress = config('mail.from.address', 'support@fuwa.ng');
                    $fromName = config('mail.from.name', 'Fuwa.NG');

                    $response = Http::timeout(8)
                        ->withToken($resendKey)
                        ->post('https://api.resend.com/emails', [
                            'from' => "{$fromName} <{$fromAddress}>",
                            'to' => [$targetEmail],
                            'subject' => "Your OTP is {$otp} — Verify Agent Profile Claim ({$preApproved->agent_code})",
                            'html' => $htmlBody,
                            'text' => $textBody,
                        ]);

                    if ($response->successful()) {
                        $mailSent = true;
                        $activeMailerUsed = 'resend_http_api';
                        $resendId = $response->json('id') ?? 'sent';
                        Log::info("Agent claim OTP successfully sent via resend_http_api to {$targetEmail} for agent {$preApproved->agent_code} (Resend ID: {$resendId})");
                    } else {
                        $dispatchError = "Resend API returned status {$response->status()}: " . $response->body();
                        Log::warning("Resend HTTP API failed for agent claim OTP: " . $dispatchError);
                    }
                } catch (\Throwable $eHttp) {
                    $dispatchError = "Resend HTTP error: " . $eHttp->getMessage();
                    Log::warning("Resend HTTP API exception: " . $dispatchError);
                }
            }

            // Attempt 2: Configured Laravel default mailer (Resend SMTP / active)
            if (!$mailSent) {
                try {
                    Mail::to($targetEmail)->send(new \App\Mail\AgentClaimVerificationMail($preApproved, $otp));
                    $mailSent = true;
                    $activeMailerUsed = config('mail.default', 'resend_smtp');
                    Log::info("Agent claim OTP sent via {$activeMailerUsed} to {$targetEmail} for agent {$preApproved->agent_code}");
                } catch (\Throwable $e1) {
                    $dispatchError = "Primary mailer error: " . $e1->getMessage();
                    Log::warning("Primary mailer ({$activeMailerUsed}) failed for agent claim OTP: " . $dispatchError);

                    // Attempt 3: Explicit Mailtrap SMTP failover
                    try {
                        Mail::mailer('mailtrap_smtp')->to($targetEmail)->send(new \App\Mail\AgentClaimVerificationMail($preApproved, $otp));
                        $mailSent = true;
                        $activeMailerUsed = 'mailtrap_smtp';
                        Log::info("Agent claim OTP sent via fallback mailtrap_smtp to {$targetEmail} for agent {$preApproved->agent_code}");
                    } catch (\Throwable $e2) {
                        $dispatchError = "All mail transports failed. Primary: " . $e1->getMessage() . " | Mailtrap: " . $e2->getMessage();
                        Log::error("All mail transports failed to send claim OTP to {$targetEmail}: " . $dispatchError);
                    }
                }
            }
        }

        // Log into email_logs table if available
        if (Schema::hasTable('email_logs')) {
            try {
                EmailLog::create([
                    'id' => (string) Str::uuid(),
                    'to_email' => $targetEmail,
                    'type' => 'agent_claim_otp',
                    'subject' => "Your OTP is {$otp} — Verify Agent Profile Claim ({$preApproved->agent_code})",
                    'status' => $mailSent ? 'sent' : 'failed',
                    'metadata' => [
                        'agent_code' => $preApproved->agent_code,
                        'full_name' => $preApproved->full_name,
                        'mailer' => $activeMailerUsed,
                        'error' => $dispatchError,
                    ],
                    'sent_at' => $mailSent ? now() : null,
                    'failed_at' => !$mailSent ? now() : null,
                ]);
            } catch (\Throwable $logEx) {
                // Ignore log save errors
            }
        }

        if (!$mailSent) {
            return response()->json([
                'ok' => false,
                'message' => 'Unable to dispatch verification email at this moment. ' . ($dispatchError ? "({$dispatchError})" : 'Please try again shortly.'),
            ], 500);
        }

        [$emailUser, $domain] = explode('@', $targetEmail, 2);
        $maskedEmail = (strlen($emailUser) > 1 ? substr($emailUser, 0, 1) : 'a') . '***@' . $domain;

        return response()->json([
            'ok' => true,
            'message' => "Verification OTP sent to {$maskedEmail}.",
            'masked_email' => $maskedEmail,
            'cooldown_seconds' => 60,
        ]);
    }

    public function store(Request $request, AccountKycIdentityService $kycService)
    {
        if ($request->filled('company_agent_code')) {
            $request->merge(['company_agent_code' => strtoupper(trim((string)$request->input('company_agent_code')))]);
        }
        if ($request->filled('claim_otp')) {
            $request->merge(['claim_otp' => preg_replace('/\D/', '', (string)$request->input('claim_otp'))]);
        }

        $agentType = $request->input('agent_type', 'new');

        // If existing agent pre-approved profile is being submitted with masked email or phone,
        // hydrate them with authentic pre-approved credentials before validation.
        if ($agentType === 'existing' && $request->filled('company_agent_code')) {
            $preApprovedLookup = PreApprovedAgent::where('agent_code', $request->input('company_agent_code'))->first();
            if ($preApprovedLookup) {
                $submittedEmail = (string)$request->input('email');
                $submittedPhone = (string)$request->input('phone_number');

                if ((empty($submittedEmail) || str_contains($submittedEmail, '*')) && !empty($preApprovedLookup->email)) {
                    $request->merge(['email' => $preApprovedLookup->email]);
                }
                if ((empty($submittedPhone) || str_contains($submittedPhone, '*')) && !empty($preApprovedLookup->phone_number)) {
                    $request->merge(['phone_number' => $preApprovedLookup->phone_number]);
                }
            }
        }
        
        $rules = [
            'agent_type' => ['required', 'string', 'in:existing,new'],
            'company_agent_code' => ['required_if:agent_type,existing', 'nullable', 'string', 'max:50'],
            'claim_otp' => ['nullable', 'string', 'digits:6'],
            'full_name' => ['required', 'string', 'max:255'],
            'phone_number' => ['required', 'string', 'max:20'],
            'email' => ['required', 'email', 'max:255'],
            'password' => [Auth::check() ? 'nullable' : 'required', 'nullable', 'string', 'min:8', 'confirmed'],
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
                if (empty($validated['claim_otp'])) {
                    return back()
                        ->withInput()
                        ->withErrors(['claim_otp' => 'Profile Claim Verification OTP is required to claim this pre-approved agent profile. Please click "Send Email OTP" to verify account ownership.']);
                }

                $savedOtp = session('claim_otp_code_' . $preApprovedRecord->agent_code);
                $expiresAt = session('claim_otp_expires_' . $preApprovedRecord->agent_code);

                if (!$savedOtp) {
                    $cached = Cache::get('agent_claim_otp_' . $preApprovedRecord->agent_code);
                    if ($cached && is_array($cached)) {
                        $savedOtp = $cached['code'] ?? null;
                        $expiresAt = $cached['expires_at'] ?? null;
                    }
                }

                if (!$savedOtp || !$expiresAt || now()->greaterThan($expiresAt) || (string)$savedOtp !== (string)$validated['claim_otp']) {
                    return back()
                        ->withInput()
                        ->withErrors(['claim_otp' => 'Invalid or expired email verification OTP. Please click "Send Email OTP" to receive a fresh verification code.']);
                }

                // Clear OTP once successfully validated
                session()->forget([
                    'claim_otp_code_' . $preApprovedRecord->agent_code,
                    'claim_otp_expires_' . $preApprovedRecord->agent_code,
                ]);
                Cache::forget('agent_claim_otp_' . $preApprovedRecord->agent_code);
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
                $loginUrl = route('login', [
                    'email' => $validated['email'],
                    'redirect' => route('agent.register', ['type' => $validated['agent_type'] ?? 'existing'])
                ]);
                return back()
                    ->withInput()
                    ->with('existing_account_login_url', $loginUrl)
                    ->withErrors(['email' => 'An account with this email address already exists. Please log in to your account first to link your enrollment agent profile.']);
            }

            $user = User::create([
                'fullname' => $validated['full_name'],
                'number' => $validated['phone_number'],
                'email' => $validated['email'],
                'username' => User::generateUniqueUsername($validated['email']),
                'password' => Hash::make($validated['password']),
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

                    if ($preApproved->has_paid_license && $agent->license_status !== 'paid') {
                        $agent->update([
                            'license_status' => 'paid',
                            'license_fee_paid' => \App\Models\EnrollmentAgent::getEffectiveLicenseFee(),
                            'license_payment_method' => $preApproved->license_payment_method ?: 'legacy_pre_platform',
                            'license_paid_at' => now(),
                            'license_admin_notes' => $preApproved->license_notes ?: 'Accredited via master pre-approved roster',
                        ]);
                    }

                    // Clean up OTP from session upon successful claim
                    session()->forget([
                        'claim_otp_code_' . $preApproved->agent_code,
                        'claim_otp_expires_' . $preApproved->agent_code,
                    ]);
                }
            });
        }

        $msg = $ninServerStatus === 'verified'
            ? 'Phase 1 registration complete! Your NIN was verified. Welcome to your Agent Dashboard.'
            : 'Phase 1 registration complete! (NIN server is currently offline; your NIN will be verified in the background). Welcome to your Agent Dashboard.';

        return redirect()->route('agent.dashboard')->with('success', $msg);
    }
}
