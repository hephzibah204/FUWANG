<?php

namespace App\Support;

final class TestimonialAvatars
{
    public static function pool(): array
    {
        $dir = public_path('images/people/testimonials');
        if (! is_dir($dir)) {
            return [];
        }

        $files = array_merge(
            glob($dir . '/*.jpg') ?: [],
            glob($dir . '/*.jpeg') ?: [],
            glob($dir . '/*.png') ?: [],
            glob($dir . '/*.webp') ?: [],
        );

        $basenames = [];
        foreach ($files as $file) {
            if (is_string($file) && is_file($file)) {
                $basenames[] = basename($file);
            }
        }

        $basenames = array_values(array_unique($basenames));
        sort($basenames, SORT_STRING);

        return array_map(
            static fn (string $name) => 'images/people/testimonials/' . $name,
            $basenames
        );
    }

    public static function relativePath(string $seed): string
    {
        $pool = self::pool();
        if ($pool === []) {
            return 'images/people/customer-1.jpg';
        }

        $index = (int) (abs(crc32($seed)) % count($pool));
        $path = $pool[$index] ?? $pool[0];

        if (is_file(public_path($path))) {
            return $path;
        }

        return 'images/people/customer-1.jpg';
    }

    public static function webPath(string $seed): string
    {
        return '/' . ltrim(self::relativePath($seed), '/');
    }

    public static function url(string $seed): string
    {
        return asset(self::relativePath($seed));
    }
}
