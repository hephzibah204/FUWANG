<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class ProfileController extends Controller
{
    public function index()
    {
        return view('profile.index', [
            'user' => Auth::user(),
        ]);
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
            'fullname'         => 'sometimes|string|max:100',
            'number'           => 'sometimes|string|digits:11',
            'current_password' => 'required_with:new_password|string',
            'new_password'     => ['nullable', 'string', 'confirmed', Password::min(8)->mixedCase()->numbers()->symbols()],
            'current_pin'      => 'required_with:new_pin|string',
            'new_pin'          => 'nullable|string|digits:4',
        ]);

        $data = [];

        if ($request->filled('fullname'))  $data['fullname'] = $request->fullname;
        if ($request->filled('number'))    $data['number']   = $request->number;

        if ($request->filled('new_password')) {
            if (!Hash::check($request->current_password, $user->password)) {
                return response()->json(['status' => false, 'message' => 'Current password is incorrect.']);
            }
            $data['password'] = Hash::make($request->new_password);
        }

        if ($request->filled('new_pin')) {
            if (!$user->transaction_pin || !Hash::check($request->current_pin, $user->transaction_pin)) {
                return response()->json(['status' => false, 'message' => 'Current PIN is incorrect.']);
            }
            $data['transaction_pin'] = Hash::make($request->new_pin);
        }

        if (!empty($data)) {
            $user->update($data);
        }

        return response()->json(['status' => true, 'message' => 'Profile updated successfully.']);
    }
}
