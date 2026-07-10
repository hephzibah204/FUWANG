<?php

namespace App\Support;

use chillerlan\QRCode\Output\QRGdImage;
use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;

class QrCodeDataUri
{
    public static function make(?string $data, int $size = 92): ?string
    {
        $data = trim((string) $data);

        if ($data === '') {
            return null;
        }

        try {
            $scale = max(2, (int) ceil($size / 23));

            return (new QRCode(new QROptions([
                'outputInterface' => QRGdImage::class,
                'outputBase64' => true,
                'scale' => $scale,
                'addQuietzone' => false,
            ])))->render($data);
        } catch (\Throwable $e) {
            return null;
        }
    }
}
