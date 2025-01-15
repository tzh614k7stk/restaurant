<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Employee;
use App\Models\Reservation;
use App\Models\ReservationNote;

class NotesTest extends TestCase
{
    use RefreshDatabase;

    private $employee;
    private $user;
    private $reservation;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();

        $this->employee = User::factory()->create();
        Employee::create(['user_id' => $this->employee->id]);

        $this->user = User::factory()->create();
        $this->reservation = Reservation::factory()->create([
            'user_id' => $this->user->id
        ]);
    }

    public function test_can_add_reservation_note()
    {
        $response = $this->actingAs($this->employee)
            ->postJson('/api/admin/reservation_note', [
                'id' => $this->reservation->id,
                'note' => 'Test note'
            ]);

        $response->assertSuccessful();
        $this->assertDatabaseHas('reservation_notes', [
            'reservation_id' => $this->reservation->id,
            'note' => 'Test note'
        ]);
    }

    public function test_can_update_reservation_note()
    {
        //first create note
        ReservationNote::create([
            'reservation_id' => $this->reservation->id,
            'note' => 'Original note'
        ]);

        $response = $this->actingAs($this->employee)
            ->postJson('/api/admin/reservation_note', [
                'id' => $this->reservation->id,
                'note' => 'Updated note'
            ]);

        $response->assertSuccessful();
        $this->assertDatabaseHas('reservation_notes', [
            'reservation_id' => $this->reservation->id,
            'note' => 'Updated note'
        ]);
    }

    public function test_can_delete_reservation_note()
    {
        //first create note
        ReservationNote::create([
            'reservation_id' => $this->reservation->id,
            'note' => 'Test note'
        ]);

        $response = $this->actingAs($this->employee)
            ->postJson('/api/admin/reservation_note', [
                'id' => $this->reservation->id,
                'note' => null
            ]);

        $response->assertSuccessful();
        $this->assertDatabaseMissing('reservation_notes', [
            'reservation_id' => $this->reservation->id
        ]);
    }

    public function test_can_add_user_note()
    {
        $response = $this->actingAs($this->employee)
            ->postJson('/api/admin/user_note', [
                'id' => $this->user->id,
                'note' => 'Test user note'
            ]);

        $response->assertSuccessful();
        $this->assertDatabaseHas('user_notes', [
            'user_id' => $this->user->id,
            'note' => 'Test user note'
        ]);
    }
}
