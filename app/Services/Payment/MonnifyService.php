<?php

namespace App\Services\Payment;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Monnify Payment Gateway Integration Service
 * 
 * Documentation: https://developers.monnify.com/docs/monnify-checkout/
 * 
 * Prerequisites:
 * - Monnify account with API credentials
 * - Contract code from Monnify dashboard
 * - Public/Secret keys configured in PaymentGateway config
 */
class MonnifyService
{
    protected string $baseUrl;
    protected string $apiKey;
    protected string $secretKey;
    protected string $contractCode;

    public function __construct(array $config)
    {
        $this->apiKey = $config['api_key'] ?? '';
        $this->secretKey = $config['secret_key'] ?? '';
        $this->contractCode = $config['contract_code'] ?? '';
        
        // Monnify base URL (sandbox or live)
        $this->baseUrl = $this->isLiveMode() 
            ? 'https://api.monnify.com' 
            : 'https://sandbox.monnify.com';
    }

    /**
     * Check if we're in live mode based on API key format
     */
    private function isLiveMode(): bool
    {
        return !str_contains($this->apiKey, 'TEST');
    }

    /**
     * Generate access token for API requests
     */
    private function getAccessToken(): ?string
    {
        if (empty($this->apiKey) || empty($this->secretKey)) {
            Log::warning('Monnify API credentials not configured');
            return null;
        }

        try {
            $response = Http::withBasicAuth($this->apiKey, $this->secretKey)
                ->timeout(30)
                ->post("{$this->baseUrl}/api/v1/auth/login");

            if ($response->successful()) {
                $data = $response->json();
                return $data['responseBody']['accessToken'] ?? null;
            }

            Log::error('Monnify auth failed', [
                'status' => $response->status(),
                'body' => $response->json()
            ]);
            
            return null;
        } catch (\Exception $e) {
            Log::error('Monnify auth exception', ['error' => $e->getMessage()]);
            return null;
        }
    }

    /**
     * Initialize Monnify checkout session
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
                'message' => 'Unable to authenticate with Monnify'
            ];
        }

        if (empty($this->contractCode)) {
            return [
                'ok' => false,
                'message' => 'Monnify contract code not configured'
            ];
        }

        $payload = [
            'amount' => $amount,
            'currencyCode' => 'NGN',
            'paymentReference' => $reference,
            'paymentDescription' => $description,
            'contractCode' => $this->contractCode,
            'customer' => [
                'email' => $customerEmail,
                'name' => $customerName
            ],
            'redirectUrl' => url('/payment/callback/monnify'),
            'paymentMethods' => ['CARD', 'ACCOUNT_TRANSFER', 'USSD', 'PHONE_NUMBER']
        ];

        try {
            $response = Http::withToken($token)
                ->timeout(30)
                ->post("{$this->baseUrl}/api/v1/merchant/transactions/init-transaction", $payload);

            if ($response->successful()) {
                $data = $response->json();
                $checkoutUrl = $data['responseBody']['checkoutUrl'] ?? null;
                
                if ($checkoutUrl) {
                    return [
                        'ok' => true,
                        'message' => 'Checkout initialized successfully',
                        'data' => [
                            'checkout_url' => $checkoutUrl,
                            'transaction_reference' => $reference,
                            'payment_reference' => $data['responseBody']['paymentReference'] ?? $reference
                        ]
                    ];
                }
            }

            Log::error('Monnify checkout initialization failed', [
                'status' => $response->status(),
                'body' => $response->json(),
                'payload' => $payload
            ]);

            return [
                'ok' => false,
                'message' => $response->json('responseMessage') ?? 'Failed to initialize checkout'
            ];
        } catch (\Exception $e) {
            Log::error('Monnify checkout exception', [
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
     * @param string $transactionReference Transaction reference
     * @return array{ok: bool, message: string, data?: array}
     */
    public function verifyTransaction(string $transactionReference): array
    {
        $token = $this->getAccessToken();
        if (!$token) {
            return [
                'ok' => false,
                'message' => 'Unable to authenticate with Monnify'
            ];
        }

        try {
            $response = Http::withToken($token)
                ->timeout(30)
                ->get("{$this->baseUrl}/api/v1/merchant/transactions/{$transactionReference}");

            if ($response->successful()) {
                $data = $response->json();
                $transaction = $data['responseBody'] ?? [];
                
                $status = strtolower($transaction['paymentStatus'] ?? 'pending');
                $isSuccessful = in_array($status, ['paid', 'completed', 'successful']);
                
                return [
                    'ok' => true,
                    'message' => 'Transaction retrieved successfully',
                    'data' => [
                        'status' => $status,
                        'paid' => $isSuccessful,
                        'amount' => $transaction['amount'] ?? 0,
                        'currency' => $transaction['currencyCode'] ?? 'NGN',
                        'reference' => $transaction['paymentReference'] ?? $transactionReference,
                        'transaction_reference' => $transaction['transactionReference'] ?? $transactionReference,
                        'paid_on' => $transaction['paidOn'] ?? null,
                        'payment_method' => $transaction['paymentMethod'] ?? null,
                        'customer' => [
                            'email' => $transaction['customer']['email'] ?? '',
                            'name' => $transaction['customer']['name'] ?? ''
                        ]
                    ]
                ];
            }

            Log::error('Monnify transaction verification failed', [
                'status' => $response->status(),
                'body' => $response->json(),
                'reference' => $transactionReference
            ]);

            return [
                'ok' => false,
                'message' => 'Failed to verify transaction'
            ];
        } catch (\Exception $e) {
            Log::error('Monnify verification exception', [
                'error' => $e->getMessage(),
                'reference' => $transactionReference
            ]);

            return [
                'ok' => false,
                'message' => 'An error occurred while verifying payment'
            ];
        }
    }

