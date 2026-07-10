<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class UserFactory extends Factory
{
    protected $model = User::class;

    public function definition(): array
    {
        $username = Str::lower(Str::substr(preg_replace('/[^a-zA-Z0-9_]/', '', $this->faker->userName()), 0, 20));
        if ($username === '') {
            $username = 'user' . $this->faker->randomNumber(6, true);
        }

        return [
            'fullname' => $this->faker->name(),
            'username' => $username,
            'email' => $this->faker->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => 'Password123!',
            'transaction_pin' => '1234',
            'reseller_id' => 'default',
            'referral_id' => Str::upper(Str::random(10)),
            'user_status' => 'active',
            'online_status' => 'offline',
        ];
    }
}
