<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\KycService;
use Illuminate\Auth\Events\Verified;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EmailVerificationController extends Controller
{
    public function notice(Request $request): RedirectResponse|View
    {
        if ($request->user()->hasVerifiedEmail()) {
            return redirect()->intended(route('dashboard'));
        }

        return view('auth.verify-email');
    }

    public function verify(Request $request, $id, $hash): RedirectResponse
    {
        // The signed URL itself is the proof of ownership - do not require a
        // matching login session. The old FormRequest authorize() demanded one,
        // so the first click from the email (usually logged out) died with 403
        // and nothing was verified.
        $user = \App\Models\User::findOrFail($id);

        if (!hash_equals(sha1($user->getEmailForVerification()), (string) $hash)) {
            abort(403);
        }

        $sameUser = $request->user() && (int) $request->user()->id === (int) $user->id;

        if ($user->hasVerifiedEmail()) {
            return $sameUser
                ? redirect()->intended(route('dashboard', absolute: false).'?verified=1')
                : redirect()->route('login')->with('status', __('Your email address is already verified. Please log in.'));
        }

        if ($user->markEmailAsVerified()) {
            event(new Verified($user));
            app(KycService::class)->refreshUserTier($user);
        }

        if ($sameUser) {
            return redirect()->intended(route('dashboard', absolute: false).'?verified=1');
        }

        return redirect()->route('login')->with('status', __('Your email address has been verified. Please log in.'));
    }

    public function send(Request $request): RedirectResponse
    {
        if ($request->user()->hasVerifiedEmail()) {
            return redirect()->route('dashboard');
        }

        try {
            $request->user()->sendEmailVerificationNotification();
            return back()->with('status', __('A fresh verification link has been sent to your email address.'));
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('Resending verification email failed: ' . $e->getMessage(), [
                'user_id' => $request->user()->id,
                'email' => $request->user()->email,
            ]);

            return back()->with('error', __('We could not deliver the verification email right now. Please check your connection and try again in a moment.'));
        }
    }
}
