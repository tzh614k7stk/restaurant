<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Employee;
use App\Models\Table;
use App\Models\Reservation;

class AdminReservationsTest extends TestCase
{
    use RefreshDatabase;

    private $employee;
    private $user;
    private $table;
    private $reservation;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();

        $this->employee = User::factory()->create();
        Employee::create(['user_id' => $this->employee->id]);

        $this->user = User::factory()->create();
        $this->table = Table::factory()->create();
        
        $this->reservation = Reservation::create([
            'user_id' => $this->user->id,
            'table_id' => $this->table->id,
            'start_date' => date('Y-m-d'),
            'end_date' => date('Y-m-d'),
            'start_time' => '14:00',
            'end_time' => '16:00',
            'duration' => 2,
            'seats' => $this->table->seats
        ]);
    }

    public function test_can_get_reservations_by_date()
    {
        $response = $this->actingAs($this->employee)
            ->postJson('/api/admin/reservations', [
                'date' => date('Y-m-d')
            ]);

        $response->assertSuccessful()
            ->assertJsonStructure([
                'success',
                'reservations' => [
                    '*' => [
                        'id',
                        'table_id',
                        'user_id',
                        'start_date',
                        'end_date',
                        'start_time',
                        'end_time',
                        'duration',
                        'seats',
                        'note',
                        'table',
                        'user'
                    ]
                ],
                'tables'
            ]);
    }

    public function test_non_employee_cannot_access_reservations()
    {
        $response = $this->actingAs($this->user)
            ->postJson('/api/admin/reservations', [
                'date' => date('Y-m-d')
            ]);

        $response->assertStatus(401);
    }

    public function test_invalid_date_format_rejected()
    {
        $response = $this->actingAs($this->employee)
            ->postJson('/api/admin/reservations', [
                'date' => 'invalid-date'
            ]);

        $response->assertStatus(422);
    }
}
