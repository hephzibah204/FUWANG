<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
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

            if ($request->has('phone_number') && ! $request->has('phone')) {
                $request->merge(['phone' => $request->input('phone_number')]);
            }
            if ($request->has('number') && ! $request->has('phone')) {
                $request->merge(['phone' => $request->input('number')]);
            }

            $request->validate([
                'fullname' => ['required', 'string', 'max:255'],
                'username' => ['required', 'string', 'max:20', 'unique:users'],
                'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
                'phone' => ['required', 'string', 'regex:/^[0-9+\s\-()]{10,20}$/'],
                'password' => ['required', 'string', Password::min(8)->mixedCase()->numbers()->symbols()],
                'transaction_pin' => ['required', 'string', 'min:4', 'max:4'],
            ], [
                'phone.required' => 'Please enter your phone number.',
                'phone.regex' => 'Please enter a valid phone number (e.g. 08012345678).',
            ]);

            $rawPhone = (string) $request->input('phone');
            $cleanedPhone = preg_replace('/\D+/', '', $rawPhone);
            if (str_starts_with($cleanedPhone, '234') && strlen($cleanedPhone) === 13) {
                $cleanedPhone = '0' . substr($cleanedPhone, 3);
            } elseif (strlen($cleanedPhone) === 10 && ! str_starts_with($cleanedPhone, '0')) {
                $cleanedPhone = '0' . $cleanedPhone;
            }
            $finalPhone = ! empty($cleanedPhone) ? $cleanedPhone : $rawPhone;

            $referralService = app(ReferralService::class);
            $referralCode = $referralService->normalizeCode($request->input('referral_code'));
            $referrer = $referralCode ? $referralService->findReferrerByCode($referralCode) : null;

            $user = User::create([
                'fullname' => $request->fullname,
                'username' => $request->username,
                'email' => $request->email,
                'number' => $finalPhone,
                'password' => Hash::make($request->password),
                'transaction_pin' => $request->transaction_pin,
                'referral_id' => $referralService->generateReferralCode(),
                'reseller_id' => $request->reseller_id ?? 'default',
            ]);

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
                        'phone' => $finalPhone,
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

            $emailDeliveryFailed = false;
            if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail()) {
                try {
                    $user->sendEmailVerificationNotification();
                } catch (\Throwable $mailException) {
                    $emailDeliveryFailed = true;
                    Log::warning('User registration completed, but verification email could not be delivered: ' . $mailException->getMessage(), [
                        'user_id' => $user->id,
                        'email' => $user->email,
                    ]);
                }
            }

            $redirect = ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                ? route('verification.notice')
                : ($service === 'logistics'
                    ? route('logistics.dashboard')
                    : ($service === 'auctions' ? route('auction.dashboard') : route('dashboard')));

            $friendlyMessage = $emailDeliveryFailed
                ? 'Account created, but verification email could not be delivered immediately. Redirecting...'
                : 'Registration successful! Redirecting...';

            if ($emailDeliveryFailed) {
                session()->flash('warning', 'Account created, but verification email could not be delivered immediately. Please click below to resend it.');
            }

            if ($request->expectsJson()) {
                return response()->json([
                    'status' => 'success',
                    'message' => $friendlyMessage,
                    'redirect' => $redirect,
                    'email_delivery_failed' => $emailDeliveryFailed,
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
