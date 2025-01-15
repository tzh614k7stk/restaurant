<?php

namespace Tests\Unit;

use Tests\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Reservation;
use App\Models\Table;
use App\Models\User;
use App\Models\OpeningHours;

class ReservationOverlapTest extends TestCase
{
    use RefreshDatabase;

    private $table;
    private $user;
    private $openingHours;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
        
        //create test table and user
        $this->table = Table::factory()->create();
        $this->user = User::factory()->create();

        //set up opening hours 1000 - 2200 for testing
        $this->setupOpeningHours();
    }

    private function setupOpeningHours()
    {
        //drop existing opening hours
        OpeningHours::query()->delete();

        //create opening hours for all days of the week
        $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
        foreach ($days as $day) {
            OpeningHours::create([
                'day' => $day,
                'open' => '10:00',
                'close' => '22:00',
                'closed' => false
            ]);
        }
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
            'duration' => 2, //using standard 2 hour duration
            'seats' => 1
        ]);

        //attempt to create new reservation
        $response = $this->actingAs($this->user)->postJson('/api/create_reservation', [
            'date' => date('Y-m-d', strtotime($new_start)),
            'time' => date('H:i', strtotime($new_start)),
            'duration' => 2, //using standard 2 hour duration
            'table_id' => $this->table->id
        ]);

        if ($should_overlap)
        {
            $response->assertStatus(422)->assertJson(['success' => false, 'message' => 'Table is not available at requested time.']);
        }
        else
        {
            $response->assertSuccessful();
        }
    }

    public static function reservationOverlapProvider(): array
    {
        //use a future date that's guaranteed to be valid
        $test_date = date('Y-m-d', strtotime('+1 week'));
        
        return [
            'Existing ends during new' => [
                $test_date.' 12:00',
                $test_date.' 14:00',
                $test_date.' 13:00',
                $test_date.' 15:00',
                true
            ],
            'Existing starts during new' => [
                $test_date.' 14:00',
                $test_date.' 16:00',
                $test_date.' 13:00',
                $test_date.' 15:00',
                true
            ],
            'Existing encompasses new' => [
                $test_date.' 12:00',
                $test_date.' 16:00',
                $test_date.' 13:00',
                $test_date.' 15:00',
                true
            ],
            'No overlap - before' => [
                $test_date.' 10:00',
                $test_date.' 12:00',
                $test_date.' 13:00',
                $test_date.' 15:00',
                false
            ],
            'No overlap - after' => [
                $test_date.' 16:00',
                $test_date.' 18:00',
                $test_date.' 13:00',
                $test_date.' 15:00',
                false
            ],
            'Edge case - exactly adjacent' => [
                $test_date.' 12:00',
                $test_date.' 14:00',
                $test_date.' 14:00',
                $test_date.' 16:00',
                false
            ]
        ];
    }
}