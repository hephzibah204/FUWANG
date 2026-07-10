<?php

namespace Database\Factories;

use App\Models\DeliveryAgent;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class DeliveryAgentFactory extends Factory
{
    protected $model = DeliveryAgent::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'state' => 'Lagos',
            'city' => 'Ikeja',
            'availability_status' => 'offline',
            'rating' => 0.0,
            'approval_status' => 'pending',
        ];
    }

    public function approved(): self
    {
        return $this->state(fn () => ['approval_status' => 'approved']);
    }
}

