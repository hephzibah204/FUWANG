<?php

namespace App\Services;

use App\Models\User;
use App\Models\VerificationResult;
use App\Services\KycService;
use Illuminate\Support\Str;

class VerificationResultService
{
    public function create(
        User|int $user,
        string $serviceType,
        string $identifier,
        string $providerName,
        mixed $responseData,
        string $status = 'success',
        ?string $referencePrefix = null
    ): VerificationResult {
        $userId = $user instanceof User ? $user->id : $user;

        $res = VerificationResult::create([
            'user_id' => $userId,
            'service_type' => $serviceType,
            'identifier' => $identifier,
            'provider_name' => $providerName,
            'response_data' => $responseData,
            'status' => $status,
            'reference_id' => $this->generateReferenceId($referencePrefix ?: strtoupper($serviceType)),
        ]);

        if ($status === 'success') {
            $u = $user instanceof User ? $user : User::find($userId);
            if ($u) {
                app(KycService::class)->refreshUserTier($u);
            }
        }

        return $res;
    }

    public function generateReferenceId(string $prefix): string
    {
        $cleanPrefix = strtoupper(preg_replace('/[^A-Za-z0-9]/', '', $prefix));
        $cleanPrefix = $cleanPrefix !== '' ? $cleanPrefix : 'REF';
        return $cleanPrefix . '-' . Str::upper(Str::random(8));
    }
}
