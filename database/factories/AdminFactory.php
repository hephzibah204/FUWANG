<?php

namespace Database\Factories;

use App\Models\Admin;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class AdminFactory extends Factory
{
    protected $model = Admin::class;

    public function definition(): array
    {
        return [
            'username' => Str::lower(Str::substr($this->faker->userName(), 0, 30)),
            'fullname' => $this->faker->name(),
            'email' => $this->faker->unique()->safeEmail(),
            'phone' => $this->faker->phoneNumber(),
            'avatar' => null,
            'password' => 'Password123!',
            'is_super_admin' => false,
        ];
    }
}
