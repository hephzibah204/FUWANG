<?php

namespace Database\Factories;

use App\Models\LogisticsRequest;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class LogisticsRequestFactory extends Factory
{
    protected $model = LogisticsRequest::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'sender_name' => $this->faker->name(),
            'sender_address' => $this->faker->address(),
            'recipient_name' => $this->faker->name(),
            'recipient_address' => $this->faker->address(),
            'weight' => $this->faker->randomFloat(2, 0.5, 10),
            'description' => $this->faker->sentence(),
            'delivery_type' => $this->faker->randomElement(['standard', 'express', 'overnight']),
            'amount' => $this->faker->randomFloat(2, 1000, 50000),
            'tracking_id' => 'FUP-' . Str::upper(Str::random(8)),
            'status' => $this->faker->randomElement(['processing', 'in_transit', 'out_for_delivery', 'delivered']),
            'last_status_updated_at' => now(),
        ];
    }
}

