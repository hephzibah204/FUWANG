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
use Illuminate\Support\Facades\Schema;
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

        AgentLicenseTransaction::ensureSchemaIntegrity();
        PreApprovedAgent::ensureSchemaIntegrity();

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

            AgentLicenseTransaction::createSafe([
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
                try {
                    $updateData = [];
                    if (Schema::hasColumn('pre_approved_agents', 'has_paid_license')) {
                        $updateData['has_paid_license'] = true;
                    }
                    if (Schema::hasColumn('pre_approved_agents', 'license_payment_method')) {
                        $updateData['license_payment_method'] = $method;
                    }
                    if (Schema::hasColumn('pre_approved_agents', 'license_notes')) {
                        $updateData['license_notes'] = 'Marked paid by admin: ' . $request->admin_notes;
                    }
                    if (!empty($updateData)) {
                        PreApprovedAgent::where('agent_code', $agent->company_agent_code)->update($updateData);
                    }
                } catch (\Throwable $preEx) {
                    \Illuminate\Support\Facades\Log::warning('Notice updating pre_approved_agents in markPaid: ' . $preEx->getMessage());
                }
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
     * Update / Correct existing license details (price, status, payment method, reference, notes)
     */
    public function update(Request $request, $id)
    {
        $agent = EnrollmentAgent::findOrFail($id);

        $request->validate([
            'license_status' => ['required', 'string', 'in:paid,unpaid,pending_review,waived'],
            'amount' => ['nullable', 'numeric', 'min:0'],
            'payment_method' => ['nullable', 'string', 'in:legacy_pre_platform,admin_manual,waived,wallet,paystack,offline_proof'],
            'payment_reference' => ['nullable', 'string', 'max:120'],
            'payment_date' => ['nullable', 'date'],
            'admin_notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $admin = Auth::guard('admin')->user();
        $status = $request->license_status;
        $method = $request->payment_method ?: ($agent->license_payment_method ?: 'admin_manual');

        AgentLicenseTransaction::ensureSchemaIntegrity();
        PreApprovedAgent::ensureSchemaIntegrity();

        DB::transaction(function () use ($agent, $admin, $status, $method, $request) {
            if ($status === 'paid') {
                $amount = $request->filled('amount') 
                    ? (float) $request->amount 
                    : (float) ($agent->license_fee_paid ?? EnrollmentAgent::getEffectiveLicenseFee());
                $paidAt = $request->filled('payment_date') 
                    ? Carbon::parse($request->payment_date) 
                    : ($agent->license_paid_at ?: now());
                $ref = $request->filled('payment_reference') 
                    ? $request->payment_reference 
                    : ($agent->license_payment_reference ?: 'ADMIN-' . date('Ymd') . '-' . strtoupper(Str::random(4)));

                $agent->update([
                    'license_status' => 'paid',
                    'license_fee_amount' => $amount,
                    'license_fee_paid' => $amount,
                    'license_payment_method' => $method,
                    'license_payment_reference' => $ref,
                    'license_paid_at' => $paidAt,
                    'license_verified_by' => $admin?->id ?: $agent->license_verified_by,
                    'license_verified_at' => $agent->license_verified_at ?: now(),
                    'license_admin_notes' => $request->admin_notes,
                    'license_rejection_reason' => null,
                ]);

                AgentLicenseTransaction::createSafe([
                    'agent_id' => $agent->id,
                    'user_id' => $agent->user_id,
                    'amount' => $amount,
                    'payment_method' => $method,
                    'reference' => $ref,
                    'status' => 'completed',
                    'admin_id' => $admin?->id,
                    'notes' => 'License updated/corrected by Admin ' . ($admin?->name ?? '') . ($request->admin_notes ? ': ' . $request->admin_notes : ''),
                ]);

                if ($agent->company_agent_code) {
                    try {
                        $updateData = [];
                        if (Schema::hasColumn('pre_approved_agents', 'has_paid_license')) {
                            $updateData['has_paid_license'] = true;
                        }
                        if (Schema::hasColumn('pre_approved_agents', 'license_payment_method')) {
                            $updateData['license_payment_method'] = $method;
                        }
                        if (Schema::hasColumn('pre_approved_agents', 'license_notes')) {
                            $updateData['license_notes'] = 'License updated by admin: ' . $request->admin_notes;
                        }
                        if (!empty($updateData)) {
                            PreApprovedAgent::where('agent_code', $agent->company_agent_code)->update($updateData);
                        }
                    } catch (\Throwable $preEx) {
                        \Illuminate\Support\Facades\Log::warning('Notice updating pre_approved_agents in update license: ' . $preEx->getMessage());
                    }
                }
            } elseif ($status === 'waived') {
                $ref = $request->filled('payment_reference') 
                    ? $request->payment_reference 
                    : ($agent->license_payment_reference ?: 'WAIVED-' . ($agent->company_agent_code ?: $agent->id));

                $agent->update([
                    'license_status' => 'waived',
                    'license_fee_amount' => 0.00,
                    'license_fee_paid' => 0.00,
                    'license_payment_method' => 'waived',
                    'license_payment_reference' => $ref,
                    'license_paid_at' => $request->filled('payment_date') ? Carbon::parse($request->payment_date) : ($agent->license_paid_at ?: now()),
                    'license_verified_by' => $admin?->id ?: $agent->license_verified_by,
                    'license_verified_at' => $agent->license_verified_at ?: now(),
                    'license_admin_notes' => $request->admin_notes,
                    'license_rejection_reason' => null,
                ]);

                AgentLicenseTransaction::createSafe([
                    'agent_id' => $agent->id,
                    'user_id' => $agent->user_id,
                    'amount' => 0.00,
                    'payment_method' => 'waived',
                    'reference' => $ref,
                    'status' => 'completed',
                    'admin_id' => $admin?->id,
                    'notes' => 'License waived by Admin ' . ($admin?->name ?? '') . ($request->admin_notes ? ': ' . $request->admin_notes : ''),
                ]);

                if ($agent->company_agent_code) {
                    try {
                        $updateData = [];
                        if (Schema::hasColumn('pre_approved_agents', 'has_paid_license')) {
                            $updateData['has_paid_license'] = true;
                        }
                        if (Schema::hasColumn('pre_approved_agents', 'license_payment_method')) {
                            $updateData['license_payment_method'] = 'waived';
                        }
                        if (Schema::hasColumn('pre_approved_agents', 'license_notes')) {
                            $updateData['license_notes'] = 'Waived by admin: ' . $request->admin_notes;
                        }
                        if (!empty($updateData)) {
                            PreApprovedAgent::where('agent_code', $agent->company_agent_code)->update($updateData);
                        }
                    } catch (\Throwable $preEx) {}
                }
            } elseif ($status === 'pending_review') {
                $amount = $request->filled('amount') ? (float) $request->amount : (float) ($agent->license_fee_paid ?? EnrollmentAgent::getEffectiveLicenseFee());
                $agent->update([
                    'license_status' => 'pending_review',
                    'license_fee_amount' => $amount,
                    'license_payment_method' => $method ?: 'offline_proof',
                    'license_payment_reference' => $request->payment_reference ?: $agent->license_payment_reference,
                    'license_admin_notes' => $request->admin_notes,
                ]);

                if ($agent->company_agent_code) {
                    try {
                        if (Schema::hasColumn('pre_approved_agents', 'has_paid_license')) {
                            PreApprovedAgent::where('agent_code', $agent->company_agent_code)->update(['has_paid_license' => false]);
                        }
                    } catch (\Throwable $preEx) {}
                }
            } else { // 'unpaid'
                $agent->update([
                    'license_status' => 'unpaid',
                    'license_fee_paid' => null,
                    'license_payment_method' => null,
                    'license_payment_reference' => null,
                    'license_paid_at' => null,
                    'license_admin_notes' => $request->admin_notes ?: 'License reset to unpaid by admin correction',
                    'license_rejection_reason' => $request->admin_notes,
                ]);

                AgentLicenseTransaction::createSafe([
                    'agent_id' => $agent->id,
                    'user_id' => $agent->user_id,
                    'amount' => 0.00,
                    'payment_method' => 'admin_manual',
                    'reference' => 'CORRECTION-UNPAID-' . date('Ymd') . '-' . strtoupper(Str::random(4)),
                    'status' => 'rejected',
                    'admin_id' => $admin?->id,
                    'notes' => 'Status corrected to UNPAID by Admin ' . ($admin?->name ?? '') . ($request->admin_notes ? ': ' . $request->admin_notes : ''),
                ]);

                if ($agent->company_agent_code) {
                    try {
                        $updateData = [];
                        if (Schema::hasColumn('pre_approved_agents', 'has_paid_license')) {
                            $updateData['has_paid_license'] = false;
                        }
                        if (Schema::hasColumn('pre_approved_agents', 'license_payment_method')) {
                            $updateData['license_payment_method'] = null;
                        }
                        if (Schema::hasColumn('pre_approved_agents', 'license_notes')) {
                            $updateData['license_notes'] = 'Corrected to unpaid by admin: ' . $request->admin_notes;
                        }
                        if (!empty($updateData)) {
                            PreApprovedAgent::where('agent_code', $agent->company_agent_code)->update($updateData);
                        }
                    } catch (\Throwable $preEx) {}
                }
            }
        });

        return back()->with('success', "License details for {$agent->full_name} have been updated successfully.");
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

        AgentLicenseTransaction::ensureSchemaIntegrity();
        PreApprovedAgent::ensureSchemaIntegrity();

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

            AgentLicenseTransaction::updateOrCreateSafe(
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
                try {
                    $updateData = [];
                    if (Schema::hasColumn('pre_approved_agents', 'has_paid_license')) {
                        $updateData['has_paid_license'] = true;
                    }
                    if (Schema::hasColumn('pre_approved_agents', 'license_payment_method')) {
                        $updateData['license_payment_method'] = 'offline_proof';
                    }
                    if (!empty($updateData)) {
                        PreApprovedAgent::where('agent_code', $agent->company_agent_code)->update($updateData);
                    }
                } catch (\Throwable $preEx) {
                    \Illuminate\Support\Facades\Log::warning('Notice updating pre_approved_agents in approveProof: ' . $preEx->getMessage());
                }
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
