<?php

namespace App\Services\VirtualAccounts\Providers;

use App\Models\ApiCenter;
use App\Models\PaymentGateway;

abstract class AbstractHttpProvider implements VirtualAccountProvider
{
    protected function getGatewayConfig(string $gatewayName): array
    {
        $apiCenter = ApiCenter::first();
        $gw = PaymentGateway::where('name', $gatewayName)->first();
        return [
            'apiCenter' => $apiCenter,
            'gateway' => $gw,
            'config' => $gw?->config ?? [],
        ];
    }
}

