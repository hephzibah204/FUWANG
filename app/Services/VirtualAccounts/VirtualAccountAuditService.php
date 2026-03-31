<?php

namespace App\Services\VirtualAccounts;

use App\Models\VirtualAccount;
use App\Models\VirtualAccountAuditLog;
use Illuminate\Support\Facades\Log;

class VirtualAccountAuditService
{
    public function log(?VirtualAccount $account, int $userId, string $gateway, string $action, string $status, ?string $message = null, ?array $context = null): void
    {
        $payload = [
            'virtual_account_id' => $account?->id,
            'user_id' => $userId,
            'gateway' => $gateway,
            'action' => $action,
            'status' => $status,
            'message' => $message,
            'context' => $context,
            'created_at' => now(),
        ];

        VirtualAccountAuditLog::create($payload);

        $level = $status === 'failed' ? 'warning' : 'info';
        Log::{$level}('Virtual account audit', [
            'user_id' => $userId,
            'gateway' => $gateway,
            'action' => $action,
            'status' => $status,
            'message' => $message,
        ]);
    }
}

