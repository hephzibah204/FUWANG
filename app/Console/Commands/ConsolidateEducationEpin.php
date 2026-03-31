<?php

namespace App\Console\Commands;

use App\Services\EducationEpinConsolidationService;
use Illuminate\Console\Command;

class ConsolidateEducationEpin extends Command
{
    protected $signature = 'epin:consolidate-education {--dry-run : Show status only; do not write flags}';

    protected $description = 'Consolidate Education ePIN module with VTU ePIN infrastructure (idempotent).';

    public function handle(): int
    {
        $svc = app(EducationEpinConsolidationService::class);
        $status = $svc->status();

        $this->info('Education/ePIN consolidation status');
        $this->line('flag_key: ' . $status['flag_key']);
        $this->line('has_system_settings: ' . ($status['has_system_settings'] ? 'yes' : 'no'));
        $this->line('flag_value: ' . ($status['flag_value'] !== '' ? $status['flag_value'] : '(empty)'));
        $this->line('has_epin_products_config: ' . ($status['has_epin_products_config'] ? 'yes' : 'no'));
        $this->line('custom_api.vtu_epin: ' . (string) ($status['custom_api_counts']['vtu_epin'] ?? 0));
        $this->line('custom_api.education_*: ' . (string) ($status['custom_api_counts']['education'] ?? 0));
        $this->line('is_consolidated: ' . ($status['is_consolidated'] ? 'yes' : 'no'));

        if ($this->option('dry-run')) {
            return 0;
        }

        if ($status['is_consolidated']) {
            $this->info('No action needed.');
            return 0;
        }

        $newStatus = $svc->ensureConsolidated();
        $this->info('Consolidation applied.');
        $this->line('flag_value: ' . ($newStatus['flag_value'] !== '' ? $newStatus['flag_value'] : '(empty)'));
        $this->line('is_consolidated: ' . ($newStatus['is_consolidated'] ? 'yes' : 'no'));

        return 0;
    }
}

