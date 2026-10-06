<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\VerificationResult;
use App\Models\User;
use App\Models\ApiCenter;
use App\Models\SystemSetting;
use App\Http\Controllers\Service\VerificationController;
use App\Services\WalletService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Symfony\Component\HttpFoundation\StreamedResponse;

class NinValidationAdminController extends Controller
{
    public function index(Request $request)
    {
        $query = VerificationResult::whereIn('service_type', ['validation', 'nin_validation'])
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
                    ->orWhere('response_data->validation_reason', 'like', '%' . $q . '%')
                    ->orWhereHas('user', function ($uq) use ($q) {
                        $uq->where('fullname', 'like', '%' . $q . '%')
                           ->orWhere('email', 'like', '%' . $q . '%');
                    });
            });
        }

        $requests = $query->paginate(20)->withQueryString();
        $currentMode = SystemSetting::get('nin_validation_mode', 'manual');

        return view('admin.verifications.nin_validation.index', compact('requests', 'currentMode'));
    }

    /**
     * Switch Operating Mode between Manual Reporting and Robosttech API
     */
    public function updateMode(Request $request)
    {
        $request->validate([
            'mode' => 'required|string|in:manual,robosttech',
        ]);

        SystemSetting::set('nin_validation_mode', $request->mode, 'services');

        $label = $request->mode === 'robosttech' ? 'Robosttech API' : 'Manual Reporting';

        return back()->with('success', "NIN Validation operating mode switched to {$label}.");
    }

    /**
     * Show detail view of a single validation request
     */
    public function show($id)
    {
        $request = VerificationResult::with(['user:id,fullname,email,phone'])->findOrFail($id);
        return view('admin.verifications.nin_validation.show', compact('request'));
    }

    /**
     * Update validation status, admin note, or result data
     */
    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|string|in:pending,waiting_for_review,successful,failed',
            'nin' => 'nullable|string|max:20',
            'admin_note' => 'nullable|string|max:1000'
        ]);

        $verification = VerificationResult::findOrFail($id);
        $user = User::findOrFail($verification->user_id);
        $oldStatus = $verification->status;

        $responseData = $verification->response_data ?? [];

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
                ?? \App\Models\VerificationPrice::first()->validation_price 
                ?? 700);
            $wallet = app(WalletService::class);
            $ref = $wallet->failAndRefund(
                $user,
                $price,
                'NIN Validation Rejected: ' . ($request->admin_note ?: 'Request unsuccessful'),
                $verification->reference_id
            );
            if ($ref['ok'] ?? false) {
                $responseData['refunded'] = true;
                $responseData['refunded_amount'] = $price;
                $responseData['refunded_at'] = now()->toDateTimeString();
                $verification->response_data = $responseData;
                $verification->save();
            }
        }

        return back()->with('success', 'NIN Validation status updated to ' . ucfirst($request->status));
    }

    /**
     * Bulk Download all/filtered NIN Validation Requests as CSV
     */
    public function exportCsv(Request $request): StreamedResponse
    {
        $query = VerificationResult::whereIn('service_type', ['validation', 'nin_validation'])
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
                    ->orWhere('response_data->validation_reason', 'like', '%' . $q . '%')
                    ->orWhereHas('user', function ($uq) use ($q) {
                        $uq->where('fullname', 'like', '%' . $q . '%')
                           ->orWhere('email', 'like', '%' . $q . '%');
                    });
            });
        }

        $filename = 'nin_validation_requests_' . date('Y-m-d_His') . '.csv';

        return response()->streamDownload(function () use ($query) {
            $handle = fopen('php://output', 'w');

            // Header Row
            fputcsv($handle, [
                'ID',
                'Reference ID',
                'Engine',
                'NIN / Identifier',
                'Reason / Category',
                'Status',
                'Admin Note',
                'Applicant Name',
                'Applicant Email',
                'Applicant Phone',
                'Amount Paid',
                'Submitted At'
            ]);

            $query->chunk(100, function ($rows) use ($handle) {
                foreach ($rows as $row) {
                    $d = $row->response_data ?? [];
                    fputcsv($handle, [
                        $row->id,
                        $row->reference_id ?? '',
                        $row->provider_name ?? 'ADMIN_MANUAL',
                        $row->identifier,
                        $d['validation_reason'] ?? ($d['category'] ?? 'NIN Validation'),
                        $row->status,
                        $row->admin_note ?? '',
                        $row->user->fullname ?? ($d['user_name'] ?? 'N/A'),
                        $row->user->email ?? ($d['user_email'] ?? 'N/A'),
                        $row->user->phone ?? ($d['user_phone'] ?? 'N/A'),
                        $d['amount_paid'] ?? 700,
                        $row->created_at->format('Y-m-d H:i:s'),
                    ]);
                }
            });

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }

    /**
     * Bulk Upload NIN Validation Results CSV
     */
    public function importCsv(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:csv,txt|max:5120',
        ]);

        $file = $request->file('file');
        $handle = fopen($file->getRealPath(), 'r');

        if (!$handle) {
            return back()->with('error', 'Unable to open uploaded CSV file.');
        }

        $headers = fgetcsv($handle);
        if (!$headers) {
            fclose($handle);
            return back()->with('error', 'The uploaded file appears to be empty.');
        }

        $headers = array_map(fn($h) => strtolower(trim((string) $h)), $headers);

        $refIdx = array_search('reference id', $headers);
        if ($refIdx === false) $refIdx = array_search('reference', $headers);
        if ($refIdx === false) $refIdx = array_search('ref', $headers);

        $ninIdx = array_search('nin / identifier', $headers);
        if ($ninIdx === false) $ninIdx = array_search('nin', $headers);
        if ($ninIdx === false) $ninIdx = array_search('identifier', $headers);

        $statusIdx = array_search('status', $headers);
        $noteIdx   = array_search('admin note', $headers);
        if ($noteIdx === false) $noteIdx = array_search('note', $headers);
        if ($noteIdx === false) $noteIdx = array_search('remarks', $headers);

        if ($statusIdx === false || ($refIdx === false && $ninIdx === false)) {
            fclose($handle);
            return back()->with('error', 'Invalid CSV format. Header must contain at least "Reference ID" (or "NIN") and "Status".');
        }

        $processed = 0;
        $failedCount = 0;
        $successCount = 0;
        $refundCount = 0;
        $wallet = app(WalletService::class);

        while (($row = fgetcsv($handle)) !== false) {
            if (empty(array_filter($row))) continue;

            $reference = $refIdx !== false ? trim((string) ($row[$refIdx] ?? '')) : '';
            $nin = $ninIdx !== false ? trim((string) ($row[$ninIdx] ?? '')) : '';
            $statusRaw = trim((string) ($row[$statusIdx] ?? ''));
            $adminNote = $noteIdx !== false ? trim((string) ($row[$noteIdx] ?? '')) : null;

            $normStatus = strtolower($statusRaw);
            $finalStatus = match($normStatus) {
                'successful', 'success', 'cleared', 'completed', 'valid', 'validated', 'passed' => 'successful',
                'failed', 'fail', 'rejected', 'reject', 'invalid' => 'failed',
                'waiting_for_review', 'under review', 'review', 'in_progress', 'pending' => 'waiting_for_review',
                default => null,
            };

            if (!$finalStatus) continue;

            $query = VerificationResult::whereIn('service_type', ['validation', 'nin_validation']);
            if (!empty($reference)) {
                $query->where('reference_id', $reference);
            } elseif (!empty($nin)) {
                $query->where(function($q) use ($nin) {
                    $q->where('identifier', $nin)
                      ->orWhere('response_data->nin', $nin);
                });
            } else {
                continue;
            }

            $record = $query->first();
            if (!$record) continue;

            $oldStatus = $record->status;
            $d = $record->response_data ?? [];

            $record->status = $finalStatus;
            if ($adminNote) {
                $record->admin_note = $adminNote;
            }

            // Refund if transitioned to failed
            if ($finalStatus === 'failed' && $oldStatus !== 'failed') {
                $user = User::find($record->user_id);
                if ($user) {
                    $price = (float) ($d['amount_paid'] 
                        ?? \App\Models\VerificationPrice::first()->validation_price 
                        ?? 700);
                    $ref = $wallet->failAndRefund(
                        $user,
                        $price,
                        'NIN Validation Rejected (Bulk Update): ' . ($adminNote ?: 'Request unsuccessful'),
                        $record->reference_id
                    );
                    if ($ref['ok'] ?? false) {
                        $d['refunded'] = true;
                        $d['refunded_amount'] = $price;
                        $d['refunded_at'] = now()->toDateTimeString();
                        $refundCount++;
                    }
                }
            }

            $record->response_data = $d;
            $record->save();

            $processed++;
            if ($finalStatus === 'successful') $successCount++;
            if ($finalStatus === 'failed') $failedCount++;
        }

        fclose($handle);

        return back()->with('success', "Bulk update complete! Processed: {$processed} records. Successful: {$successCount}, Failed: {$failedCount} (Refunded: {$refundCount}).");
    }

    /**
     * Manually sync a validation request from Robosttech API
     */
    public function syncRobosttech($id)
    {
        $verification = VerificationResult::findOrFail($id);
        $apiCenter = ApiCenter::first();

        if (!$apiCenter || !$apiCenter->robosttech_api_key) {
            return back()->with('error', 'Robosttech API credentials are not configured in Admin Settings.');
        }

        $controller = new VerificationController();
        $endpoint = $controller->resolveRobostEndpoint(
            (string) ($apiCenter->robosttech_endpoint_validation ?: 'https://robosttech.com/api'),
            'validation'
        );

        try {
            $response = Http::timeout(30)->withHeaders([
                'api-key' => $apiCenter->robosttech_api_key,
                'Content-Type' => 'application/json'
            ])->post($endpoint, [
                'nin' => $verification->identifier,
                'number' => $verification->identifier,
            ]);

            if ($response->successful()) {
                $data = $response->json();
                $responseData = $verification->response_data ?? [];
                $responseData['robosttech_sync'] = $data;

                $apiStatus = strtolower((string) ($data['status'] ?? $data['data']['status'] ?? ''));

                if (in_array($apiStatus, ['successful', 'success', 'cleared', 'completed', 'valid', 'true', '1'])) {
                    $verification->status = 'successful';
                    $verification->admin_note = $data['message'] ?? 'Validated via Robosttech API sync.';
                } elseif (in_array($apiStatus, ['failed', 'rejected', 'error', 'invalid', 'false', '0'])) {
                    $verification->status = 'failed';
                    $verification->admin_note = $data['message'] ?? 'Rejected via Robosttech API sync.';
                    if (empty($responseData['refunded'])) {
                        $user = User::find($verification->user_id);
                        if ($user) {
                            $refundAmount = (float) ($responseData['amount_paid'] ?? \App\Models\VerificationPrice::first()->validation_price ?? 700);
                            $wallet = app(WalletService::class);
                            $ref = $wallet->failAndRefund(
                                $user,
                                $refundAmount,
                                'NIN Validation Rejected (API Sync): ' . $verification->admin_note,
                                $verification->reference_id
                            );
                            if ($ref['ok'] ?? false) {
                                $responseData['refunded'] = true;
                                $responseData['refunded_amount'] = $refundAmount;
                                $responseData['refunded_at'] = now()->toDateTimeString();
                            }
                        }
                    }
                }

                $verification->response_data = $responseData;
                $verification->save();

                return back()->with('success', 'Robosttech API sync completed. Current Status: ' . ucfirst($verification->status));
            }

            return back()->with('error', 'Robosttech API returned error (HTTP ' . $response->status() . '): ' . ($response->json()['message'] ?? 'Unknown response'));
        } catch (\Throwable $e) {
            return back()->with('error', 'Sync failed: ' . $e->getMessage());
        }
    }
}
