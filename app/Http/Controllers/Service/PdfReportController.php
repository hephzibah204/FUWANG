<?php

namespace App\Http\Controllers\Service;

use App\Http\Controllers\Controller;
use App\Models\VerificationResult;
use App\Models\ApiCenter;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

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

        $providerName = strtolower((string) ($result->provider_name ?? ''));
        $isRobostTech = str_contains($providerName, 'robost');
        $fallbackReason = 'unknown';

        $result->response_data = $this->normalizeNinSlipPayload($result->response_data, (string) $result->identifier);

        $cachedResponse = $this->serveCachedNinSlip($result, $type);
        if ($cachedResponse !== null) {
            return $cachedResponse;
        }

        // Prefer official DataVerify slip endpoints if configured
        try {
            if ($isRobostTech) {
                $fallbackReason = 'provider_is_robosttech';
                Log::info('NIN slip remote generation skipped', [
                    'result_id' => $result->id,
                    'reference_id' => $result->reference_id,
                    'type' => $type,
                    'provider_name' => $result->provider_name,
                    'reason' => $fallbackReason,
                ]);
                throw new \RuntimeException('Skip remote slip generation for RobostTech results.');
            }
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
                    if ($preferPhone && $isPhoneMode) {
                        $endpoint = trim((string) ($apiCenter->dataverify_endpoint_premium_slip_phone ?? ''));
                        if ($endpoint === '') {
                            $endpoint = 'https://dataverify.com.ng/developers/nin_slips/nin_premium_phone';
                        }
                        $expectsPhone = true;
                    } else {
                        $endpoint = trim((string) ($apiCenter->dataverify_endpoint_premium_slip ?? ''));
                        if ($endpoint === '') {
                            $endpoint = 'https://dataverify.com.ng/developers/nin_slips/nin_premium';
                        }
                    }
                } elseif ($type === 'standard_slip') {
                    $endpoint = trim((string) ($apiCenter->dataverify_endpoint_standard_slip ?? ''));
                    if ($endpoint === '' || str_contains($endpoint, '/developers/standard_slip')) {
                        $endpoint = 'https://dataverify.com.ng/developers/nin_slips/nin_standard';
                    }
                } elseif ($type === 'regular_slip') {
                    $endpoint = trim((string) ($apiCenter->dataverify_endpoint_regular_slip ?? ''));
                    if ($endpoint === '' || str_contains($endpoint, '/developers/regular_slip')) {
                        $endpoint = 'https://dataverify.com.ng/developers/nin_slips/nin_regular';
                    }
                } elseif ($type === 'vnin_slip') {
                    $endpoint = trim((string) ($apiCenter->dataverify_endpoint_vnin_slip ?? ''));
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

            Log::info('NIN slip endpoint decision', [
                'result_id' => $result->id,
                'reference_id' => $result->reference_id,
                'type' => $type,
                'provider_name' => $result->provider_name,
                'verification_mode' => $mode,
                'is_phone_mode' => $isPhoneMode,
                'expects_phone' => $expectsPhone,
                'is_premium_demo_endpoint' => $isPremiumDemoEndpoint,
                'endpoint' => $endpoint,
                'nin_present' => !empty($nin),
                'api_center_present' => (bool) $apiCenter,
                'dataverify_key_present' => (bool) ($apiCenter && $apiCenter->dataverify_api_key),
            ]);

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
                        $fallbackReason = 'missing_phone_payload';
                        Log::warning('NIN slip fallback: required phone missing', [
                            'result_id' => $result->id,
                            'reference_id' => $result->reference_id,
                            'type' => $type,
                            'endpoint' => $endpoint,
                        ]);
                        throw new \Exception('Phone is required to generate slip.');
                    }
                    if (!$expectsPhone && empty($payload['nin'])) {
                        $fallbackReason = 'missing_nin_payload';
                        Log::warning('NIN slip fallback: required NIN missing', [
                            'result_id' => $result->id,
                            'reference_id' => $result->reference_id,
                            'type' => $type,
                            'endpoint' => $endpoint,
                        ]);
                        throw new \Exception('NIN is required to generate slip.');
                    }
                }

                Log::info('NIN slip remote request', [
                    'result_id' => $result->id,
                    'reference_id' => $result->reference_id,
                    'type' => $type,
                    'endpoint' => $endpoint,
                    'payload_keys' => array_keys($payload),
                ]);

                $resp = Http::timeout(60)
                    ->withHeaders(['Content-Type' => 'application/json'])
                    ->post($endpoint, $payload);

                if ($resp->successful()) {
                    $ct = strtolower((string) ($resp->header('Content-Type') ?? ''));
                    $body = $resp->body();
                    Log::info('NIN slip remote response', [
                        'result_id' => $result->id,
                        'reference_id' => $result->reference_id,
                        'type' => $type,
                        'endpoint' => $endpoint,
                        'status' => $resp->status(),
                        'content_type' => $ct,
                    ]);
                    // If provider returns a PDF directly
                    if (str_contains($ct, 'application/pdf') || str_starts_with($body, '%PDF')) {
                        $this->cacheNinSlipBinary($result, $type, $body, 'dataverify_direct_pdf');
                        return $this->buildPdfResponse($body, $type, (string) $result->reference_id);
                    }
                    // If JSON wrapper with base64 or URL
                    $json = $resp->json();
                    if (is_array($json)) {
                        // Common patterns: pdf_base64, pdf, file, url
                        $b64 = $json['pdf_base64'] ?? $json['pdf'] ?? null;
                        if (is_string($b64) && preg_match('/^[A-Za-z0-9+\/=\r\n]+$/', $b64)) {
                            $bin = base64_decode($b64, true);
                            if ($bin !== false) {
                                $this->cacheNinSlipBinary($result, $type, $bin, 'dataverify_pdf_base64');
                                return $this->buildPdfResponse($bin, $type, (string) $result->reference_id);
                            }
                        }
                        $url = $json['url'] ?? $json['file'] ?? null;
                        if (is_string($url) && filter_var($url, FILTER_VALIDATE_URL)) {
                            $fileResp = Http::timeout(60)->get($url);
                            if ($fileResp->successful()) {
                                $bin = $fileResp->body();
                                $this->cacheNinSlipBinary($result, $type, $bin, 'dataverify_remote_url');
                                return $this->buildPdfResponse($bin, $type, (string) $result->reference_id);
                            }
                        }
                    }
                    // Fall through to internal rendering if not a recognizable PDF
                    $fallbackReason = 'remote_success_but_unrecognized_pdf_format';
                    Log::warning('NIN slip fallback: remote success but unusable content', [
                        'result_id' => $result->id,
                        'reference_id' => $result->reference_id,
                        'type' => $type,
                        'endpoint' => $endpoint,
                    ]);
                } else {
                    $fallbackReason = 'remote_http_failure';
                    Log::warning('NIN slip remote request failed', [
                        'result_id' => $result->id,
                        'reference_id' => $result->reference_id,
                        'type' => $type,
                        'endpoint' => $endpoint,
                        'status' => $resp->status(),
                        'body' => $resp->body(),
                    ]);
                }
            } else {
                $fallbackReason = 'no_endpoint_configured';
                Log::warning('NIN slip fallback: endpoint not configured', [
                    'result_id' => $result->id,
                    'reference_id' => $result->reference_id,
                    'type' => $type,
                    'provider_name' => $result->provider_name,
                ]);
            }
        } catch (\Throwable $e) {
            // On any error, fall back to internal slip rendering
            Log::warning('NIN slip fallback exception', [
                'result_id' => $result->id,
                'reference_id' => $result->reference_id,
                'type' => $type,
                'provider_name' => $result->provider_name,
                'reason' => $fallbackReason,
                'error' => $e->getMessage(),
            ]);
        }

        // Internal slip rendering fallback
        Log::info('NIN slip internal PDF rendering', [
            'result_id' => $result->id,
            'reference_id' => $result->reference_id,
            'type' => $type,
            'provider_name' => $result->provider_name,
            'reason' => $fallbackReason,
        ]);
        $view = match ($type) {
            'premium_slip' => 'pdf.nin_premium_slip',
            'regular_slip' => 'pdf.nin_regular_slip',
            'vnin_slip' => 'pdf.nin_vnin_slip',
            default => 'pdf.nin_standard_slip',
        };
        $pdf = Pdf::loadView($view, compact('result'));
        $bin = $pdf->output();
        $this->cacheNinSlipBinary($result, $type, $bin, 'internal_fallback');
        return $this->buildPdfResponse($bin, $type, (string) $result->reference_id);
    }

    private function normalizeNinSlipPayload($payload, string $identifier): array
    {
        $data = is_array($payload) ? $payload : [];
        $nestedCandidates = [];
        foreach (['data', 'result', 'payload', 'response', 'record', 'details'] as $k) {
            if (isset($data[$k]) && is_array($data[$k])) {
                $nestedCandidates[] = $data[$k];
            }
        }

        $get = function (array $keys) use ($data, $nestedCandidates) {
            foreach ($keys as $key) {
                if (array_key_exists($key, $data) && $data[$key] !== null && $data[$key] !== '') {
                    return $data[$key];
                }
                foreach ($nestedCandidates as $nested) {
                    if (array_key_exists($key, $nested) && $nested[$key] !== null && $nested[$key] !== '') {
                        return $nested[$key];
                    }
                }
            }
            return null;
        };

        $nin = $get(['nin', 'NIN', 'nin_number', 'ninNo', 'nin_no', 'id_number']);
        $ninDigits = preg_replace('/\D+/', '', (string) ($nin ?? ''));
        if ($ninDigits === '' || strlen($ninDigits) !== 11) {
            $ninDigits = preg_replace('/\D+/', '', (string) $identifier);
        }
        if (strlen($ninDigits) === 11) {
            $data['nin'] = $ninDigits;
        }

        $data['firstname'] = $data['firstname'] ?? $get(['firstname', 'first_name', 'firstName', 'given_name', 'givenName']);
        $data['middlename'] = $data['middlename'] ?? $get(['middlename', 'middle_name', 'middleName']);
        $data['lastname'] = $data['lastname'] ?? $get(['lastname', 'last_name', 'lastName', 'surname']);

        $dob = $data['birthdate'] ?? $data['dob'] ?? $get(['birthdate', 'dob', 'dateOfBirth', 'date_of_birth']);
        if (is_string($dob) && $dob !== '') {
            $data['dob'] = $dob;
        }

        $gender = $data['gender'] ?? $get(['gender', 'sex']);
        if (is_string($gender) && $gender !== '') {
            $g = strtolower(trim($gender));
            if ($g === 'male') $g = 'm';
            if ($g === 'female') $g = 'f';
            $data['gender'] = strtoupper($g);
        }

        $photo = $get(['photo', 'image', 'passport', 'photo_base64', 'photoBase64', 'image_base64', 'imageBase64']);
        if (is_string($photo) && $photo !== '') {
            $data['photo'] = $photo;
        }

        $phone = $data['telephoneno'] ?? $data['phone'] ?? $get(['telephoneno', 'phone', 'phoneNumber', 'phone_number', 'msisdn']);
        if ($phone !== null && $phone !== '') {
            $data['phone'] = $phone;
        }

        return $data;
    }

    private function serveCachedNinSlip(VerificationResult $result, string $type)
    {
        $responseData = is_array($result->response_data) ? $result->response_data : [];
        $cached = data_get($responseData, '_cached_slips.' . $type);
        if (!is_array($cached)) {
            return null;
        }

        $disk = (string) ($cached['disk'] ?? 'local');
        $path = trim((string) ($cached['path'] ?? ''));
        if ($path === '') {
            return null;
        }

        if (!Storage::disk($disk)->exists($path)) {
            Log::warning('NIN slip cache entry points to missing file', [
                'result_id' => $result->id,
                'reference_id' => $result->reference_id,
                'type' => $type,
                'disk' => $disk,
                'path' => $path,
            ]);
            return null;
        }

        $bin = Storage::disk($disk)->get($path);
        Log::info('NIN slip served from cache', [
            'result_id' => $result->id,
            'reference_id' => $result->reference_id,
            'type' => $type,
            'disk' => $disk,
            'path' => $path,
            'source' => (string) ($cached['source'] ?? 'unknown'),
        ]);

        return $this->buildPdfResponse($bin, $type, (string) $result->reference_id);
    }

    private function cacheNinSlipBinary(VerificationResult $result, string $type, string $bin, string $source): void
    {
        $disk = 'local';
        $path = 'private/nin-slips/' . $result->user_id . '/' . $result->id . '/' . $type . '.pdf';

        try {
            Storage::disk($disk)->put($path, $bin);

            $responseData = is_array($result->response_data) ? $result->response_data : [];
            $responseData['_cached_slips'] = is_array($responseData['_cached_slips'] ?? null)
                ? $responseData['_cached_slips']
                : [];
            $responseData['_cached_slips'][$type] = [
                'disk' => $disk,
                'path' => $path,
                'source' => $source,
                'size_bytes' => strlen($bin),
                'cached_at' => now()->toIso8601String(),
            ];

            $result->response_data = $responseData;
            $result->save();

            Log::info('NIN slip cached to storage', [
                'result_id' => $result->id,
                'reference_id' => $result->reference_id,
                'type' => $type,
                'disk' => $disk,
                'path' => $path,
                'source' => $source,
                'size_bytes' => strlen($bin),
            ]);
        } catch (\Throwable $e) {
            Log::warning('NIN slip cache write failed', [
                'result_id' => $result->id,
                'reference_id' => $result->reference_id,
                'type' => $type,
                'source' => $source,
                'error' => $e->getMessage(),
            ]);
        }
    }

    private function buildPdfResponse(string $bin, string $type, string $referenceId)
    {
        $name = strtoupper($type) . '_' . $referenceId . '.pdf';

        return response($bin, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="' . $name . '"',
        ]);
    }
}
