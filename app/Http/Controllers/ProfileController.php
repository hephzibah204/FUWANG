<?php

namespace App\Http\Controllers;

use App\Models\DeliveryAgent;
use App\Models\User;
use App\Services\KycService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use PragmaRX\Google2FA\Google2FA;

class ProfileController extends Controller
{
    private const PENDING_2FA_SESSION_KEY = 'profile_2fa_pending_secret';

    public function index()
    {
        $user = Auth::user();

        $kycService = app(KycService::class);
        $kycService->refreshUserTier($user);
        $user->refresh();
        $kycSummary = $kycService->tierUpgradeSummary($user);
        $deliveryAgent = DeliveryAgent::query()->where('user_id', $user->id)->first();
        $hasMissingAgentDetails = false;
        if ($deliveryAgent) {
            $hasMissingAgentDetails = blank($deliveryAgent->means_of_identification)
                || blank($deliveryAgent->identification_number)
                || blank($deliveryAgent->proof_of_address)
                || blank($deliveryAgent->next_of_kin_name)
                || blank($deliveryAgent->next_of_kin_phone);
        }

        $google2fa_secret = '';
        if (! $user->google2fa_secret) {
            $google2fa_secret = session(self::PENDING_2FA_SESSION_KEY);
            if (! $google2fa_secret) {
                $google2fa_secret = (new Google2FA)->generateSecretKey();
                session([self::PENDING_2FA_SESSION_KEY => $google2fa_secret]);
            }
        }

        $google2fa_qr_url = '';
        if ($google2fa_secret !== '') {
            $g2fa = new Google2FA;
            $otpauth = $g2fa->getQRCodeUrl(config('app.name'), (string) $user->email, $google2fa_secret);
            $google2fa_qr_url = 'https://quickchart.io/qr?size=200&text='.rawurlencode($otpauth);
        }

        return view('profile.index', [
            'user' => $user,
            'google2fa_secret' => $google2fa_secret,
            'google2fa_qr_url' => $google2fa_qr_url,
            'kycSummary' => $kycSummary,
            'deliveryAgent' => $deliveryAgent,
            'showAgentDetailsPrompt' => (bool) session('agent_profile_prompt', false) || $hasMissingAgentDetails,
        ]);
    }

    public function enableTwoFactor(Request $request)
    {
        $request->validate([
            'secret' => ['required', 'string', 'min:16', 'max:64'],
            'one_time_password' => ['required', 'string', 'size:6'],
        ]);

        $user = Auth::user();
        if ($user->google2fa_secret) {
            return response()->json(['status' => false, 'message' => 'Two-factor authentication is already enabled.']);
        }

        $google2fa = new Google2FA;
        if (! $google2fa->verifyKey($request->secret, $request->one_time_password)) {
            return response()->json(['status' => false, 'message' => 'Invalid verification code. Try again.']);
        }

        $user->forceFill(['google2fa_secret' => $request->secret])->save();
        session()->forget(self::PENDING_2FA_SESSION_KEY);

        return response()->json(['status' => true, 'message' => 'Two-factor authentication enabled.']);
    }

    public function disableTwoFactor(Request $request)
    {
        $request->validate([
            'current_password' => ['required', 'string'],
        ]);

        $user = Auth::user();
        if (! $user->google2fa_secret) {
            return response()->json(['status' => false, 'message' => 'Two-factor authentication is not enabled.']);
        }

        if (! Hash::check($request->current_password, $user->password)) {
            return response()->json(['status' => false, 'message' => 'Current password is incorrect.']);
        }

        $user->forceFill(['google2fa_secret' => null])->save();
        session()->forget(self::PENDING_2FA_SESSION_KEY);

        return response()->json(['status' => true, 'message' => 'Two-factor authentication disabled.']);
    }

    public function security()
    {
        return view('profile.security', [
            'user' => Auth::user(),
        ]);
    }

    public function update(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'fullname' => 'sometimes|string|max:100',
            'number' => 'sometimes|string|digits:11',
            'current_password' => 'required_with:new_password|string',
            'new_password' => ['nullable', 'string', 'confirmed', Password::min(8)->mixedCase()->numbers()->symbols()],
            'current_pin' => 'required_with:new_pin|string',
            'new_pin' => 'nullable|string|digits:4',
        ]);

        $data = [];

        if ($request->filled('fullname')) {
            $data['fullname'] = $request->fullname;
        }
        if ($request->filled('number')) {
            $data['number'] = $request->number;
        }

        if ($request->filled('new_password')) {
            if (! Hash::check($request->current_password, $user->password)) {
                return response()->json(['status' => false, 'message' => 'Current password is incorrect.']);
            }
            $data['password'] = Hash::make($request->new_password);
        }

        if ($request->filled('new_pin')) {
            if (! $this->transactionPinMatches($user, (string) $request->current_pin)) {
                return response()->json(['status' => false, 'message' => 'Current PIN is incorrect.']);
            }
            // Plain 4-digit PIN (same as RegisterController); session/login expect this format.
            $data['transaction_pin'] = $request->new_pin;
        }

        if (! empty($data)) {
            $user->update($data);
            if (array_key_exists('transaction_pin', $data)) {
                session(['transaction_pin' => $data['transaction_pin']]);
            }
        }

        return response()->json(['status' => true, 'message' => 'Profile updated successfully.']);
    }

    public function updateDeliveryAgentDetails(Request $request)
    {
        $user = Auth::user();
        $deliveryAgent = DeliveryAgent::query()->where('user_id', $user->id)->first();

        if (! $deliveryAgent) {
            return redirect()->route('profile')->with('error', 'Delivery agent profile was not found.');
        }

        $proofRules = $deliveryAgent->proof_of_address
            ? ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:2048']
            : ['required', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:2048'];

        $validated = $request->validate([
            'means_of_identification' => ['required', 'string', Rule::in(['nin', 'drivers_license', 'voters_card', 'passport'])],
            'identification_number' => ['required', 'string', 'max:255'],
            'proof_of_address' => $proofRules,
            'next_of_kin_name' => ['required', 'string', 'max:255'],
            'next_of_kin_phone' => ['required', 'string', 'max:20'],
        ]);

        if ($request->hasFile('proof_of_address')) {
            $validated['proof_of_address'] = $request->file('proof_of_address')->store('proof_of_address', 'public');
        } else {
            unset($validated['proof_of_address']);
        }

        if ($deliveryAgent->approval_status !== 'approved') {
            $validated['approval_status'] = 'pending';
        }

        $deliveryAgent->update($validated);
        session()->forget('agent_profile_prompt');

        return redirect()->route('profile')->with('success', 'Agent verification details saved successfully.');
    }

    /**
     * Registration stores PIN as plain digits; older profile updates may have used Hash::make.
     */
    private function transactionPinMatches(User $user, string $candidate): bool
    {
        $stored = $user->transaction_pin;
        if ($stored === null || $stored === '') {
            return false;
        }
        $stored = (string) $stored;
        if (str_starts_with($stored, '$2y$')
            || str_starts_with($stored, '$2a$')
            || str_starts_with($stored, '$2b$')
            || str_starts_with($stored, '$argon')) {
            return Hash::check($candidate, $stored);
        }

        return hash_equals($stored, $candidate);
    }
}
