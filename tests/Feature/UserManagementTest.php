<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Employee;

class UserManagementTest extends TestCase
{
    use RefreshDatabase;

    private $employee;
    private $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();

        $this->employee = User::factory()->create();
        Employee::create(['user_id' => $this->employee->id]);

        $this->user = User::factory()->create();
    }

    public function test_can_search_users()
    {
        $response = $this->actingAs($this->employee)
            ->postJson('/api/admin/search_users', [
                'search' => $this->user->name
            ]);

        $response->assertSuccessful()
            ->assertJsonStructure([
                'success',
                'users' => [
                    '*' => ['id', 'name']
                ]
            ]);
    }

    public function test_can_get_user_data()
    {
        $response = $this->actingAs($this->employee)
            ->postJson('/api/admin/user_data', [
                'id' => $this->user->id
            ]);

        $response->assertSuccessful()
            ->assertJsonStructure([
                'success',
                'user' => [
                    'id',
                    'name',
                    'email',
                    'employee',
                    'note'
                ],
                'reservations'
            ]);
    }

    public function test_can_set_max_future_reservations()
    {
        $response = $this->actingAs($this->employee)
            ->postJson('/api/admin/set_max_future_reservations', [
                'id' => $this->user->id,
                'max_future_reservations' => 5
            ]);

        $response->assertSuccessful();
        $this->assertDatabaseHas('users', [
            'id' => $this->user->id,
            'max_future_reservations' => 5
        ]);
    }

    public function test_can_clear_max_future_reservations()
    {
        $this->user->update(['max_future_reservations' => 5]);

        $response = $this->actingAs($this->employee)
            ->postJson('/api/admin/set_max_future_reservations', [
                'id' => $this->user->id,
                'max_future_reservations' => null
            ]);

        $response->assertSuccessful();
        $this->assertDatabaseHas('users', [
            'id' => $this->user->id,
            'max_future_reservations' => null
        ]);
    }
}
