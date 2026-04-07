<?php
namespace App\Services;

class VuvaaStatusMapper
{
    private const STATUS_CODES = [
        '00' => ['status' => 'success', 'message' => 'Verification Successful', 'uiState' => 'completed'],
        '51' => ['status' => 'error', 'message' => 'Insufficient units — please top up wallet', 'uiState' => 'error'],
        '99' => ['status' => 'pending', 'message' => 'Transaction incomplete — retrying...', 'uiState' => 'verifying'],
    ];
    
    public static function map(string $statusCode): array
    {
        return self::STATUS_CODES[$statusCode] ?? [
            'status' => 'unknown',
            'message' => 'Unknown status: ' . $statusCode,
            'uiState' => 'error'
        ];
    }
}
