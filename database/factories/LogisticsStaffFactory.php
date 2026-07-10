<?php

namespace Database\Factories;

use App\Models\LogisticsStaff;
use Illuminate\Database\Eloquent\Factories\Factory;

class LogisticsStaffFactory extends Factory
{
    protected $model = LogisticsStaff::class;

    public function definition(): array
    {
        return [
            'fullname' => $this->faker->name(),
            'email' => $this->faker->unique()->safeEmail(),
            'password' => 'Password123!',
            'is_active' => true,
        ];
    }
}

