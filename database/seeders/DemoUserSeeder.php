<?php

namespace Database\Seeders;

use App\Models\Admin;
use App\Models\AccountBalance;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

class DemoUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $email = 'admin@fuwa.ng';
        $password = 'Fuwa.com123';
        $fullname = 'Demo Admin';
        $username = 'admin_demo';

        // 1. Create in Users table (Customers)
        $user = User::updateOrCreate(
            ['email' => $email],
            [
                'fullname' => $fullname,
                'username' => $username,
                'password' => Hash::make($password),
                'email_verified_at' => now(),
            ]
        );
        
        if (Schema::hasColumn('users', 'role')) {
            $user->role = 'admin';
            $user->save();
        }

        // Add balance for the user
        if (Schema::hasTable('account_balances')) {
            AccountBalance::updateOrCreate(
                ['email' => $user->email],
                [
                    'user_id' => $user->id,
                    'user_balance' => 100000,
                    'api_key' => 'demo_key_' . bin2hex(random_bytes(8)),
                ]
            );
        }

        // 2. Create in Admins table (Staff)
        Admin::updateOrCreate(
            ['email' => $email],
            [
                'fullname' => $fullname,
                'username' => $username,
                'password' => Hash::make($password),
                'is_super_admin' => true,
            ]
        );
    }
}
