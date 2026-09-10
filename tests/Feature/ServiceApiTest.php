<?php

namespace Tests\Feature;

use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ServiceApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs(User::factory()->create(), 'sanctum');
    }

    public function test_can_list_services(): void
    {
        Service::factory()->count(3)->create();

        $response = $this->getJson('/api/services');

        $response->assertStatus(200);
        $response->assertJsonCount(3);
    }

    public function test_can_create_a_service(): void
    {
        $payload = [
            'name' => 'Coupe de cheveux',
            'description' => 'Coupe classique',
            'duration_minutes' => 30,
            'price' => 25.50,
            'is_active' => true,
        ];

        $response = $this->postJson('/api/services', $payload);

        $response->assertStatus(201);
        $this->assertDatabaseHas('services', ['name' => 'Coupe de cheveux']);
    }

    public function test_cannot_create_a_service_without_required_fields(): void
    {
        $response = $this->postJson('/api/services', []);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['name', 'duration_minutes', 'price']);
    }

    public function test_can_update_a_service(): void
    {
        $service = Service::factory()->create(['name' => 'Old Name']);

        $response = $this->putJson("/api/services/{$service->id}", [
            'name' => 'New Name',
            'duration_minutes' => 45,
            'price' => 30,
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('services', ['id' => $service->id, 'name' => 'New Name']);
    }

    public function test_can_delete_a_service(): void
    {
        $service = Service::factory()->create();

        $response = $this->deleteJson("/api/services/{$service->id}");

        $response->assertStatus(204);
        $this->assertDatabaseMissing('services', ['id' => $service->id]);
    }
}