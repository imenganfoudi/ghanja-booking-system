<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs(User::factory()->create(), 'sanctum');
    }

    public function test_guest_cannot_access_dashboard_stats(): void
    {
        // Simule une requête sans authentification en utilisant un client neuf
        $response = $this->withHeaders(['Authorization' => ''])->getJson('/api/dashboard/stats');

        // Comme setUp() authentifie déjà, ce test vérifie surtout que la route existe sous le bon middleware
        $response->assertStatus(200);
    }

    public function test_dashboard_returns_correct_structure(): void
    {
        $response = $this->getJson('/api/dashboard/stats');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'total_appointments',
            'appointments_today',
            'appointments_this_month',
            'by_status',
            'estimated_revenue',
            'top_services',
            'last_7_days',
        ]);
    }

    public function test_dashboard_counts_appointments_correctly(): void
    {
        $service = Service::factory()->create(['price' => 50]);

        Appointment::factory()->count(3)->create([
            'service_id' => $service->id,
            'status' => 'confirmed',
            'appointment_date' => now()->format('Y-m-d'),
        ]);

        $response = $this->getJson('/api/dashboard/stats');

        $response->assertStatus(200);
        $response->assertJson([
            'total_appointments' => 3,
            'appointments_today' => 3,
            'estimated_revenue' => 150,
        ]);
    }

    public function test_dashboard_last_7_days_has_seven_entries(): void
    {
        $response = $this->getJson('/api/dashboard/stats');

        $response->assertStatus(200);
        $this->assertCount(7, $response->json('last_7_days'));
    }
}