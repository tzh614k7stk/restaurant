<?php

namespace Tests\Unit;

use Tests\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Reservation;
use App\Models\Table;
use App\Models\User;
use DateTime;

//README
//README
//README
//README
//README
//duration of generated test might not be in durations config table
//once the api checks for min date + time (right now it only checks for min date) must account for that

class ReservationOverlapTest extends TestCase
{
    use RefreshDatabase;

    private $table_id;
    private $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
        
        //create test table and user
        $this->table = Table::factory()->create();
        $this->user = User::factory()->create();
    }

    /**
     * Test cases for reservation overlap scenarios
     */
    #[DataProvider('reservationOverlapProvider')]
    public function test_reservation_overlap_detection($existing_start, $existing_end, $new_start, $new_end, $should_overlap)
    {
        //create existing reservation
        Reservation::create([
            'table_id' => $this->table->id,
            'user_id' => $this->user->id,
            'start_date' => date('Y-m-d', strtotime($existing_start)),
            'end_date' => date('Y-m-d', strtotime($existing_end)),
            'start_time' => date('H:i', strtotime($existing_start)),
            'end_time' => date('H:i', strtotime($existing_end)),
            'duration' => (strtotime($existing_end) - strtotime($existing_start)) / 3600,
            'seats' => 1
        ]);

        //attempt to create new reservation
        $response = $this->actingAs($this->user)->postJson('/api/create_reservation', [
            'date' => date('Y-m-d', strtotime($new_start)),
            'time' => date('H:i', strtotime($new_start)),
            'duration' => (strtotime($new_end) - strtotime($new_start)) / 3600,
            'table_id' => $this->table->id
        ]);

        if ($should_overlap)
        {
            $response->assertStatus(422)->assertJson(['success' => false, 'message' => 'Table is not available at requested time']);
        }
        else
        {
            $response->assertSuccessful();
        }
    }

    public static function reservationOverlapProvider(): array
    {
        $current_date = date('Y-m-d');
        return [
            'Existing ends during new' => [
                $current_date.' 12:00',  //existing_start
                $current_date.' 14:00',  //existing_end
                $current_date.' 13:00',  //new_start
                $current_date.' 15:00',  //new_end
                true                     //should_overlap
            ],
            'Existing starts during new' => [
                $current_date.' 14:00',
                $current_date.' 16:00',
                $current_date.' 13:00',
                $current_date.' 15:00',
                true
            ],
            'Existing encompasses new' => [
                $current_date.' 12:00',
                $current_date.' 16:00',
                $current_date.' 13:00',
                $current_date.' 15:00',
                true
            ],
            'No overlap - before' => [
                $current_date.' 10:00',
                $current_date.' 12:00',
                $current_date.' 13:00',
                $current_date.' 15:00',
                false
            ],
            'No overlap - after' => [
                $current_date.' 16:00',
                $current_date.' 18:00',
                $current_date.' 13:00',
                $current_date.' 15:00',
                false
            ],
            'Edge case - exactly adjacent' => [
                $current_date.' 12:00',
                $current_date.' 13:00',
                $current_date.' 13:00',
                $current_date.' 15:00',
                false
            ]
        ];
    }
}