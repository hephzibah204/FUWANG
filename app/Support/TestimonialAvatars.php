<?php

namespace App\Support;

final class TestimonialAvatars
{
    /**
     * Curated, verified photo mappings for specific named testimonials.
     * Ensures consistent gender and distinct high-resolution headshots.
     */
    private const EXPLICIT_MAP = [
        'chioma' => 'images/people/testimonials/testimonial-03.jpg', // Professional woman with glasses
        'yusuf'  => 'images/people/testimonials/testimonial-05.jpg', // Professional man in grey blazer with glasses
        'amaka'  => 'images/people/testimonials/testimonial-04.jpg', // Professional woman with braided locs
    ];

    public static function pool(): array
    {
        $dir = public_path('images/people/testimonials');
        if (! is_dir($dir)) {
            return [
                'images/people/testimonials/testimonial-03.jpg',
                'images/people/testimonials/testimonial-05.jpg',
                'images/people/testimonials/testimonial-04.jpg',
                'images/people/testimonials/testimonial-07.jpg',
                'images/people/testimonials/testimonial-02.jpg',
            ];
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

        if ($basenames === []) {
            return [
                'images/people/testimonials/testimonial-03.jpg',
                'images/people/testimonials/testimonial-05.jpg',
                'images/people/testimonials/testimonial-04.jpg',
            ];
        }

        return array_map(
            static fn (string $name) => 'images/people/testimonials/' . $name,
            $basenames
        );
    }

    public static function relativePath(string $seed): string
    {
        $lower = strtolower($seed);
        foreach (self::EXPLICIT_MAP as $keyword => $path) {
            if (str_contains($lower, $keyword)) {
                return $path;
            }
        }

        $pool = self::pool();
        if ($pool === []) {
            return 'images/people/testimonials/testimonial-05.jpg';
        }

        $index = (int) (abs(crc32($seed)) % count($pool));
        return $pool[$index] ?? $pool[0];
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
