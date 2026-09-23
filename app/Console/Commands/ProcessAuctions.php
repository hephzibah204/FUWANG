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
        $endedLots = AuctionLot::where('status', 'live')
            ->where('end_at', '<=', $now)
            ->get();

        $toEnded = $endedLots->count();
        if ($toEnded > 0) {
            foreach ($endedLots as $lot) {
                $lot->status = 'ended';
                $lot->save();

                $winningBid = \App\Models\AuctionBid::where('lot_id', $lot->lot_code)
                    ->where('status', 'winning')
                    ->first();

                if ($winningBid) {
                    Log::info("Auction Lot [{$lot->lot_code}] ended. Winning bid: {$winningBid->reference} by User #{$winningBid->user_id} for ₦{$winningBid->bid_amount}");
                }
            }

            $this->info("Transitioned {$toEnded} lots to ENDED.");
            Log::info("Auction: Transitioned {$toEnded} lots to ENDED.");
        }

        return Command::SUCCESS;
    }
}
