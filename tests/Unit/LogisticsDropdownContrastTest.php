<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class LogisticsDropdownContrastTest extends TestCase
{
    public function test_logistics_dropdown_palette_meets_wcag_thresholds(): void
    {
        $closedControlRatio = $this->contrastRatio('#f8fafc', '#111827');
        $optionListRatio = $this->contrastRatio('#111827', '#ffffff');
        $disabledRatio = $this->contrastRatio('#94a3b8', '#0b1220');
        $selectedOptionRatio = $this->contrastRatio('#111827', '#fde68a');
        $nexusSelectedOptionRatio = $this->contrastRatio('#111827', '#bfdbfe');

        $this->assertGreaterThanOrEqual(4.5, $closedControlRatio);
        $this->assertGreaterThanOrEqual(4.5, $optionListRatio);
        $this->assertGreaterThanOrEqual(4.5, $disabledRatio);
        $this->assertGreaterThanOrEqual(4.5, $selectedOptionRatio);
        $this->assertGreaterThanOrEqual(4.5, $nexusSelectedOptionRatio);
    }

    public function test_shared_layouts_keep_accessible_select_rules(): void
    {
        $projectRoot = dirname(__DIR__, 2);
        $postoffice = file_get_contents($projectRoot . '/resources/views/layouts/postoffice.blade.php');
        $nexus = file_get_contents($projectRoot . '/resources/views/layouts/nexus.blade.php');

        $this->assertIsString($postoffice);
        $this->assertIsString($nexus);

        $this->assertStringContainsString('--po-select-surface: #111827;', $postoffice);
        $this->assertStringContainsString('select.form-control option:checked', $postoffice);
        $this->assertStringContainsString('select.tracking-input option:checked', $postoffice);

        $this->assertStringContainsString('--select-surface: #111827;', $nexus);
        $this->assertStringContainsString('select.form-control option:checked', $nexus);
        $this->assertStringContainsString('select.form-control:disabled', $nexus);
    }

    private function contrastRatio(string $foreground, string $background): float
    {
        $fg = $this->relativeLuminance($foreground);
        $bg = $this->relativeLuminance($background);

        $lighter = max($fg, $bg);
        $darker = min($fg, $bg);

        return ($lighter + 0.05) / ($darker + 0.05);
    }

    private function relativeLuminance(string $hex): float
    {
        [$red, $green, $blue] = $this->hexToRgb($hex);

        $channels = array_map(function (int $channel): float {
            $value = $channel / 255;

            return $value <= 0.03928
                ? $value / 12.92
                : (($value + 0.055) / 1.055) ** 2.4;
        }, [$red, $green, $blue]);

        return 0.2126 * $channels[0] + 0.7152 * $channels[1] + 0.0722 * $channels[2];
    }

    /**
     * @return array{int, int, int}
     */
    private function hexToRgb(string $hex): array
    {
        $normalized = ltrim($hex, '#');

        return [
            hexdec(substr($normalized, 0, 2)),
            hexdec(substr($normalized, 2, 2)),
            hexdec(substr($normalized, 4, 2)),
        ];
    }
}
