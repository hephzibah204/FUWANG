<?php

namespace App\Services\VirtualAccounts\Providers;

use App\Models\User;
use App\Models\VirtualAccount;
use App\Services\VirtualAccounts\Dto\VirtualAccountCreationResult;

interface VirtualAccountProvider
{
    public function name(): string;

    public function supportsVirtualAccounts(): bool;

    public function create(User $user): VirtualAccountCreationResult;

    public function syncStatus(VirtualAccount $virtualAccount): ?VirtualAccountCreationResult;
}

