<?php

namespace App\Console\Commands;

use App\Models\Page;
use App\Services\HtmlSanitizer;
use Illuminate\Console\Command;

class SanitizePageContent extends Command
{
    protected $signature = 'pages:sanitize {--dry-run : Show how many pages would change without saving}';
    protected $description = 'Sanitize stored CMS page HTML content to reduce XSS risk.';

    public function handle(HtmlSanitizer $sanitizer): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $changed = 0;
        $total = 0;

        Page::query()->orderBy('id')->chunkById(100, function ($pages) use ($sanitizer, $dryRun, &$changed, &$total) {
            foreach ($pages as $page) {
                $total++;
                $original = (string) ($page->content ?? '');
                $sanitized = $sanitizer->sanitize($original);
                if ($sanitized !== $original) {
                    $changed++;
                    if (!$dryRun) {
                        $page->content = $sanitized;
                        $page->save();
                    }
                }
            }
        });

        $this->info('Pages scanned: ' . $total);
        $this->info('Pages changed: ' . $changed);
        if ($dryRun) {
            $this->warn('Dry-run mode: no pages were saved.');
        }

        return self::SUCCESS;
    }
}

