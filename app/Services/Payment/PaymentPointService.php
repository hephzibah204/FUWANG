<?php

namespace App\Services\Payment;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * PaymentPoint Payment Gateway Integration Service
 * 
 * Documentation: PaymentPoint API Documentation
 * 
 * Prerequisites:
 * - PaymentPoint merchant account with API credentials
 * - API key and secret key from PaymentPoint dashboard
 * - Webhook endpoint configured for payment notifications
 */
class PaymentPointService
{
    protected string $baseUrl;
    protected string $apiKey;
    protected string $secretKey;

    public function __construct(array $config)
    {
        $this->apiKey = $config['api_key'] ?? '';
        $this->secretKey = $config['secret_key'] ?? '';
        
        // PaymentPoint base URL (sandbox or live)
        $this->baseUrl = $this->isLiveMode() 
            ? 'https://api.paymentpoint.com' 
            : 'https://sandbox-api.paymentpoint.com';
    }

    /**
     * Check if we're in live mode based on API key format
     */
    private function isLiveMode(): bool
    {
        return !str_contains($this->apiKey, 'test') && !str_contains($this->apiKey, 'sandbox');
    }

    /**
     * Generate access token for API requests
     */
    private function getAccessToken(): ?string
    {
        if (empty($this->apiKey) || empty($this->secretKey)) {
            Log::warning('PaymentPoint API credentials not configured');
            return null;
        }

        try {
            $response = Http::withBasicAuth($this->apiKey, $this->secretKey)
                ->timeout(30)
                ->post("{$this->baseUrl}/v1/auth/token");

            if ($response->successful()) {
                $data = $response->json();
                return $data['access_token'] ?? $data['token'] ?? null;
            }

            Log::error('PaymentPoint auth failed', [
                'status' => $response->status(),
                'body' => $response->json()
            ]);
            
            return null;
        } catch (\Exception $e) {
            Log::error('PaymentPoint auth exception', ['error' => $e->getMessage()]);
            return null;
        }
    }

    /**
     * Initialize PaymentPoint checkout session
     * 
     * @param string $amount Amount in NGN
     * @param string $reference Unique transaction reference
     * @param string $customerEmail Customer email
     * @param string $customerName Customer name
     * @param string $description Transaction description
     * @return array{ok: bool, message: string, data?: array}
     */
    public function initializeCheckout(
        string $amount,
        string $reference,
        string $customerEmail,
        string $customerName,
        string $description = 'Payment'
    ): array {
        $token = $this->getAccessToken();
        if (!$token) {
            return [
                'ok' => false,
                'message' => 'Unable to authenticate with PaymentPoint'
            ];
        }

        $payload = [
            'amount' => $amount,
            'currency' => 'NGN',
            'reference' => $reference,
            'description' => $description,
            'customer' => [
                'email' => $customerEmail,
                'name' => $customerName
            ],
            'redirect_url' => url('/payment/callback/paymentpoint'),
            'callback_url' => url('/webhooks/paymentpoint'),
            'payment_methods' => ['card', 'bank_transfer', 'ussd']
        ];

        try {
            $response = Http::withToken($token)
                ->timeout(30)
                ->post("{$this->baseUrl}/v1/payments/initialize", $payload);

            if ($response->successful()) {
                $data = $response->json();
                $checkoutUrl = $data['checkout_url'] ?? $data['data']['checkout_url'] ?? null;
                $paymentToken = $data['payment_token'] ?? $data['data']['payment_token'] ?? null;
                
                if ($checkoutUrl) {
                    return [
                        'ok' => true,
                        'message' => 'Checkout initialized successfully',
                        'data' => [
                            'checkout_url' => $checkoutUrl,
                            'payment_token' => $paymentToken,
                            'reference' => $reference,
                            'amount' => $amount,
                            'currency' => 'NGN'
                        ]
                    ];
                }
            }

            Log::error('PaymentPoint checkout initialization failed', [
                'status' => $response->status(),
                'body' => $response->json(),
                'payload' => $payload
            ]);

            return [
                'ok' => false,
                'message' => $response->json('message') ?? 'Failed to initialize checkout'
            ];
        } catch (\Exception $e) {
            Log::error('PaymentPoint checkout exception', [
                'error' => $e->getMessage(),
                'payload' => $payload
            ]);

            return [
                'ok' => false,
                'message' => 'An error occurred while initializing payment'
            ];
        }
    }

