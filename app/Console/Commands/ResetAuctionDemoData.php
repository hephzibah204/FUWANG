<?php

namespace App\Console\Commands;

use Database\Seeders\AuctionDemoSeeder;
use Illuminate\Console\Command;

class ResetAuctionDemoData extends Command
{
    protected $signature = 'auction:demo-reset';

    protected $description = 'Reset and seed demo data for the auction management system.';

    public function handle(): int
    {
        $this->call('db:seed', [
            '--class' => AuctionDemoSeeder::class,
            '--force' => true,
        ]);

        $this->info('Auction demo dataset seeded.');

        return Command::SUCCESS;
    }
}

