<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class ImageOptimizer
{
    public static function storeOptimizedPng(UploadedFile $file, string $disk, string $dir, int $targetW, int $targetH): ?string
    {
        $bin = @file_get_contents($file->getRealPath());
        if (!$bin) {
            return null;
        }

        if (!function_exists('imagecreatefromstring')) {
            return null;
        }

        $src = @imagecreatefromstring($bin);
        if (!$src) {
            return null;
        }

        $srcW = imagesx($src);
        $srcH = imagesy($src);
        if ($srcW <= 0 || $srcH <= 0) {
            imagedestroy($src);
            return null;
        }

        $dst = imagecreatetruecolor($targetW, $targetH);
        imagealphablending($dst, false);
        imagesavealpha($dst, true);
        $transparent = imagecolorallocatealpha($dst, 0, 0, 0, 127);
        imagefilledrectangle($dst, 0, 0, $targetW, $targetH, $transparent);

        $scale = min($targetW / $srcW, $targetH / $srcH);
        $newW = (int) max(1, floor($srcW * $scale));
        $newH = (int) max(1, floor($srcH * $scale));
        $dstX = (int) floor(($targetW - $newW) / 2);
        $dstY = (int) floor(($targetH - $newH) / 2);

        imagecopyresampled($dst, $src, $dstX, $dstY, 0, 0, $newW, $newH, $srcW, $srcH);

        ob_start();
        imagepng($dst, null, 8);
        $png = ob_get_clean();

        imagedestroy($src);
        imagedestroy($dst);

        if (!$png) {
            return null;
        }

        $name = trim($dir, '/') . '/' . uniqid('img_', true) . '.png';
        Storage::disk($disk)->put($name, $png, ['visibility' => 'public']);
        return Storage::disk($disk)->url($name);
    }
}

