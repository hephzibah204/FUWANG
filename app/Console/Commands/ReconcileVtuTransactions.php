<?php

namespace App\Console\Commands;

use App\Models\VtuTransaction;
use App\Services\VtuReconciliationService;
use Illuminate\Console\Command;

class ReconcileVtuTransactions extends Command
{
    protected $signature = 'vtu:reconcile {--limit=50}';
    protected $description = 'Query configured provider status endpoints without resending purchases';
    public function handle(VtuReconciliationService $service): int
    {
        $pending = VtuTransaction::where('status', 'pending')->where('created_at', '<', now()->subMinutes(2))
            ->oldest('updated_at')->limit(max(1, min(500, (int) $this->option('limit'))))->get();
        foreach ($pending as $tx) { $service->reconcile($tx)->touch(); }
        $this->info('Checked '.$pending->count().' pending transaction(s).');
        return self::SUCCESS;
    }
}
