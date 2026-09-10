<?php

namespace Database\Factories;

use App\Models\Service;
use App\Models\Staff;
use Illuminate\Database\Eloquent\Factories\Factory;

class AppointmentFactory extends Factory
{
    public function definition(): array
    {
        return [
            'service_id' => Service::factory(),
            'staff_id' => Staff::factory(),
            'customer_name' => $this->faker->name(),
            'customer_email' => $this->faker->safeEmail(),
            'customer_phone' => $this->faker->phoneNumber(),
            'appointment_date' => now()->addDay()->format('Y-m-d'),
            'start_time' => '10:00:00',
            'end_time' => '10:30:00',
            'status' => 'pending',
            'notes' => null,
        ];
    }
}