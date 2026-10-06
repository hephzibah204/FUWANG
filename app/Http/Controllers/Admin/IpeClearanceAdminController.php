<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\VerificationResult;
use App\Models\User;
use App\Services\WalletService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class IpeClearanceAdminController extends Controller
{
    public function index(Request $request)
    {
        $query = VerificationResult::whereIn('service_type', ['ipe_clearance', 'clearance', 'clearance_request'])
            ->with(['user:id,fullname,email,phone'])
            ->orderBy('created_at', 'desc');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('q')) {
            $q = trim((string) $request->q);
            $query->where(function ($sub) use ($q) {
                $sub->where('reference_id', 'like', '%' . $q . '%')
                    ->orWhere('identifier', 'like', '%' . $q . '%')
                    ->orWhere('response_data->nin', 'like', '%' . $q . '%')
                    ->orWhere('response_data->new_tracking_id', 'like', '%' . $q . '%')
                    ->orWhereHas('user', function ($uq) use ($q) {
                        $uq->where('fullname', 'like', '%' . $q . '%')
                           ->orWhere('email', 'like', '%' . $q . '%');
                    });
            });
        }

        $requests = $query->paginate(20)->withQueryString();

        return view('admin.verifications.ipe_clearance.index', compact('requests'));
    }

    public function show($id)
    {
        $request = VerificationResult::with(['user:id,fullname,email,phone'])->findOrFail($id);
        return view('admin.verifications.ipe_clearance.show', compact('request'));
    }

    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|string|in:pending,waiting_for_review,successful,failed',
            'new_tracking_id' => 'nullable|string|max:120',
            'nin' => 'nullable|string|max:20',
            'admin_note' => 'nullable|string|max:1000'
        ]);

        $verification = VerificationResult::findOrFail($id);
        $user = User::findOrFail($verification->user_id);
        $oldStatus = $verification->status;

        $responseData = $verification->response_data ?? [];

        if ($request->status === 'successful') {
            if ($request->filled('new_tracking_id')) {
                $responseData['new_tracking_id'] = trim((string) $request->new_tracking_id);
            }
        }

        if ($request->filled('nin')) {
            $responseData['nin'] = trim((string) $request->nin);
        }

        $verification->update([
            'status' => $request->status,
            'admin_note' => $request->admin_note,
            'response_data' => $responseData,
        ]);

        // Handle failure and refund
        if ($request->status === 'failed' && $oldStatus !== 'failed') {
            $price = (float) ($responseData['amount_paid'] 
                ?? \App\Models\VerificationPrice::first()->ipe_clearance_price 
                ?? 700);
            $wallet = app(WalletService::class);
            $wallet->failAndRefund(
                $user,
                $price,
                'IPE Clearance Rejected: ' . ($request->admin_note ?: 'Request unsuccessful'),
                $verification->reference_id
            );
        }

        return back()->with('success', 'IPE Clearance status updated to ' . ucfirst($request->status));
    }

    /**
     * Bulk Download all/filtered IPE Clearance Requests as CSV
     */
    public function exportCsv(Request $request): StreamedResponse
    {
        $query = VerificationResult::whereIn('service_type', ['ipe_clearance', 'clearance', 'clearance_request'])
            ->with(['user:id,fullname,email,phone'])
            ->orderBy('created_at', 'desc');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('q')) {
            $q = trim((string) $request->q);
            $query->where(function ($sub) use ($q) {
                $sub->where('reference_id', 'like', '%' . $q . '%')
                    ->orWhere('identifier', 'like', '%' . $q . '%')
                    ->orWhere('response_data->nin', 'like', '%' . $q . '%')
                    ->orWhere('response_data->new_tracking_id', 'like', '%' . $q . '%')
                    ->orWhereHas('user', function ($uq) use ($q) {
                        $uq->where('fullname', 'like', '%' . $q . '%')
                           ->orWhere('email', 'like', '%' . $q . '%');
                    });
            });
        }

        $filename = 'ipe_clearance_requests_' . date('Y-m-d_His') . '.csv';

        return response()->streamDownload(function () use ($query) {
            $handle = fopen('php://output', 'w');

            // Header Row
            fputcsv($handle, [
                'ID',
                'Reference ID',
                'Tracking ID',
                'NIN',
                'Category',
                'Status',
                'New Tracking ID',
                'Admin Note',
                'Applicant Name',
                'Applicant Email',
                'Applicant Phone',
                'Amount Paid',
                'Submitted At',
            ]);

            $query->chunk(200, function ($records) use ($handle) {
                foreach ($records as $r) {
                    $d = $r->response_data ?? [];
                    fputcsv($handle, [
                        $r->id,
                        $r->reference_id,
                        $r->identifier,
                        $d['nin'] ?? '',
                        $d['category'] ?? 'Improcessing Error',
                        $r->status,
                        $d['new_tracking_id'] ?? '',
                        $r->admin_note ?? '',
                        $r->user->fullname ?? ($d['user_name'] ?? 'N/A'),
                        $r->user->email ?? ($d['user_email'] ?? 'N/A'),
                        $r->user->phone ?? ($d['user_phone'] ?? 'N/A'),
                        $d['amount_paid'] ?? 700,
                        $r->created_at->format('Y-m-d H:i:s'),
                    ]);
                }
            });

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Cache-Control' => 'no-store, no-cache',
        ]);
    }

    /**
     * Bulk Upload IPE Clearance Results CSV
     */
    public function importCsv(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:csv,txt|max:5120',
        ]);

        $file = $request->file('file');
        $handle = fopen($file->getRealPath(), 'r');
        if (!$handle) {
            return back()->with('error', 'Unable to read the uploaded CSV file.');
        }

        $headers = fgetcsv($handle);
        if (!$headers) {
            fclose($handle);
            return back()->with('error', 'The uploaded file is empty.');
        }

        // Clean headers: lowercase and trim
        $headerMap = [];
        foreach ($headers as $index => $col) {
            $headerMap[strtolower(trim($col))] = $index;
        }

        // Identify key columns
        $refCol = $headerMap['reference id'] ?? $headerMap['reference'] ?? $headerMap['reference_id'] ?? null;
        $trackingCol = $headerMap['tracking id'] ?? $headerMap['tracking_id'] ?? $headerMap['identifier'] ?? null;
        $idCol = $headerMap['id'] ?? null;
        $statusCol = $headerMap['status'] ?? null;
        $newTrackingCol = $headerMap['new tracking id'] ?? $headerMap['new_tracking_id'] ?? $headerMap['new tracking'] ?? null;
        $ninCol = $headerMap['nin'] ?? null;
        $noteCol = $headerMap['admin note'] ?? $headerMap['admin_note'] ?? $headerMap['remarks'] ?? $headerMap['note'] ?? null;

        if ($refCol === null && $trackingCol === null && $idCol === null) {
            fclose($handle);
            return back()->with('error', 'CSV must contain at least "Reference ID", "Tracking ID", or "ID" column.');
        }

        $processed = 0;
        $updated = 0;
        $refunded = 0;
        $errors = 0;

        $wallet = app(WalletService::class);

        while (($row = fgetcsv($handle)) !== false) {
            if (empty(array_filter($row))) {
                continue;
            }
            $processed++;

            $query = VerificationResult::whereIn('service_type', ['ipe_clearance', 'clearance', 'clearance_request']);

            $record = null;
            if ($idCol !== null && !empty($row[$idCol])) {
                $record = (clone $query)->find(trim($row[$idCol]));
            }
            if (!$record && $refCol !== null && !empty($row[$refCol])) {
                $record = (clone $query)->where('reference_id', trim($row[$refCol]))->first();
            }
            if (!$record && $trackingCol !== null && !empty($row[$trackingCol])) {
                $record = (clone $query)->where('identifier', trim($row[$trackingCol]))->first();
            }

            if (!$record) {
                $errors++;
                continue;
            }

            $rawStatus = $statusCol !== null ? strtolower(trim($row[$statusCol])) : '';
            $status = null;
            if (in_array($rawStatus, ['successful', 'cleared', 'success', 'passed', 'ready'])) {
                $status = 'successful';
            } elseif (in_array($rawStatus, ['failed', 'rejected', 'fail', 'error'])) {
                $status = 'failed';
            } elseif (in_array($rawStatus, ['pending', 'waiting_for_review', 'under_review', 'review'])) {
                $status = 'waiting_for_review';
            }

            $resp = $record->response_data ?? [];

            if ($newTrackingCol !== null && !empty(trim($row[$newTrackingCol]))) {
                $resp['new_tracking_id'] = trim($row[$newTrackingCol]);
            }
            if ($ninCol !== null && !empty(trim($row[$ninCol]))) {
                $resp['nin'] = trim($row[$ninCol]);
            }

            $adminNote = $noteCol !== null ? trim($row[$noteCol]) : null;
            $oldStatus = $record->status;

            $updateData = ['response_data' => $resp];
            if ($status !== null) {
                $updateData['status'] = $status;
            }
            if ($adminNote !== null && $adminNote !== '') {
                $updateData['admin_note'] = $adminNote;
            }

            $record->update($updateData);
            $updated++;

            // Handle refund if newly marked failed
            if ($status === 'failed' && $oldStatus !== 'failed' && $record->user_id) {
                $user = User::find($record->user_id);
                if ($user) {
                    $refundAmount = (float) ($resp['amount_paid'] 
                        ?? \App\Models\VerificationPrice::first()->ipe_clearance_price 
                        ?? 700);
                    $wallet->failAndRefund(
                        $user,
                        $refundAmount,
                        'IPE Clearance Rejected (Bulk Update): ' . ($adminNote ?: 'Request unsuccessful'),
                        $record->reference_id
                    );
                    $refunded++;
                }
            }
        }

        fclose($handle);

        $msg = "Bulk upload completed. Processed: {$processed}, Updated: {$updated}";
        if ($refunded > 0) {
            $msg .= ", Auto-refunded failed: {$refunded}";
        }
        if ($errors > 0) {
            $msg .= ", Unmatched rows: {$errors}";
        }

        return back()->with('success', $msg);
    }
}
