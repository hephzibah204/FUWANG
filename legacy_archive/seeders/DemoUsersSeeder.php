<?php

namespace Database\Seeders;

use App\Models\AccountBalance;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DemoUsersSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::updateOrCreate(
            ['email' => 'admin@nexuspay.test'],
            [
                'fullname' => 'Demo Admin',
                'username' => 'demo_admin',
                'password' => Hash::make('Admin@12345'),
            ]
        );
        $admin->role = 'admin';
        $admin->save();

        AccountBalance::updateOrCreate(
            ['email' => $admin->email],
            [
                'user_balance' => 25000,
                'api_key' => 'user',
            ]
        );

        $user = User::updateOrCreate(
            ['email' => 'user@nexuspay.test'],
            [
                'fullname' => 'Demo User',
                'username' => 'demo_user',
                'password' => Hash::make('User@12345'),
            ]
        );
        $user->role = 'user';
        $user->save();

        AccountBalance::updateOrCreate(
            ['email' => $user->email],
            [
                'user_balance' => 10000,
                'api_key' => 'user',
            ]
        );
    }
}

