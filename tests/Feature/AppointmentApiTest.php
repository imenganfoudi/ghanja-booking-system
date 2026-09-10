<?php

namespace Tests\Feature;

use App\Models\Service;
use App\Models\Staff;
use App\Models\Appointment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AppointmentApiTest extends TestCase
{
    use RefreshDatabase;

    private function validPayload(Service $service, Staff $staff, string $time = '10:00'): array
    {
        return [
            'service_id' => $service->id,
            'staff_id' => $staff->id,
            'customer_name' => 'Test Client',
            'customer_email' => 'client@example.com',
            'customer_phone' => '12345678',
            'appointment_date' => now()->addDay()->format('Y-m-d'),
            'start_time' => $time,
        ];
    }

    public function test_can_book_an_available_slot(): void
    {
        $service = Service::factory()->create(['duration_minutes' => 30]);
        $staff = Staff::factory()->create();

        $response = $this->postJson('/api/appointments', $this->validPayload($service, $staff));

        $response->assertStatus(201);
        $this->assertDatabaseHas('appointments', [
            'service_id' => $service->id,
            'staff_id' => $staff->id,
            'status' => 'pending',
        ]);
    }

    public function test_cannot_double_book_same_slot(): void
    {
        $service = Service::factory()->create(['duration_minutes' => 30]);
        $staff = Staff::factory()->create();

        Appointment::factory()->create([
            'service_id' => $service->id,
            'staff_id' => $staff->id,
            'appointment_date' => now()->addDay()->format('Y-m-d'),
            'start_time' => '10:00:00',
            'end_time' => '10:30:00',
            'status' => 'pending',
        ]);

        $response = $this->postJson('/api/appointments', $this->validPayload($service, $staff, '10:00'));

        $response->assertStatus(409);
        $this->assertEquals(1, Appointment::where('staff_id', $staff->id)->count());
    }

    public function test_validation_fails_with_missing_required_fields(): void
    {
        $response = $this->postJson('/api/appointments', []);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors([
            'service_id',
            'customer_name',
            'customer_email',
            'customer_phone',
            'appointment_date',
            'start_time',
        ]);
    }

    public function test_available_slots_excludes_booked_time(): void
    {
        $service = Service::factory()->create(['duration_minutes' => 30]);
        $staff = Staff::factory()->create();
        $date = now()->addDay()->format('Y-m-d');

        Appointment::factory()->create([
            'service_id' => $service->id,
            'staff_id' => $staff->id,
            'appointment_date' => $date,
            'start_time' => '10:00:00',
            'end_time' => '10:30:00',
            'status' => 'confirmed',
        ]);

        $response = $this->getJson("/api/available-slots?service_id={$service->id}&date={$date}&staff_id={$staff->id}");

        $response->assertStatus(200);
        $response->assertJsonMissing(['slots' => ['10:00']]);
    }
}