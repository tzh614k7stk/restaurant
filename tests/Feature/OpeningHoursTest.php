<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Employee;
use App\Models\OpeningHours;

class OpeningHoursTest extends TestCase
{
    use RefreshDatabase;

    private $employee;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();

        $this->employee = User::factory()->create();
        Employee::create(['user_id' => $this->employee->id]);
    }

    public function test_can_save_regular_opening_hours()
    {
        $response = $this->actingAs($this->employee)
            ->postJson('/api/admin/save_opening_hours', [
                'day' => 'Monday',
                'open' => '09:00',
                'close' => '17:00',
                'closed' => false
            ]);

        $response->assertSuccessful();
        $this->assertDatabaseHas('opening_hours', [
            'day' => 'Monday',
            'open' => '09:00',
            'close' => '17:00',
            'closed' => false
        ]);
    }

    public function test_can_save_closed_day()
    {
        $response = $this->actingAs($this->employee)
            ->postJson('/api/admin/save_opening_hours', [
                'day' => 'Sunday',
                'closed' => true
            ]);

        $response->assertSuccessful();
        $this->assertDatabaseHas('opening_hours', [
            'day' => 'Sunday',
            'closed' => true
        ]);
    }

    public function test_can_save_special_hours()
    {
        $response = $this->actingAs($this->employee)
            ->postJson('/api/admin/save_special_hours', [
                'date' => '2024-12-24',
                'open' => '09:00',
                'close' => '15:00',
                'closed' => false
            ]);

        $response->assertSuccessful();
        $this->assertDatabaseHas('opening_hours', [
            'day' => '2024-12-24',
            'open' => '09:00',
            'close' => '15:00'
        ]);
    }

    public function test_can_delete_special_hours()
    {
        //first create special hours
        OpeningHours::create([
            'day' => '2024-12-24',
            'open' => '09:00',
            'close' => '15:00',
            'closed' => false
        ]);

        $response = $this->actingAs($this->employee)
            ->postJson('/api/admin/delete_special_hours', [
                'date' => '2024-12-24'
            ]);

        $response->assertSuccessful();
        $this->assertDatabaseMissing('opening_hours', [
            'day' => '2024-12-24'
        ]);
    }

    public function test_detects_overnight_hours()
    {
        $response = $this->actingAs($this->employee)
            ->postJson('/api/admin/save_opening_hours', [
                'day' => 'Friday',
                'open' => '18:00',
                'close' => '02:00',
                'closed' => false
            ]);

        $response->assertSuccessful();
        $this->assertDatabaseHas('opening_hours', [
            'day' => 'Friday',
            'close_on_next_day' => true
        ]);
    }
}
