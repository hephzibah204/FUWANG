<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\Referrals\ReferralService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

class GoogleAuthController extends Controller
{
    private const USER_2FA_PENDING = 'user_2fa_pending';
    private const USER_2FA_ID = 'user_2fa_id';
    private const USER_2FA_REMEMBER = 'user_2fa_remember';
    private const OAUTH_SERVICE = 'oauth_service';

    public function redirect(Request $request): RedirectResponse
    {
        if (! config('services.google.client_id') || ! config('services.google.client_secret') || ! config('services.google.redirect')) {
            return redirect()->route('login')->withErrors([
                'email' => 'Google sign-in is not configured yet.',
            ]);
        }

        $service = $request->query('service');
        $service = is_string($service) ? $service : null;
        $request->session()->put(self::OAUTH_SERVICE, $service);

        $redirect = $request->query('redirect');
        if (is_string($redirect) && str_starts_with($redirect, '/')) {
            session(['url.intended' => $redirect]);
        } elseif ($service === 'logistics') {
            session(['url.intended' => route('logistics.dashboard')]);
        }

        return Socialite::driver('google')->redirect();
    }

    public function callback(ReferralService $referralService): RedirectResponse
    {
        try {
            $googleUser = Socialite::driver('google')->user();
        } catch (\Throwable $e) {
            Log::warning('Google OAuth callback failed', ['error' => $e->getMessage()]);

            return redirect()->route('login')->withErrors([
                'email' => 'Google sign-in failed. Please try again.',
            ]);
        }

        $email = trim((string) ($googleUser->getEmail() ?? ''));
        if ($email === '') {
            return redirect()->route('login')->withErrors([
                'email' => 'Google account does not have an email address.',
            ]);
        }

        $googleId = (string) $googleUser->getId();
        $name = trim((string) ($googleUser->getName() ?? 'Google User'));
        $avatar = $googleUser->getAvatar();

        $user = User::query()
            ->where('google_id', $googleId)
            ->orWhere('email', $email)
            ->first();

        if (! $user) {
            $username = $this->uniqueUsernameFromEmail($email);
            $user = User::query()->create([
                'fullname' => $name !== '' ? $name : $username,
                'username' => $username,
                'email' => $email,
                'password' => Hash::make(Str::random(40)),
                'transaction_pin' => (string) random_int(1000, 9999),
                'reseller_id' => 'default',
                'referral_id' => $referralService->generateReferralCode(),
                'google_id' => $googleId,
                'google_avatar' => is_string($avatar) ? $avatar : null,
                'email_verified_at' => now(),
            ]);
        } else {
            $dirty = false;
            if (! $user->google_id) {
                $user->google_id = $googleId;
                $dirty = true;
            }
            if (is_string($avatar) && $avatar !== '' && $user->google_avatar !== $avatar) {
                $user->google_avatar = $avatar;
                $dirty = true;
            }
            if (! $user->email_verified_at) {
                $user->email_verified_at = now();
                $dirty = true;
            }
            if ($dirty) {
                $user->save();
            }
        }

        if (($user->user_status ?? 'active') !== 'active') {
            return redirect()->route('login')->withErrors([
                'email' => 'Your account is currently '.$user->user_status.'. Please contact support.',
            ]);
        }

        if (! empty($user->google2fa_secret)) {
            session([
                self::USER_2FA_PENDING => true,
                self::USER_2FA_ID => $user->id,
                self::USER_2FA_REMEMBER => true,
            ]);

            return redirect()->route('2fa.challenge');
        }

        Auth::login($user, true);
        session([
            'fullname' => $user->fullname,
            'transaction_pin' => $user->transaction_pin,
        ]);
        $user->update(['online_status' => 'online']);

        $service = session(self::OAUTH_SERVICE);
        session()->forget(self::OAUTH_SERVICE);

        $redirect = ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
            ? route('verification.notice')
            : ($service === 'logistics' ? route('logistics.dashboard') : route('dashboard'));

        return redirect()->intended($redirect);
    }

    private function uniqueUsernameFromEmail(string $email): string
    {
        $base = Str::of($email)->before('@')->lower()->replaceMatches('/[^a-z0-9_]/', '')->value();
        if ($base === '') {
            $base = 'user';
        }
        $candidate = Str::limit($base, 20, '');
        if (! User::query()->where('username', $candidate)->exists()) {
            return $candidate;
        }

        do {
            $suffix = (string) random_int(100, 9999);
            $trimmed = Str::limit($base, max(1, 20 - strlen($suffix)), '');
            $candidate = $trimmed.$suffix;
        } while (User::query()->where('username', $candidate)->exists());

        return $candidate;
    }
}
