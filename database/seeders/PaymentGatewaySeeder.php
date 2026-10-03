<?php

namespace Database\Seeders;

use App\Models\PaymentGateway;
use Illuminate\Database\Seeder;

class PaymentGatewaySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $gateways = [
            [
                'name' => 'paystack',
                'display_name' => 'Paystack',
                'is_active' => true,
                'priority' => 1,
                'logo_url' => '/images/gateways/paystack.svg',
            ],
            [
                'name' => 'flutterwave',
                'display_name' => 'Flutterwave',
                'is_active' => true,
                'priority' => 2,
                'logo_url' => '/images/gateways/flutterwave.svg',
            ],
            [
                'name' => 'monnify',
                'display_name' => 'Monnify',
                'is_active' => true,
                'priority' => 3,
                'logo_url' => '/images/gateways/monnify.svg',
            ],
            [
                'name' => 'payvessel',
                'display_name' => 'Payvessel',
                'is_active' => true,
                'priority' => 4,
                'logo_url' => '/images/gateways/payvessel.svg',
            ],
            [
                'name' => 'palmpay',
                'display_name' => 'PalmPay',
                'is_active' => true,
                'priority' => 5,
                'logo_url' => '/images/gateways/palmpay.svg',
            ],
        ];

        foreach ($gateways as $gw) {
            PaymentGateway::updateOrCreate(
                ['name' => $gw['name']],
                $gw
            );
        }
    }
}
