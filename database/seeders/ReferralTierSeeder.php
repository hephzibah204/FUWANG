<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ReferralTier;

class ReferralTierSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        ReferralTier::create([
            'name' => 'Bronze',
            'description' => 'Bronze Tier',
            'commission_rate' => 5.00,
            'minimum_referrals' => 0,
        ]);

        ReferralTier::create([
            'name' => 'Silver',
            'description' => 'Silver Tier',
            'commission_rate' => 10.00,
            'minimum_referrals' => 10,
        ]);

        ReferralTier::create([
            'name' => 'Gold',
            'description' => 'Gold Tier',
            'commission_rate' => 15.00,
            'minimum_referrals' => 25,
        ]);
    }
}
