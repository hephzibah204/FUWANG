<?php

namespace App\Console\Commands;

use App\Models\AuctionLot;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class ProcessAuctions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'auction:process-status';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Process auction status transitions (Scheduled -> Live -> Ended)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $now = Carbon::now();

        // 1. Scheduled -> Live
        $toLive = AuctionLot::where('status', 'scheduled')
            ->where('start_at', '<=', $now)
            ->update(['status' => 'live']);

        if ($toLive > 0) {
            $this->info("Transitioned {$toLive} lots to LIVE.");
            Log::info("Auction: Transitioned {$toLive} lots to LIVE.");
        }

        // 2. Live -> Ended
        $toEnded = AuctionLot::where('status', 'live')
            ->where('end_at', '<=', $now)
            ->update(['status' => 'ended']);

        if ($toEnded > 0) {
            $this->info("Transitioned {$toEnded} lots to ENDED.");
            Log::info("Auction: Transitioned {$toEnded} lots to ENDED.");
            
            // Note: In a real app, you'd trigger notifications for winners here.
        }

        return Command::SUCCESS;
    }
}
