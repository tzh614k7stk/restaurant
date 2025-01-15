<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Employee;
use App\Models\RestaurantConfig;

class AdminConfigDataTest extends TestCase
{
    use RefreshDatabase;

    private $employee;
    private $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();

        //regular employee
        $this->employee = User::factory()->create();
        Employee::create([
            'user_id' => $this->employee->id,
            'admin' => false
        ]);

        //admin employee
        $this->admin = User::factory()->create();
        Employee::create([
            'user_id' => $this->admin->id,
            'admin' => true
        ]);

        //set up basic config
        RestaurantConfig::create([
            'name' => 'timezone',
            'value' => 'UTC'
        ]);
        RestaurantConfig::create([
            'name' => 'durations',
            'value' => json_encode([1, 1.5, 2])
        ]);
    }

    public function test_can_get_admin_config()
    {
        $response = $this->actingAs($this->employee)->postJson('/api/admin/config');

        $response->assertSuccessful()
            ->assertJsonStructure([
                'success',
                'timezone',
                'opening_hours',
                'custom_opening_hours',
                'closing_dates',
                'durations',
                'employees',
                'max_future_reservations',
                'min_hours_before_reservation',
                'max_days_in_advance',
                'phone',
                'email'
            ]);
    }

    public function test_only_admin_sees_employees()
    {
        //regular employee request
        $response = $this->actingAs($this->employee)->postJson('/api/admin/config');
        
        $response->assertSuccessful();
        $this->assertEmpty($response['employees']);

        //admin request
        $response = $this->actingAs($this->admin)->postJson('/api/admin/config');
        
        $response->assertSuccessful();
        $this->assertNotEmpty($response['employees']);
    }

    public function test_non_employee_cannot_access_config()
    {
        $user = User::factory()->create();
        
        $response = $this->actingAs($user)->postJson('/api/admin/config');
        $response->assertStatus(401);
    }
}