    /**
     * Get JavaScript SDK configuration for frontend
     * 
     * @return array{ok: bool, message: string, data?: array}
     */
    public function getSdkConfig(): array
    {
        if (empty($this->apiKey) || empty($this->contractCode)) {
            return [
                'ok' => false,
                'message' => 'Monnify credentials not configured'
            ];
        }

        return [
            'ok' => true,
            'message' => 'SDK configuration retrieved',
            'data' => [
                'api_key' => $this->apiKey,
                'contract_code' => $this->contractCode,
                'is_live' => $this->isLiveMode(),
                'base_url' => $this->baseUrl
            ]
        ];
    }

    /**
     * Generate reserved account for customer (for bank transfer payments)
     * 
     * @param string $customerEmail Customer email
     * @param string $customerName Customer name
     * @param string $reference Unique reference
     * @return array{ok: bool, message: string, data?: array}
     */
    public function generateReservedAccount(
        string $customerEmail,
        string $customerName,
        string $reference
    ): array {
        $token = $this->getAccessToken();
        if (!$token) {
            return [
                'ok' => false,
                'message' => 'Unable to authenticate with Monnify'
            ];
        }

        $payload = [
            'accountReference' => $reference,
            'accountName' => $customerName,
            'currencyCode' => 'NGN',
            'contractCode' => $this->contractCode,
            'customerEmail' => $customerEmail,
            'customerName' => $customerName,
            'getAllAvailableBanks' => true
        ];

        try {
            $response = Http::withToken($token)
                ->timeout(30)
                ->post("{$this->baseUrl}/api/v1/bank-transfer/reserved-accounts", $payload);

            if ($response->successful()) {
                $data = $response->json();
                $accounts = $data['responseBody']['accounts'] ?? [];
                
                if (!empty($accounts)) {
                    return [
                        'ok' => true,
                        'message' => 'Reserved accounts generated successfully',
                        'data' => [
                            'reference' => $reference,
                            'accounts' => array_map(function ($account) {
                                return [
                                    'bank_name' => $account['bankName'] ?? '',
                                    'bank_code' => $account['bankCode'] ?? '',
                                    'account_number' => $account['accountNumber'] ?? '',
                                    'account_name' => $account['accountName'] ?? ''
                                ];
                            }, $accounts)
                        ]
                    ];
                }
            }

            Log::error('Monnify reserved account generation failed', [
                'status' => $response->status(),
                'body' => $response->json(),
                'payload' => $payload
            ]);

            return [
                'ok' => false,
                'message' => 'Failed to generate reserved accounts'
            ];
        } catch (\Exception $e) {
            Log::error('Monnify reserved account exception', [
                'error' => $e->getMessage(),
                'payload' => $payload
            ]);

            return [
                'ok' => false,
                'message' => 'An error occurred while generating accounts'
            ];
        }
    }
}