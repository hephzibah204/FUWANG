<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ApiCenter;
use App\Models\PaymentWebhookEvent;
use App\Models\User;
use App\Support\DbTable;
use App\Jobs\ProcessPaymentWebhookEvent;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class WebhookController extends Controller
{
    private function getHeaderValue(Request $request, string $name): ?string
    {
        $direct = $request->header($name);
        if (is_string($direct) && $direct !== '') {
            return $direct;
        }

        $alt = strtoupper(str_replace('-', '_', $name));
        $serverKey = 'HTTP_' . $alt;
        $serverVal = $request->server($serverKey);
        if (is_string($serverVal) && $serverVal !== '') {
            return $serverVal;
        }

        return null;
    }

    private function hasPaymentTransactionsTable(): bool
    {
        return Schema::hasTable('payment_transactions');
    }

    private function hasFundingHistoryTable(): bool
    {
        return Schema::hasTable('funding_history');
    }

    private function hasFundingsTable(): bool
    {
        return Schema::hasTable('fundings');
    }

    private function transactionAlreadyProcessed(string $reference): bool
    {
        if ($this->hasFundingsTable()) {
            return DB::table('fundings')->where('reference', $reference)->exists();
        }
        if ($this->hasPaymentTransactionsTable()) {
            return DB::table('payment_transactions')->where('reference', $reference)->exists();
        }
        return false;
    }

    private function enqueueEvent(PaymentWebhookEvent $event): void
    {
        ProcessPaymentWebhookEvent::dispatch($event->id);
    }

    private function eventAlreadyLogged(string $provider, ?string $providerEventId): bool
    {
        if (!$providerEventId) {
            return false;
        }
        return PaymentWebhookEvent::where('provider', $provider)
            ->where('provider_event_id', $providerEventId)
            ->exists();
    }

    private function storeEvent(
        string $provider,
        ?string $eventType,
        ?string $providerEventId,
        ?string $reference,
        ?string $email,
        ?float $amount,
        ?string $currency,
        bool $signatureValid,
        ?string $signature,
        array $payload
    ): PaymentWebhookEvent {
        $status = 'pending';
        if ($this->eventAlreadyLogged($provider, $providerEventId)) {
            $status = 'ignored';
        } elseif ($reference && $this->transactionAlreadyProcessed($reference)) {
            $status = 'ignored';
        }

        return PaymentWebhookEvent::create([
            'provider' => $provider,
            'event_type' => $eventType,
            'provider_event_id' => $providerEventId,
            'reference' => $reference,
            'email' => $email,
            'amount' => $amount,
            'currency' => $currency,
            'signature_valid' => $signatureValid,
            'signature' => $signature,
            'payload' => $payload,
            'processing_status' => $status,
            'processed_at' => $status === 'ignored' ? now() : null,
            'processing_error' => $status === 'ignored' ? 'Already processed' : null,
        ]);
    }

    private function logFunding(string $email, float $amount, string $reference, string $description, string $fundingType, string $fullname): void
    {
        if ($this->hasFundingsTable()) {
            DB::table('fundings')->insert([
                'email' => $email,
                'amount' => $amount,
                'reference' => $reference,
                'description' => $description,
                'funding_type' => $fundingType,
                'fullname' => $fullname,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        if ($this->hasPaymentTransactionsTable() && DbTable::isBaseTable('payment_transactions')) {
            DB::table('payment_transactions')->insert([
                'reference' => $reference,
                'email' => $email,
                'amount' => $amount,
                'description' => $description,
            ]);
        }

        if ($this->hasFundingHistoryTable() && DbTable::isBaseTable('funding_history')) {
            DB::table('funding_history')->insert([
                'funding_type' => $fundingType,
                'email' => $email,
                'fullname' => $fullname,
                'amount' => $amount,
                'date' => now(),
            ]);
        }
    }

    public function handlePayvessel(Request $request)
    {
        $payload = $request->getContent();
        $signature = $this->getHeaderValue($request, 'PAYVESSEL_HTTP_SIGNATURE');
        $ip = (string) $request->server('REMOTE_ADDR', $request->ip());
        $trustedIps = ['3.255.23.38', '162.246.254.36'];

        $apiCenter = ApiCenter::first();
        if (!$apiCenter || !$apiCenter->payvessel_secret_key) {
             return response()->json(['message' => 'API credentials not found'], 400);
        }

        $hash = hash_hmac('sha512', $payload, $apiCenter->payvessel_secret_key);

        if (!$signature || !hash_equals((string) $hash, (string) $signature) || !in_array($ip, $trustedIps, true)) {
            Log::warning("Payvessel signature validation failed");
            return response()->json(['message' => 'Permission denied'], 403);
        }

        $data = json_decode($payload, true);
        $reference = $data['transaction']['reference'] ?? null;
        $settlementAmount = floatval($data['order']['settlement_amount'] ?? 0);
        $email = $data['customer']['email'] ?? null;

        if (!$reference || !$email) {
            return response()->json(['message' => 'Invalid payload'], 400);
        }

        $event = $this->storeEvent(
            'payvessel',
            (string) ($data['event'] ?? 'transfer.success'),
            (string) ($data['transaction']['id'] ?? $reference),
            (string) $reference,
            (string) $email,
            (float) $settlementAmount,
            (string) ($data['order']['currency'] ?? 'NGN'),
            true,
            (string) $signature,
            (array) $data
        );
        if ($event->processing_status === 'pending') {
            $this->enqueueEvent($event);
        }

        return response()->json(['message' => 'Accepted'], 200);
    }

    private function handlePaymentpointLike(Request $request, string $provider)
    {
        $payload = $request->getContent();
        $signature = $this->getHeaderValue($request, 'PAYMENTPOINT_SIGNATURE');
        
        $apiCenter = ApiCenter::first();
        $secret = $apiCenter->paypoint_secret_key ?? null;
        if (!$secret && Schema::hasTable('paypoint_details')) {
            $secret = DB::table('paypoint_details')->value('paypoint_secret_key');
        }
        if (!$secret) {
            return response()->json(['message' => 'API credentials not found'], 400);
        }

        $hash = hash_hmac('sha256', $payload, $secret);

        if (!$signature || !hash_equals($hash, $signature)) {
            return response()->json(['message' => 'Invalid signature'], 403);
        }

        $data = json_decode($payload, true);
        $reference = $data['transaction_id'] ?? null;
        $settlementAmount = floatval($data['settlement_amount'] ?? 0);
        $email = $data['customer']['email'] ?? null;
        $status = $data['transaction_status'] ?? null;

        if (!$reference || !$email) {
            return response()->json(['message' => 'Invalid payload'], 400);
        }

        if ($status && !in_array((string) $status, ['success', 'successful', 'SUCCESS'], true)) {
            return response()->json(['message' => 'Ignored non-success transaction'], 200);
        }

        $event = $this->storeEvent(
            $provider,
            (string) ($data['event'] ?? 'transfer.success'),
            (string) ($data['transaction_id'] ?? $reference),
            (string) $reference,
            (string) $email,
            (float) $settlementAmount,
            (string) ($data['currency'] ?? 'NGN'),
            true,
            (string) $signature,
            (array) $data
        );
        if ($event->processing_status === 'pending') {
            $this->enqueueEvent($event);
        }

        return response()->json(['message' => 'Accepted'], 200);
    }

    public function handlePalmpay(Request $request)
    {
        return $this->handlePaymentpointLike($request, 'palmpay');
    }

    public function handlePaymentpoint(Request $request)
    {
        return $this->handlePaymentpointLike($request, 'paymentpoint');
    }

    public function handlePaystack(Request $request)
    {
        $payload = $request->getContent();
        $sig = (string) $request->header('x-paystack-signature', '');

        $apiCenter = ApiCenter::first();
        $secret = $apiCenter->paystack_secret_key ?? null;
        if (!$secret) {
            return response()->json(['message' => 'API credentials not found'], 400);
        }

        $calc = hash_hmac('sha512', $payload, $secret);
        if (!hash_equals($calc, $sig)) {
            return response()->json(['message' => 'Invalid signature'], 403);
        }

        $data = json_decode($payload, true);
        $evt = (string) ($data['event'] ?? '');
        $obj = $data['data'] ?? [];
        if ($evt !== 'charge.success') {
            $event = $this->storeEvent('paystack', $evt, (string) ($obj['id'] ?? null), null, null, null, null, true, $sig, (array) $data);
            return response()->json(['message' => 'Accepted'], 200);
        }

        $reference = (string) ($obj['reference'] ?? '');
        $status = strtolower((string) ($obj['status'] ?? ''));
        $currency = strtoupper((string) ($obj['currency'] ?? ''));
        $amountKobo = (int) ($obj['amount'] ?? 0);
        $email = (string) ($obj['customer']['email'] ?? '');

        $amount = round($amountKobo / 100, 2);

        $event = $this->storeEvent(
            'paystack',
            $evt,
            (string) ($obj['id'] ?? $reference),
            (string) $reference,
            (string) $email,
            (float) $amount,
            (string) $currency,
            true,
            $sig,
            (array) $data
        );
        if ($event->processing_status === 'pending') {
            $this->enqueueEvent($event);
        }

        return response()->json(['message' => 'Accepted'], 200);
    }

    public function handleFlutterwave(Request $request)
    {
        $payload = $request->getContent();
        $data = json_decode($payload, true);

        $secretHash = (string) (config('services.flutterwave.webhook_hash') ?: '');
        $incomingHash = (string) ($request->header('verif-hash', '') ?: $request->header('verifi-hash', ''));
        if ($secretHash === '') {
            $event = $this->storeEvent('flutterwave', (string) ($data['event'] ?? ''), null, null, null, null, null, false, $incomingHash ?: null, (array) $data);
            return response()->json(['message' => 'Webhook secret not configured'], 403);
        }
        // Use timing-safe comparison to prevent timing-attack enumeration of the secret
        if (!hash_equals($secretHash, $incomingHash)) {
            $event = $this->storeEvent('flutterwave', (string) ($data['event'] ?? ''), null, null, null, null, null, false, $incomingHash ?: null, (array) $data);
            return response()->json(['message' => 'Invalid signature'], 403);
        }

        $evt = (string) ($data['event'] ?? '');
        if ($evt !== 'charge.completed') {
            $event = $this->storeEvent('flutterwave', $evt, null, null, null, null, null, true, $incomingHash ?: null, (array) $data);
            return response()->json(['message' => 'Accepted'], 200);
        }

        $obj = $data['data'] ?? [];
        $txId = (string) ($obj['id'] ?? '');
        $txRef = (string) ($obj['tx_ref'] ?? '');

        $event = $this->storeEvent(
            'flutterwave',
            $evt,
            $txId !== '' ? $txId : null,
            $txRef !== '' ? $txRef : null,
            null,
            null,
            null,
            true,
            $incomingHash ?: null,
            (array) $data
        );
        if ($event->processing_status === 'pending') {
            $this->enqueueEvent($event);
        }

        return response()->json(['message' => 'Accepted'], 200);
    }

    public function handleMonnify(Request $request)
    {
        $payload = $request->getContent();
        $sig = $this->getHeaderValue($request, 'monnify-signature');

        $apiCenter = ApiCenter::first();
        $clientSecret = $apiCenter?->monnify_secret_key;
        if (!$clientSecret) {
            return response()->json(['message' => 'API credentials not found'], 400);
        }

        $calc = hash_hmac('sha512', $payload, $clientSecret);
        if (!$sig || !hash_equals((string) $calc, (string) $sig)) {
            $data = json_decode($payload, true) ?: [];
            $this->storeEvent('monnify', (string) ($data['eventType'] ?? ''), null, null, null, null, null, false, $sig, (array) $data);
            return response()->json(['message' => 'Invalid signature'], 403);
        }

        $data = json_decode($payload, true) ?: [];
        $eventType = (string) ($data['eventType'] ?? '');
        $eventData = (array) ($data['eventData'] ?? []);

        $reference = (string) ($eventData['transactionReference'] ?? $eventData['paymentReference'] ?? '');
        $email = (string) (($eventData['customer']['email'] ?? $eventData['customerEmailAddress'] ?? '') ?: '');

        $amount = null;
        if (isset($eventData['settlementAmount'])) {
            $amount = (float) $eventData['settlementAmount'];
        } elseif (isset($eventData['amountPaid'])) {
            $amount = (float) $eventData['amountPaid'];
        }

        $currency = (string) ($eventData['currency'] ?? 'NGN');

        $event = $this->storeEvent(
            'monnify',
            $eventType,
            $reference !== '' ? $reference : null,
            $reference !== '' ? $reference : null,
            $email !== '' ? $email : null,
            $amount,
            $currency,
            true,
            $sig,
            (array) $data
        );

        if ($event->processing_status === 'pending') {
            $this->enqueueEvent($event);
        }

        return response()->json(['message' => 'Accepted'], 200);
    }
}
