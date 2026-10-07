<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AgentLicenseTransaction;
use App\Models\EnrollmentAgent;
use App\Models\PreApprovedAgent;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class AdminAgentLicenseController extends Controller
{
    /**
     * Dedicated Agent License Desk
     */
    public function index(Request $request)
    {
        $status = $request->query('status');
        $search = $request->query('search');

        $query = EnrollmentAgent::with(['user', 'licenseVerifiedBy'])->latest();

        if (in_array($status, ['paid', 'pending_review', 'unpaid', 'waived'], true)) {
            if ($status === 'unpaid') {
                $query->where(function($q) {
                    $q->whereNull('license_status')->orWhere('license_status', 'unpaid');
                });
            } else {
                $query->where('license_status', $status);
            }
        }

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('full_name', 'like', "%{$search}%")
                  ->orWhere('phone_number', 'like', "%{$search}%")
                  ->orWhere('nin', 'like', "%{$search}%")
                  ->orWhere('company_agent_code', 'like', "%{$search}%")
                  ->orWhere('license_payment_reference', 'like', "%{$search}%")
                  ->orWhereHas('user', function($uq) use ($search) {
                      $uq->where('email', 'like', "%{$search}%");
                  });
            });
        }

        $agents = $query->paginate(20)->withQueryString();

        $counts = [
            'total' => EnrollmentAgent::count(),
            'paid' => EnrollmentAgent::whereIn('license_status', ['paid', 'waived'])->count(),
            'pending_review' => EnrollmentAgent::where('license_status', 'pending_review')->count(),
            'unpaid' => EnrollmentAgent::where(function($q) {
                $q->whereNull('license_status')->orWhere('license_status', 'unpaid');
            })->count(),
            'total_revenue' => (float) EnrollmentAgent::whereIn('license_status', ['paid'])->sum('license_fee_paid'),
        ];

        // Specific queue of pending proof reviews
        $pendingReviews = EnrollmentAgent::with('user')
            ->where('license_status', 'pending_review')
            ->latest('updated_at')
            ->get();

        $effectiveFee = EnrollmentAgent::getEffectiveLicenseFee();
        $isPromo = EnrollmentAgent::isPromoActive();

        return view('admin.agents.licenses.index', compact(
            'agents', 'counts', 'status', 'search', 'pendingReviews', 'effectiveFee', 'isPromo'
        ));
    }

    /**
     * Manually mark an agent as PAID (Legacy pre-website, direct offline, or manual grant)
     */
    public function markPaid(Request $request, $id)
    {
        $agent = EnrollmentAgent::findOrFail($id);

        $request->validate([
            'payment_method' => ['required', 'string', 'in:legacy_pre_platform,admin_manual,waived,wallet,paystack'],
            'amount' => ['nullable', 'numeric', 'min:0'],
            'payment_reference' => ['nullable', 'string', 'max:120'],
            'payment_date' => ['nullable', 'date'],
            'admin_notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $admin = Auth::guard('admin')->user();
        $method = $request->payment_method;

        $feeAmount = $request->filled('amount') 
            ? (float) $request->amount 
            : ($method === 'waived' ? 0.00 : EnrollmentAgent::getEffectiveLicenseFee());

        $paymentDate = $request->filled('payment_date') 
            ? Carbon::parse($request->payment_date) 
            : now();

        $ref = $request->payment_reference ?: (
            $method === 'legacy_pre_platform' 
                ? 'LEGACY-' . ($agent->company_agent_code ?: $agent->id) 
                : 'ADMIN-' . date('Ymd') . '-' . strtoupper(Str::random(4))
        );

        DB::transaction(function () use ($agent, $admin, $method, $feeAmount, $paymentDate, $ref, $request) {
            $agent->update([
                'license_status' => $method === 'waived' ? 'waived' : 'paid',
                'license_fee_amount' => $feeAmount,
                'license_fee_paid' => $feeAmount,
                'license_payment_method' => $method,
                'license_payment_reference' => $ref,
                'license_paid_at' => $paymentDate,
                'license_verified_by' => $admin?->id,
                'license_verified_at' => now(),
                'license_admin_notes' => $request->admin_notes,
                'license_rejection_reason' => null,
            ]);

            AgentLicenseTransaction::create([
                'agent_id' => $agent->id,
                'user_id' => $agent->user_id,
                'amount' => $feeAmount,
                'payment_method' => $method,
                'reference' => $ref,
                'status' => 'completed',
                'admin_id' => $admin?->id,
                'notes' => $request->admin_notes ?: 'Manually marked as paid by Admin ' . ($admin?->name ?? ''),
            ]);

            // If agent is linked to pre_approved_agents, update master roster record too
            if ($agent->company_agent_code) {
                PreApprovedAgent::where('agent_code', $agent->company_agent_code)->update([
                    'has_paid_license' => true,
                    'license_payment_method' => $method,
                    'license_notes' => 'Marked paid by admin: ' . $request->admin_notes,
                ]);
            }
        });

        $methodLabel = match($method) {
            'legacy_pre_platform' => 'Pre-Website / Legacy Accreditation',
            'admin_manual' => 'Manual Offline Payment',
            'waived' => 'Waived Accreditation',
            default => 'Payment'
        };

        return back()->with('success', "Agent {$agent->full_name} license marked as PAID ({$methodLabel}).");
    }

    /**
     * Approve submitted offline payment proof
     */
    public function approveProof(Request $request, $id)
    {
        $agent = EnrollmentAgent::findOrFail($id);
        $admin = Auth::guard('admin')->user();

        $meta = (array) ($agent->license_proof_meta ?? []);
        $amount = (float) ($meta['amount_paid'] ?? EnrollmentAgent::getEffectiveLicenseFee());
        $reference = (string) ($meta['transaction_reference'] ?? $agent->license_payment_reference ?: ('PROOF-APPV-' . $agent->id));

        DB::transaction(function () use ($agent, $admin, $amount, $reference, $request) {
            $agent->update([
                'license_status' => 'paid',
                'license_fee_paid' => $amount,
                'license_verified_by' => $admin?->id,
                'license_verified_at' => now(),
                'license_paid_at' => now(),
                'license_admin_notes' => $request->input('admin_notes', 'Offline payment proof approved by admin.'),
                'license_rejection_reason' => null,
            ]);

            AgentLicenseTransaction::updateOrCreate(
                ['agent_id' => $agent->id, 'gateway_reference' => $agent->license_payment_reference],
                [
                    'user_id' => $agent->user_id,
                    'amount' => $amount,
                    'payment_method' => 'offline_proof',
                    'reference' => 'PROOF-' . date('Ymd') . '-' . $agent->id,
                    'proof_path' => $agent->license_proof_path,
                    'status' => 'completed',
                    'admin_id' => $admin?->id,
                    'notes' => 'Offline proof approved by admin',
                ]
            );

            if ($agent->company_agent_code) {
                PreApprovedAgent::where('agent_code', $agent->company_agent_code)->update([
                    'has_paid_license' => true,
                    'license_payment_method' => 'offline_proof',
                ]);
            }
        });

        return back()->with('success', "Payment proof for {$agent->full_name} approved! Station license has been accredited.");
    }

    /**
     * Reject submitted offline payment proof
     */
    public function rejectProof(Request $request, $id)
    {
        $request->validate([
            'rejection_reason' => ['required', 'string', 'max:1000'],
        ]);

        $agent = EnrollmentAgent::findOrFail($id);
        $admin = Auth::guard('admin')->user();

        DB::transaction(function () use ($agent, $admin, $request) {
            $agent->update([
                'license_status' => 'unpaid',
                'license_rejection_reason' => $request->rejection_reason,
                'license_verified_by' => $admin?->id,
                'license_verified_at' => now(),
            ]);

            AgentLicenseTransaction::where('agent_id', $agent->id)
                ->where('status', 'pending')
                ->latest()
                ->first()
                ?->update([
                    'status' => 'rejected',
                    'admin_id' => $admin?->id,
                    'notes' => 'Proof rejected: ' . $request->rejection_reason,
                ]);
        });

        return back()->with('success', "Offline payment proof for {$agent->full_name} rejected. The agent can re-upload fresh proof.");
    }

    /**
     * Revoke license status back to unpaid
     */
    public function revoke(Request $request, $id)
    {
        $request->validate([
            'reason' => ['required', 'string', 'max:1000'],
        ]);

        $agent = EnrollmentAgent::findOrFail($id);

        $agent->update([
            'license_status' => 'unpaid',
            'license_rejection_reason' => 'License revoked: ' . $request->reason,
            'license_admin_notes' => 'Revoked on ' . now()->toFormattedDateString() . ': ' . $request->reason,
        ]);

        return back()->with('success', "Station license for {$agent->full_name} has been revoked.");
    }
}
