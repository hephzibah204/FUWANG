<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LicenseDiscountCode;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class AdminLicenseDiscountController extends Controller
{
    /**
     * Create a percentage discount code for the station license fee.
     * Codes are stored uppercase; each code can be redeemed once per user.
     */
    public function store(Request $request)
    {
        if ($request->filled('code')) {
            $request->merge(['code' => strtoupper(trim((string) $request->input('code')))]);
        }

        $validated = $request->validate([
            'code' => ['nullable', 'string', 'max:50', 'regex:/^[A-Za-z0-9\-_]+$/', Rule::unique('license_discount_codes', 'code')],
            'percent' => ['required', 'integer', 'min:1', 'max:90'],
            'max_uses' => ['nullable', 'integer', 'min:1'],
            'expires_at' => ['nullable', 'date'],
        ]);

        $code = (string) ($validated['code'] ?? '');
        if ($code === '') {
            do {
                $code = 'FUWA-' . strtoupper(Str::random(6));
            } while (LicenseDiscountCode::where('code', $code)->exists());
        }

        LicenseDiscountCode::create([
            'code' => $code,
            'percent' => $validated['percent'],
            'max_uses' => $validated['max_uses'] ?? null,
            'expires_at' => $validated['expires_at'] ?? null,
            'is_active' => true,
        ]);

        return back()->with('success', "Discount code {$code} created ({$validated['percent']}% off the license fee).");
    }

    public function toggle($codeId)
    {
        $discount = LicenseDiscountCode::findOrFail($codeId);
        $discount->update(['is_active' => !$discount->is_active]);

        return back()->with('success', "Discount code {$discount->code} " . ($discount->is_active ? 'activated.' : 'deactivated.'));
    }

    public function destroy($codeId)
    {
        $discount = LicenseDiscountCode::findOrFail($codeId);
        $code = $discount->code;
        $discount->delete();

        return back()->with('success', "Discount code {$code} deleted.");
    }
}
