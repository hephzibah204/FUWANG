<?php

namespace App\Http\Controllers\Service;

use App\Http\Controllers\Controller;
use App\Models\VerificationResult;
use App\Models\ApiCenter;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;

class PdfReportController extends Controller
{
    public function verificationReport(int $id)
    {
        $result = VerificationResult::findOrFail($id);

        if ($result->user_id !== Auth::id()) {
            abort(403);
        }

        $pdf = Pdf::loadView('pdf.verification_report', compact('result'));

        return $pdf->download('Verification_Report_' . $result->reference_id . '.pdf');
    }

    public function ninSlip(int $id, string $type)
    {
        $result = VerificationResult::findOrFail($id);
        if ($result->user_id !== Auth::id()) {
            abort(403);
        }
        if (!in_array($type, ['standard_slip', 'regular_slip', 'premium_slip', 'vnin_slip'])) {
            abort(404);
        }

        // Prefer official DataVerify slip endpoints if configured
        try {
            $apiCenter = ApiCenter::first();
            $mode = $result->response_data['_verification_mode'] ?? null;
            $isPhoneMode = $mode === 'phone';
            $nin = $result->response_data['nin'] ?? null;
            if (!$nin) {
                // Fallback: extract digits from identifier
                $nin = preg_replace('/\D+/', '', (string) $result->identifier);
                if ($nin && strlen($nin) !== 11) {
                    $nin = null;
                }
            }

            $endpoint = null;
            $expectsPhone = false;
            if ($apiCenter && $apiCenter->dataverify_api_key) {
                if ($type === 'premium_slip') {
                    $preferPhone = \App\Models\SystemSetting::get('dataverify_use_phone_slip_for_phone_mode', 'false') === 'true';
                    if ($preferPhone && $isPhoneMode && !empty($apiCenter->dataverify_endpoint_premium_slip_phone)) {
                        $endpoint = $apiCenter->dataverify_endpoint_premium_slip_phone;
                        $expectsPhone = true;
                    } elseif (!empty($apiCenter->dataverify_endpoint_premium_slip)) {
                        $endpoint = $apiCenter->dataverify_endpoint_premium_slip;
                    }
                } elseif ($type === 'standard_slip' && !empty($apiCenter->dataverify_endpoint_standard_slip)) {
                    $endpoint = $apiCenter->dataverify_endpoint_standard_slip;
                } elseif ($type === 'regular_slip' && !empty($apiCenter->dataverify_endpoint_regular_slip)) {
                    $endpoint = $apiCenter->dataverify_endpoint_regular_slip;
                } elseif ($type === 'vnin_slip' && !empty($apiCenter->dataverify_endpoint_vnin_slip)) {
                    $endpoint = $apiCenter->dataverify_endpoint_vnin_slip;
                }
            }

            $demoLookup = $result->response_data['_lookup'] ?? null;
            $isPremiumDemoEndpoint = $type === 'premium_slip'
                && $apiCenter
                && $apiCenter->dataverify_api_key
                && !empty($apiCenter->dataverify_endpoint_premium_slip)
                && (str_contains((string) $apiCenter->dataverify_endpoint_premium_slip, 'nin_premium_demo') || str_contains((string) $apiCenter->dataverify_endpoint_premium_slip, 'nin_premium_demo.php'));
            if (!$endpoint && $isPremiumDemoEndpoint && is_array($demoLookup)) {
                $endpoint = $apiCenter->dataverify_endpoint_premium_slip;
            }

            if ($endpoint) {
                // Build payload for DataVerify slip endpoints
                $payload = ['api_key' => $apiCenter->dataverify_api_key];
                if ($isPremiumDemoEndpoint) {
                    $gender = (string) ($demoLookup['gender'] ?? '');
                    $gender = strtolower($gender);
                    if ($gender === 'male') $gender = 'm';
                    if ($gender === 'female') $gender = 'f';
                    $payload['firstname'] = (string) ($demoLookup['firstname'] ?? '');
                    $payload['lastname'] = (string) ($demoLookup['lastname'] ?? '');
                    $payload['dob'] = (string) ($demoLookup['dob'] ?? '');
                    $payload['gender'] = $gender;
                } elseif ($expectsPhone) {
                    $phone = $result->response_data['telephoneno'] ?? $result->response_data['phone'] ?? null;
                    if (!$phone) {
                        // Fallback: try identifier as phone
                        $digits = preg_replace('/\D+/', '', (string) $result->identifier);
                        if ($digits && strlen($digits) >= 10) {
                            $phone = $digits;
                        }
                    }
                    if ($phone) {
                        $payload['phone'] = $phone;
                    }
                } else {
                    if ($nin) {
                        $payload['nin'] = $nin;
                    }
                }

                if (!$isPremiumDemoEndpoint) {
                    if ($expectsPhone && empty($payload['phone'])) {
                        throw new \Exception('Phone is required to generate slip.');
                    }
                    if (!$expectsPhone && empty($payload['nin'])) {
                        throw new \Exception('NIN is required to generate slip.');
                    }
                }

                $resp = Http::timeout(60)
                    ->withHeaders(['Content-Type' => 'application/json'])
                    ->post($endpoint, $payload);

                if ($resp->successful()) {
                    $ct = strtolower((string) ($resp->header('Content-Type') ?? ''));
                    $body = $resp->body();
                    // If provider returns a PDF directly
                    if (str_contains($ct, 'application/pdf') || str_starts_with($body, '%PDF')) {
                        $name = strtoupper($type) . '_' . $result->reference_id . '.pdf';
                        return response($body, 200, [
                            'Content-Type' => 'application/pdf',
                            'Content-Disposition' => 'attachment; filename="'.$name.'"',
                        ]);
                    }
                    // If JSON wrapper with base64 or URL
                    $json = $resp->json();
                    if (is_array($json)) {
                        // Common patterns: pdf_base64, pdf, file, url
                        $b64 = $json['pdf_base64'] ?? $json['pdf'] ?? null;
                        if (is_string($b64) && preg_match('/^[A-Za-z0-9+\/=\r\n]+$/', $b64)) {
                            $bin = base64_decode($b64, true);
                            if ($bin !== false) {
                                $name = strtoupper($type) . '_' . $result->reference_id . '.pdf';
                                return response($bin, 200, [
                                    'Content-Type' => 'application/pdf',
                                    'Content-Disposition' => 'attachment; filename="'.$name.'"',
                                ]);
                            }
                        }
                        $url = $json['url'] ?? $json['file'] ?? null;
                        if (is_string($url) && filter_var($url, FILTER_VALIDATE_URL)) {
                            $fileResp = Http::timeout(60)->get($url);
                            if ($fileResp->successful()) {
                                $name = strtoupper($type) . '_' . $result->reference_id . '.pdf';
                                return response($fileResp->body(), 200, [
                                    'Content-Type' => 'application/pdf',
                                    'Content-Disposition' => 'attachment; filename="'.$name.'"',
                                ]);
                            }
                        }
                    }
                    // Fall through to internal rendering if not a recognizable PDF
                }
            }
        } catch (\Throwable $e) {
            // On any error, fall back to internal slip rendering
        }

        // Internal slip rendering fallback
        $view = match ($type) {
            'premium_slip' => 'pdf.nin_premium_slip',
            'regular_slip' => 'pdf.nin_regular_slip',
            'vnin_slip' => 'pdf.nin_vnin_slip',
            default => 'pdf.nin_standard_slip',
        };
        $pdf = Pdf::loadView($view, compact('result'));
        $name = strtoupper($type) . '_' . $result->reference_id . '.pdf';
        return $pdf->download($name);
    }
}
