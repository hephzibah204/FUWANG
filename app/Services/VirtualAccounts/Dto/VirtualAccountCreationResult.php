<?php

namespace App\Services\VirtualAccounts\Dto;

class VirtualAccountCreationResult
{
    public function __construct(
        public bool $ok,
        public ?string $gateway = null,
        public ?string $accountNumber = null,
        public ?string $bankName = null,
        public ?string $accountName = null,
        public string $currency = 'NGN',
        public string $status = 'pending',
        public ?string $reference = null,
        public ?string $providerCustomerReference = null,
        public ?string $providerAccountReference = null,
        public ?array $meta = null,
        public ?string $message = null,
    ) {
    }
}