    /**
     * Verify transaction status
     * 
     * @param string $reference Transaction reference
     * @return array{ok: bool, message: string, data?: array}
     */
    public function verifyTransaction(string $reference): array
    {
        $token = $this->getAccessToken();
        if (!$token) {
            return [
                'ok' => false,
                'message' => 'Unable to authenticate with PaymentPoint'
            ];
        }

        try {
            $response = Http::withToken($token)
                ->timeout(30)
                ->get("{$this->baseUrl}/v1/payments/verify/{$reference}");

            if ($response->successful()) {
                $data = $response->json();
                $transaction = $data['data'] ?? $data ?? [];
                
                $status = strtolower($transaction['status'] ?? 'pending');
                $isSuccessful = in_array($status, ['success', 'successful', 'completed', 'paid']);
                
                return [
                    'ok' => true,
                    'message' => 'Transaction retrieved successfully',
                    'data' => [
                        'status' => $status,
                        'paid' => $isSuccessful,
                        'amount' => $transaction['amount'] ?? 0,
                        'currency' => $transaction['currency'] ?? 'NGN',
                        'reference' => $transaction['reference'] ?? $reference,
                        'transaction_id' => $transaction['id'] ?? $transaction['transaction_id'] ?? null,
                        'paid_at' => $transaction['paid_at'] ?? $transaction['created_at'] ?? null,
                        'payment_method' => $transaction['payment_method'] ?? null,
                        'customer' => [
                            'email' => $transaction['customer']['email'] ?? '',
                            'name' => $transaction['customer']['name'] ?? ''
                        ]
                    ]
                ];
            }

            Log::error('PaymentPoint transaction verification failed', [
                'status' => $response->status(),
                'body' => $response->json(),
                'reference' => $reference
            ]);

            return [
                'ok' => false,
                'message' => 'Failed to verify transaction'
            ];
        } catch (\Exception $e) {
            Log::error('PaymentPoint verification exception', [
                'error' => $e->getMessage(),
                'reference' => $reference
            ]);

            return [
                'ok' => false,
                'message' => 'An error occurred while verifying payment'
            ];
        }
    }

    /**
     * Handle webhook notification
     * 
     * @param array $payload Webhook payload
     * @return array{ok: bool, message: string}
     */
    public function handleWebhook(array $payload): array
    {
        // Verify webhook signature if available
        $signature = request()->header('X-PaymentPoint-Signature');
        if ($signature && !$this->verifyWebhookSignature($payload, $signature)) {
            return [
                'ok' => false,
                'message' => 'Invalid webhook signature'
            ];
        }

        $event = $payload['event'] ?? '';
        $data = $payload['data'] ?? [];
        
        if ($event === 'payment.successful' && !empty($data)) {
            return [
                'ok' => true,
                'message' => 'Payment successful',
                'data' => [
                    'reference' => $data['reference'] ?? '',
                    'status' => 'success',
                    'amount' => $data['amount'] ?? 0,
                    'currency' => $data['currency'] ?? 'NGN'
                ]
            ];
        }

        return [
            'ok' => false,
            'message' => 'Unhandled webhook event'
        ];
    }

    /**
     * Verify webhook signature
     * 
     * @param array $payload Webhook payload
     * @param string $signature Signature from header
     * @return bool
     */
    private function verifyWebhookSignature(array $payload, string $signature): bool
    {
        if (empty($this->secretKey)) {
            return false;
        }

        $computedSignature = hash_hmac('sha256', json_encode($payload), $this->secretKey);
        return hash_equals($computedSignature, $signature);
    }

    /**
     * Get JavaScript SDK configuration for frontend
     * 
     * @return array{ok: bool, message: string, data?: array}
     */
    public function getSdkConfig(): array
    {
        if (empty($this->apiKey)) {
            return [
                'ok' => false,
                'message' => 'PaymentPoint API key not configured'
            ];
        }

        return [
            'ok' => true,
            'message' => 'SDK configuration retrieved',
            'data' => [
                'api_key' => $this->apiKey,
                'is_live' => $this->isLiveMode(),
                'base_url' => $this->baseUrl
            ]
        ];
    }
}