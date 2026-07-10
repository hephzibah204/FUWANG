<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\DeliveryAgent;
use App\Support\NigeriaLocations;
use App\Services\Referrals\ReferralService;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Rules\Password;
use Throwable;

class RegisterController extends Controller
{
    public function showRegistrationForm()
    {
        return view('auth.register', [
            'nigeriaStates' => NigeriaLocations::stateNames(),
        ]);
    }

    public function register(Request $request)
    {
        try {
            $service = $request->input('service');
            $service = is_string($service) ? $service : null;

            if (! $request->boolean('apply_as_agent')) {
                $request->merge([
                    'state' => null,
                    'city' => null,
                    'address' => null,
                    'phone_number' => null,
                    'means_of_identification' => null,
                    'identification_number' => null,
                    'proof_of_address' => null,
                    'next_of_kin_name' => null,
                    'next_of_kin_phone' => null,
                ]);
            }

            $locations = NigeriaLocations::stateToCityMap();

            $request->validate([
                'fullname' => ['required', 'string', 'max:255'],
                'username' => ['required', 'string', 'max:20', 'unique:users'],
                'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
                'password' => ['required', 'string', Password::min(8)->mixedCase()->numbers()->symbols()],
                'transaction_pin' => ['required', 'string', 'min:4', 'max:4'],
                'apply_as_agent' => ['sometimes', 'boolean'],
                'state' => [
                    'nullable',
                    'string',
                    'max:255',
                    'required_if:apply_as_agent,1',
                    Rule::in(array_keys($locations)),
                ],
                'city' => [
                    'nullable',
                    'string',
                    'max:255',
                    'required_if:apply_as_agent,1',
                    function (string $attribute, mixed $value, \Closure $fail) use ($request, $locations): void {
                        if (! $request->boolean('apply_as_agent')) {
                            return;
                        }
                        $state = $request->input('state');
                        if (! is_string($state) || ! isset($locations[$state]) || ! in_array($value, $locations[$state], true)) {
                            $fail('Choose a valid city or town for the selected state.');
                        }
                    },
                ],
                'address' => ['nullable', 'required_if:apply_as_agent,1', 'string', 'max:255'],
                'phone_number' => ['nullable', 'required_if:apply_as_agent,1', 'string', 'max:20'],
            ]);

            $referralService = app(ReferralService::class);
            $referralCode = $referralService->normalizeCode($request->input('referral_code'));
            $referrer = $referralCode ? $referralService->findReferrerByCode($referralCode) : null;

            $user = User::create([
                'fullname' => $request->fullname,
                'username' => $request->username,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'transaction_pin' => $request->transaction_pin,
                'referral_id' => $referralService->generateReferralCode(),
                'reseller_id' => $request->reseller_id ?? 'default',
            ]);

            if ($request->boolean('apply_as_agent')) {
                DeliveryAgent::create([
                    'user_id' => $user->id,
                    'state' => $request->state,
                    'city' => $request->city,
                    'address' => $request->address,
                    'phone_number' => $request->phone_number,
                    'means_of_identification' => null,
                    'identification_number' => null,
                    'proof_of_address' => null,
                    'next_of_kin_name' => null,
                    'next_of_kin_phone' => null,
                    'approval_status' => 'pending',
                ]);
                session(['agent_profile_prompt' => true]);
            }

            if ($referrer && $referralCode) {
                $referralService->recordRegistration($referrer, $user, $referralCode);
            }

            if ($service === 'logistics') {
                if (Schema::hasTable('logistics_profiles')) {
                    \App\Models\LogisticsProfile::query()->firstOrCreate([
                        'user_id' => $user->id,
                    ], [
                        'contact_person' => $user->fullname,
                        'email' => $user->email,
                        'is_active' => true,
                    ]);
                } else {
                    Log::warning('Skipping logistics profile creation during registration because logistics_profiles table is missing.', [
                        'user_id' => $user->id,
                        'service' => $service,
                    ]);
                }
                session(['url.intended' => route('logistics.dashboard')]);
            }

            if ($service === 'auctions') {
                session(['url.intended' => route('auction.dashboard')]);
            }

            Auth::login($user);

            if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail()) {
                $user->sendEmailVerificationNotification();
            }

            $redirect = ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                ? route('verification.notice')
                : ($service === 'logistics'
                    ? route('logistics.dashboard')
                    : ($service === 'auctions' ? route('auction.dashboard') : route('dashboard')));

            if ($request->expectsJson()) {
                return response()->json([
                    'status' => 'success',
                    'message' => 'Registration successful! Redirecting...',
                    'redirect' => $redirect,
                ]);
            }

            return redirect()->to($redirect);
        } catch (ValidationException $e) {
            Log::warning('User registration validation failed.', [
                'url' => $request->fullUrl(),
                'method' => $request->method(),
                'ip' => $request->ip(),
                'email' => $request->input('email'),
                'username' => $request->input('username'),
                'apply_as_agent' => $request->boolean('apply_as_agent'),
                'errors' => $e->errors(),
            ]);
            throw $e;
        } catch (Throwable $e) {
            Log::error('User registration failed with exception.', [
                'url' => $request->fullUrl(),
                'method' => $request->method(),
                'ip' => $request->ip(),
                'email' => $request->input('email'),
                'username' => $request->input('username'),
                'apply_as_agent' => $request->boolean('apply_as_agent'),
                'exception' => get_class($e),
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);
            throw $e;
        }
    }
}
