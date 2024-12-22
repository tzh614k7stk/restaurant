<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Table;
use App\Models\Reservation;
use App\Models\RestaurantConfig;
use DateTime;
use DateTimeZone;

class ReservationManagementTest extends TestCase
{
    use RefreshDatabase;

    private $user;
    private $table;
    private $timezone;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();

        $this->user = User::factory()->create();
        $this->table = Table::factory()->create();
        $this->timezone = 'UTC';

        //set up basic restaurant config
        RestaurantConfig::updateOrCreate(
            ['name' => 'timezone'],
            ['value' => $this->timezone]
        );
        RestaurantConfig::updateOrCreate(
            ['name' => 'durations'],
            ['value' => json_encode([1, 1.5, 2])]
        );
        RestaurantConfig::updateOrCreate(
            ['name' => 'max_future_reservations'],
            ['value' => '3']
        );
        RestaurantConfig::updateOrCreate(
            ['name' => 'min_hours_before_reservation'],
            ['value' => '2']
        );
        RestaurantConfig::updateOrCreate(
            ['name' => 'max_days_in_advance'],
            ['value' => '30']
        );
    }

    public function test_guest_cannot_create_reservation()
    {
        $response = $this->postJson('/api/create_reservation', [
            'date' => date('Y-m-d', strtotime('+1 day')),
            'time' => '14:00',
            'duration' => 2,
            'table_id' => $this->table->id
        ]);

        $response->assertStatus(401);
    }

    public function test_cannot_create_reservation_in_past()
    {
        $response = $this->actingAs($this->user)
            ->postJson('/api/create_reservation', [
                'date' => date('Y-m-d', strtotime('-1 day')),
                'time' => '14:00',
                'duration' => 2,
                'table_id' => $this->table->id
            ]);

        $response->assertStatus(422);
    }

    public function test_cannot_create_reservation_too_soon()
    {
        $response = $this->actingAs($this->user)
            ->postJson('/api/create_reservation', [
                'date' => date('Y-m-d'),
                'time' => date('H:i', strtotime('+1 hour')),
                'duration' => 2,
                'table_id' => $this->table->id
            ]);

        $response->assertStatus(422)
            ->assertJsonFragment(['message' => 'Reservation must be at least 2 hours in the future.']);
    }

    public function test_cannot_exceed_max_future_reservations()
    {
        //create max allowed reservations
        for ($i = 0; $i < 3; $i++) {
            Reservation::create([
                'user_id' => $this->user->id,
                'table_id' => $this->table->id,
                'start_date' => date('Y-m-d', strtotime('+' . ($i + 1) . ' day')),
                'end_date' => date('Y-m-d', strtotime('+' . ($i + 1) . ' day')),
                'start_time' => '14:00',
                'end_time' => '16:00',
                'duration' => 2,
                'seats' => $this->table->seats
            ]);
        }

        $response = $this->actingAs($this->user)
            ->postJson('/api/create_reservation', [
                'date' => date('Y-m-d', strtotime('+5 days')),
                'time' => '14:00',
                'duration' => 2,
                'table_id' => $this->table->id
            ]);

        $response->assertStatus(422)
            ->assertJsonFragment(['message' => 'Maximum amount of reservations reached.']);
    }

    public function test_can_delete_own_reservation()
    {
        $reservation = Reservation::create([
            'user_id' => $this->user->id,
            'table_id' => $this->table->id,
            'start_date' => date('Y-m-d', strtotime('+1 day')),
            'end_date' => date('Y-m-d', strtotime('+1 day')),
            'start_time' => '14:00',
            'end_time' => '16:00',
            'duration' => 2,
            'seats' => $this->table->seats
        ]);

        $response = $this->actingAs($this->user)
            ->postJson('/api/delete_reservation', [
                'id' => $reservation->id
            ]);

        $response->assertSuccessful();
        $this->assertDatabaseMissing('reservations', ['id' => $reservation->id]);
    }

    public function test_cannot_delete_others_reservation()
    {
        $otherUser = User::factory()->create();
        $reservation = Reservation::create([
            'user_id' => $otherUser->id,
            'table_id' => $this->table->id,
            'start_date' => date('Y-m-d', strtotime('+1 day')),
            'end_date' => date('Y-m-d', strtotime('+1 day')),
            'start_time' => '14:00',
            'end_time' => '16:00',
            'duration' => 2,
            'seats' => $this->table->seats
        ]);

        $response = $this->actingAs($this->user)
            ->postJson('/api/delete_reservation', [
                'id' => $reservation->id
            ]);

        $response->assertStatus(401);
        $this->assertDatabaseHas('reservations', ['id' => $reservation->id]);
    }
}
