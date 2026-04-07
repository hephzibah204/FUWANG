<?php
namespace App\Services;

class PhotoComplianceFilter
{
    /**
     * Remove photo data from response before any persistence
     * Ensures Base64 photo is rendered to screen ONLY
     */
    public static function sanitize(array $data): array
    {
        $photoFields = ['photo', 'image', 'selfie', 'selfie_image', 'photo_base64'];
        
        foreach ($photoFields as $field) {
            unset($data[$field]);
        }
        
        return $data;
    }
}
