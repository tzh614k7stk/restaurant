<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\OpeningHours;
use App\Models\RestaurantConfig;
class PublicApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();

        //set up basic config
        RestaurantConfig::create([
            'name' => 'phone',
            'value' => '123-456-7890'
        ]);
        RestaurantConfig::create([
            'name' => 'email',
            'value' => 'test@example.com'
        ]);

        //set up basic opening hours
        OpeningHours::query()->delete();
        OpeningHours::create([
            'day' => 'Monday',
            'open' => '09:00',
            'close' => '17:00',
            'closed' => false
        ]);
    }

    public function test_can_get_info_data()
    {
        $response = $this->postJson('/api/info_data');

        $response->assertSuccessful()
            ->assertJsonStructure([
                'success',
                'opening_hours',
                'custom_opening_hours',
                'closing_dates',
                'phone',
                'email'
            ]);
    }

    public function test_can_get_restaurant_data()
    {
        $response = $this->postJson('/api/restaurant_data');

        $response->assertSuccessful()
            ->assertJsonStructure([
                'success',
                'tables',
                'reservations',
                'user_reservations',
                'opening_hours',
                'custom_opening_hours',
                'closing_dates',
                'durations',
                'max_days_in_advance',
                'max_future_reservations',
                'min_hours_before_reservation',
                'timezone'
            ]);
    }
}
