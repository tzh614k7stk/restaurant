<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Employee;
use App\Models\Table;
use App\Models\Reservation;

class AdminTableManagementTest extends TestCase
{
    use RefreshDatabase;

    private $employee;
    private $user;
    private $table;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();

        $this->employee = User::factory()->create();
        Employee::create(['user_id' => $this->employee->id]);

        $this->user = User::factory()->create();
        $this->table = Table::factory()->create([
            'name' => 'Test Table',
            'seats' => 4
        ]);
    }

    public function test_can_save_table()
    {
        $response = $this->actingAs($this->employee)
            ->postJson('/api/admin/save_table', [
                'name' => 'New Table',
                'seats' => 6
            ]);

        $response->assertSuccessful()
            ->assertJsonStructure([
                'success',
                'table' => [
                    'id',
                    'name',
                    'seats'
                ]
            ]);

        $this->assertDatabaseHas('tables', [
            'name' => 'New Table',
            'seats' => 6
        ]);
    }

    public function test_can_save_table_without_name()
    {
        $response = $this->actingAs($this->employee)
            ->postJson('/api/admin/save_table', [
                'seats' => 6
            ]);

        $response->assertSuccessful();
        $this->assertDatabaseHas('tables', [
            'name' => null,
            'seats' => 6
        ]);
    }

    public function test_cannot_save_table_with_invalid_seats()
    {
        $response = $this->actingAs($this->employee)
            ->postJson('/api/admin/save_table', [
                'name' => 'Invalid Table',
                'seats' => -1
            ]);

        $response->assertStatus(422);
    }

    public function test_can_edit_table()
    {
        $response = $this->actingAs($this->employee)
            ->postJson('/api/admin/edit_table', [
                'id' => $this->table->id,
                'name' => 'Updated Table',
                'seats' => 8
            ]);

        $response->assertSuccessful();
        $this->assertDatabaseHas('tables', [
            'id' => $this->table->id,
            'name' => 'Updated Table',
            'seats' => 8
        ]);
    }

    public function test_cannot_edit_nonexistent_table()
    {
        $response = $this->actingAs($this->employee)
            ->postJson('/api/admin/edit_table', [
                'id' => 999,
                'name' => 'Updated Table',
                'seats' => 8
            ]);

        $response->assertStatus(422);
    }

    public function test_can_delete_table()
    {
        $response = $this->actingAs($this->employee)
            ->postJson('/api/admin/delete_table', [
                'id' => $this->table->id
            ]);

        $response->assertSuccessful();
        $this->assertDatabaseMissing('tables', [
            'id' => $this->table->id
        ]);
    }

    public function test_cannot_delete_table_with_reservations()
    {
        //create a reservation for the table
        Reservation::create([
            'user_id' => $this->user->id,
            'table_id' => $this->table->id,
            'start_date' => date('Y-m-d'),
            'end_date' => date('Y-m-d'),
            'start_time' => '14:00',
            'end_time' => '16:00',
            'duration' => 2,
            'seats' => $this->table->seats
        ]);

        $response = $this->actingAs($this->employee)
            ->postJson('/api/admin/delete_table', [
                'id' => $this->table->id
            ]);

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
                'message' => 'Cannot delete table that has reservations.'
            ]);

        $this->assertDatabaseHas('tables', [
            'id' => $this->table->id
        ]);
    }

    public function test_non_employee_cannot_manage_tables()
    {
        $responses = [
            $this->actingAs($this->user)
                ->postJson('/api/admin/save_table', [
                    'name' => 'New Table',
                    'seats' => 6
                ]),
            
            $this->actingAs($this->user)
                ->postJson('/api/admin/edit_table', [
                    'id' => $this->table->id,
                    'name' => 'Updated Table',
                    'seats' => 8
                ]),
            
            $this->actingAs($this->user)
                ->postJson('/api/admin/delete_table', [
                    'id' => $this->table->id
                ])
        ];

        foreach ($responses as $response) {
            $response->assertStatus(401);
        }
    }
}