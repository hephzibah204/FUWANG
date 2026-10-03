<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ApiCenter;
use App\Models\PaymentWebhookEvent;
use App\Models\User;
use App\Support\DbTable;
use App\Support\PaymentProviderCredentials;
use App\Jobs\ProcessPaymentWebhookEvent;
use Illuminate\Database\Schema\Blueprint;
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

    private function ensureWebhookEventsTable(): void
    {
        if (!Schema::hasTable('payment_webhook_events')) {
            try {
                Schema::create('payment_webhook_events', function (Blueprint $table) {
                    $table->id();
                    $table->string('provider', 50)->index();
                    $table->string('event_type', 100)->nullable();
                    $table->string('provider_event_id', 191)->nullable()->index();
                    $table->string('reference', 191)->nullable()->index();
                    $table->string('email', 191)->nullable()->index();
                    $table->decimal('amount', 18, 2)->nullable();
                    $table->string('currency', 10)->nullable();
                    $table->boolean('signature_valid')->default(false);
                    $table->text('signature')->nullable();
                    $table->json('payload')->nullable();
                    $table->string('processing_status', 50)->default('pending')->index();
                    $table->text('processing_error')->nullable();
                    $table->timestamp('processed_at')->nullable();
                    $table->timestamps();

                    $table->index(['provider', 'provider_event_id'], 'idx_pwe_provider_event');
                });
            } catch (\Throwable $e) {
                Log::warning('Unable to auto-create payment_webhook_events table: ' . $e->getMessage());
            }
        }
    }

    private function enqueueEvent(PaymentWebhookEvent $event): void
    {
        try {
            ProcessPaymentWebhookEvent::dispatchSync($event->id);
        } catch (\Throwable $e) {
            Log::error('Synchronous payment processing encountered error, falling back to background queue: ' . $e->getMessage(), [
                'event_id' => $event->id,
            ]);
            ProcessPaymentWebhookEvent::dispatch($event->id);
        }
    }

    private function eventAlreadyLogged(string $provider, ?string $providerEventId): bool
    {
        if (!$providerEventId) {
            return false;
        }
        $this->ensureWebhookEventsTable();
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
        $this->ensureWebhookEventsTable();

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
        $signature = $this->getHeaderValue($request, 'PAYVESSEL_HTTP_SIGNATURE')
            ?: $this->getHeaderValue($request, 'payvessel-http-signature')
            ?: $request->header('payvessel-http-signature')
            ?: $request->header('PAYVESSEL_HTTP_SIGNATURE');

        $apiCenter = ApiCenter::first();
        $pv = PaymentProviderCredentials::payvessel($apiCenter);
        $secret = $pv['secret_key'] ?? ($apiCenter?->payvessel_secret_key ?: config('services.payvessel.secret_key'));
        if (!$secret && Schema::hasTable('payvessel_details')) {
            $secret = DB::table('payvessel_details')->value('payvessel_secret_key');
        }

        if (!$secret) {
            Log::error('Payvessel credentials not found in ApiCenter or services config');
            return response()->json(['message' => 'API credentials not found'], 400);
        }

        $hash = hash_hmac('sha512', $payload, $secret);
        $signatureValid = $signature && hash_equals(strtolower((string) $hash), strtolower((string) $signature));

        if (!$signatureValid) {
            Log::warning('Payvessel signature validation failed', [
                'expected' => $hash,
                'received' => $signature,
            ]);
            return response()->json(['message' => 'Permission denied'], 403);
        }

        $data = json_decode($payload, true) ?: [];
        $reference = $data['transaction']['reference'] ?? $data['reference'] ?? null;
        $settlementAmount = floatval($data['order']['settlement_amount'] ?? $data['transaction']['amount'] ?? $data['order']['amount'] ?? $data['amount'] ?? 0);
        $email = $data['customer']['email'] ?? $data['email'] ?? null;
        $accountNumber = $data['order']['bank_account'] ?? $data['order']['account_number'] ?? $data['transaction']['bank_account'] ?? $data['bank_account'] ?? null;

        if (!$email && $accountNumber && Schema::hasTable('bank_details')) {
            $email = DB::table('bank_details')->where('psb9', $accountNumber)->value('email');
        }

        if (!$reference) {
            return response()->json(['message' => 'Invalid payload: missing reference'], 400);
        }

        $event = $this->storeEvent(
            'payvessel',
            (string) ($data['event'] ?? 'transfer.success'),
            (string) ($data['transaction']['id'] ?? $reference),
            (string) $reference,
            $email ? (string) $email : null,
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
        $signature = $this->getHeaderValue($request, 'PAYMENTPOINT_SIGNATURE')
            ?: $this->getHeaderValue($request, 'paymentpoint-signature')
            ?: $request->header('paymentpoint-signature')
            ?: $request->header('PAYMENTPOINT_SIGNATURE');
        
        $apiCenter = ApiCenter::first();
        $secret = $apiCenter?->paypoint_secret_key;
        if (!$secret && Schema::hasTable('paypoint_details')) {
            $secret = DB::table('paypoint_details')->value('paypoint_secret_key');
        }
        if (!$secret) {
            $secret = config('services.paymentpoint.secret_key') ?: config('services.palmpay.secret_key');
        }
        if (!$secret) {
            return response()->json(['message' => 'API credentials not found'], 400);
        }

        $hash = hash_hmac('sha256', $payload, $secret);

        if (!$signature || !hash_equals(strtolower((string) $hash), strtolower((string) $signature))) {
            return response()->json(['message' => 'Invalid signature'], 403);
        }

        $data = json_decode($payload, true) ?: [];
        $reference = $data['transaction_id'] ?? $data['reference'] ?? null;
        $settlementAmount = floatval($data['settlement_amount'] ?? $data['amount'] ?? 0);
        $email = $data['customer']['email'] ?? $data['email'] ?? null;
        $status = $data['transaction_status'] ?? $data['status'] ?? null;
        $accountNumber = $data['account_number'] ?? $data['bank_account_number'] ?? $data['recipient_account_number'] ?? null;

        if (!$email && $accountNumber && Schema::hasTable('bank_details')) {
            $email = DB::table('bank_details')->where('palmpay', $accountNumber)->value('email');
        }

        if (!$reference) {
            return response()->json(['message' => 'Invalid payload: missing reference'], 400);
        }

        if ($status && !in_array(strtolower((string) $status), ['success', 'successful', 'completed'], true)) {
            return response()->json(['message' => 'Ignored non-success transaction'], 200);
        }

        $event = $this->storeEvent(
            $provider,
            (string) ($data['event'] ?? 'transfer.success'),
            (string) ($data['transaction_id'] ?? $reference),
            (string) $reference,
            $email ? (string) $email : null,
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
        $sig = $this->getHeaderValue($request, 'monnify-signature')
            ?: $this->getHeaderValue($request, 'MONNIFY_SIGNATURE')
            ?: $request->header('monnify-signature');

        $apiCenter = ApiCenter::first();
        $clientSecret = PaymentProviderCredentials::monnify($apiCenter)['secret_key'];
        if (!$clientSecret) {
            return response()->json(['message' => 'API credentials not found'], 400);
        }

        $calc = hash_hmac('sha512', $payload, $clientSecret);
        if (!$sig || !hash_equals(strtolower((string) $calc), strtolower((string) $sig))) {
            $data = json_decode($payload, true) ?: [];
            $this->storeEvent('monnify', (string) ($data['eventType'] ?? ''), null, null, null, null, null, false, $sig, (array) $data);
            return response()->json(['message' => 'Invalid signature'], 403);
        }

        $data = json_decode($payload, true) ?: [];
        $eventType = (string) ($data['eventType'] ?? '');
        $eventData = (array) ($data['eventData'] ?? []);

        $reference = (string) ($eventData['transactionReference'] ?? $eventData['paymentReference'] ?? '');
        $email = (string) (($eventData['customer']['email'] ?? $eventData['customerEmailAddress'] ?? '') ?: '');

        $accountNumber = $eventData['destinationAccountInformation']['accountNumber'] ?? null;
        if ($email === '' && $accountNumber && Schema::hasTable('bank_details')) {
            $email = (string) (DB::table('bank_details')
                ->where('Wema_account', $accountNumber)
                ->orWhere('Moniepoint_account', $accountNumber)
                ->orWhere('Sterling_account', $accountNumber)
                ->orWhere('GTBank_account', $accountNumber)
                ->value('email') ?? '');
        }

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
