<?php

namespace Tests\Feature;

use App\Models\Staff;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StaffApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs(User::factory()->create(), 'sanctum');
    }

    public function test_can_list_staff(): void
    {
        Staff::factory()->count(2)->create();

        $response = $this->getJson('/api/staff');

        $response->assertStatus(200);
        $response->assertJsonCount(2);
    }

    public function test_can_create_a_staff_member(): void
    {
        $payload = [
            'name' => 'Sarra Ben Ali',
            'email' => 'sarra@example.com',
            'phone' => '20123456',
            'is_active' => true,
        ];

        $response = $this->postJson('/api/staff', $payload);

        $response->assertStatus(201);
        $this->assertDatabaseHas('staff', ['name' => 'Sarra Ben Ali']);
    }

    public function test_cannot_create_a_staff_member_without_name(): void
    {
        $response = $this->postJson('/api/staff', []);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['name']);
    }

    public function test_can_update_a_staff_member(): void
    {
        $staff = Staff::factory()->create(['name' => 'Old Name']);

        $response = $this->putJson("/api/staff/{$staff->id}", [
            'name' => 'Updated Name',
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('staff', ['id' => $staff->id, 'name' => 'Updated Name']);
    }

    public function test_can_delete_a_staff_member(): void
    {
        $staff = Staff::factory()->create();

        $response = $this->deleteJson("/api/staff/{$staff->id}");

        $response->assertStatus(204);
        $this->assertDatabaseMissing('staff', ['id' => $staff->id]);
    }
}