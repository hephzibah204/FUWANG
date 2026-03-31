<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class VirtualCardService
{
    protected string $secretKey;
    protected string $baseUrl;

    public function __construct()
    {
        $this->secretKey = (string) config('services.flutterwave.secret', '');
        $this->baseUrl = 'https://api.flutterwave.com/v3';
    }

    /**
     * Create a virtual card using Flutterwave API.
     * 
     * @param User $user The user creating the card.
     * @param string $currency 'USD', 'NGN', etc.
     * @param float $amount Initial funding amount.
     * @param string $billingName Name on the card.
     * @return array{ok: bool, message: string, data?: array}
     */
    public function createCard(User $user, string $currency, float $amount, string $billingName): array
    {
        if (empty($this->secretKey)) {
            if (app()->environment(['local', 'testing'])) {
                Log::warning('Flutterwave secret key is not set. Falling back to simulated card creation for local/testing.');
                return $this->simulateCardCreation($currency, $amount, $billingName);
            }

            Log::error('Flutterwave secret key is not set. Virtual card creation blocked in production.');
            return [
                'ok' => false,
                'message' => 'Virtual card provider is not configured.',
            ];
        }

        try {
            $response = Http::withToken($this->secretKey)
                ->timeout(30)
                ->post("{$this->baseUrl}/virtual-cards", [
                    'currency' => strtoupper($currency),
                    'amount' => $amount,
                    'billing_name' => $billingName,
                    'billing_address' => '123 Main Street',
                    'billing_city' => 'Lagos',
                    'billing_state' => 'LA',
                    'billing_postal_code' => '100001',
                    'billing_country' => 'NG',
                    'callback_url' => url('/webhook/flutterwave/card'),
                ]);

            if ($response->successful()) {
                $data = $response->json('data');
                return [
                    'ok' => true,
                    'message' => 'Virtual card created successfully.',
                    'data' => [
                        'card_id' => $data['id'],
                        'card_number' => $data['card_pan'],
                        'cvv' => $data['cvv'],
                        'expiry' => $data['expiration'],
                        'balance' => $data['amount'],
                        'currency' => $data['currency'],
                        'status' => $data['is_active'] ? 'active' : 'inactive',
                    ]
                ];
            }

            Log::error('Flutterwave Virtual Card Creation Failed', [
                'status' => $response->status(),
                'body' => $response->json(),
            ]);

            return [
                'ok' => false,
                'message' => $response->json('message', 'Failed to create virtual card with provider.'),
            ];
        } catch (\Exception $e) {
            Log::error('Virtual Card API Exception', ['error' => $e->getMessage()]);
            return [
                'ok' => false,
                'message' => 'An error occurred while communicating with the card provider.',
            ];
        }
    }

    /**
     * Fund an existing virtual card.
     */
    public function fundCard(string $cardId, float $amount): array
    {
        if (empty($this->secretKey)) {
            if (app()->environment(['local', 'testing'])) {
                return ['ok' => true, 'message' => 'Simulated funding successful.', 'new_balance' => $amount];
            }

            return ['ok' => false, 'message' => 'Virtual card provider is not configured.'];
        }

        try {
            $response = Http::withToken($this->secretKey)
                ->timeout(30)
                ->post("{$this->baseUrl}/virtual-cards/{$cardId}/fund", [
                    'amount' => $amount,
                    'debit_currency' => 'NGN',
                ]);

            if ($response->successful()) {
                return [
                    'ok' => true,
                    'message' => 'Card funded successfully.',
                ];
            }

            Log::error('Flutterwave Virtual Card Funding Failed', [
                'status' => $response->status(),
                'body' => $response->json(),
            ]);

            return [
                'ok' => false,
                'message' => $response->json('message', 'Failed to fund virtual card.'),
            ];
        } catch (\Exception $e) {
            Log::error('Virtual Card Fund Exception', ['error' => $e->getMessage()]);
            return [
                'ok' => false,
                'message' => 'An error occurred while funding the card.',
            ];
        }
    }

    /**
     * Fetch card details (e.g. balance, status).
     */
    public function getCard(string $cardId): array
    {
        if (empty($this->secretKey)) {
            return ['ok' => false, 'message' => 'Provider not configured.'];
        }

        try {
            $response = Http::withToken($this->secretKey)
                ->timeout(30)
                ->get("{$this->baseUrl}/virtual-cards/{$cardId}");

            if ($response->successful()) {
                return [
                    'ok' => true,
                    'data' => $response->json('data'),
                ];
            }

            return ['ok' => false, 'message' => 'Could not fetch card details.'];
        } catch (\Exception $e) {
            return ['ok' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Fallback mock for local dev if no secret key is provided.
     */
    private function simulateCardCreation(string $currency, float $amount, string $billingName): array
    {
        // Use cryptographically secure random values
        $cardNumber = '4' . random_int(100, 999) . ' ' . random_int(1000, 9999) . ' ' . random_int(1000, 9999) . ' ' . random_int(1000, 9999);
        $cvv = str_pad((string) random_int(0, 999), 3, '0', STR_PAD_LEFT);
        
        return [
            'ok' => true,
            'message' => 'Simulated card created.',
            'data' => [
                'card_id' => 'sim_' . bin2hex(random_bytes(6)),
                'card_number' => $cardNumber,
                'cvv' => $cvv,
                'expiry' => now()->addYears(2)->format('m/y'),
                'balance' => $amount,
                'currency' => strtoupper($currency),
                'status' => 'active',
            ]
        ];
    }
}
