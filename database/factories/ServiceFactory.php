<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class ServiceFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => $this->faker->words(2, true),
            'description' => $this->faker->sentence(),
            'duration_minutes' => $this->faker->randomElement([30, 45, 60]),
            'price' => $this->faker->randomFloat(2, 20, 150),
            'is_active' => true,
        ];
    }
}