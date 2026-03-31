<?php

namespace App\Services\LegalCatalog;

use App\Models\SystemSetting;

class LegalPricingService
{
    public function priceFor(string $documentType, ?float $fallback = null): float
    {
        $key = 'legal_' . $documentType;
        $v = SystemSetting::get($key, null);
        if ($v !== null && $v !== '') {
            return (float) $v;
        }
        return (float) ($fallback ?? 0);
    }

    public function stampAssetPath(): ?string
    {
        $v = (string) SystemSetting::get('default_stamp_url', '');
        if ($v === '') return null;
        if (str_starts_with($v, 'http://') || str_starts_with($v, 'https://')) {
            return null;
        }
        if (str_starts_with($v, '/')) {
            return public_path(ltrim($v, '/'));
        }
        return public_path($v);
    }

    public function signatureText(): string
    {
        return (string) SystemSetting::get('default_signature_prefix', '');
    }
}

