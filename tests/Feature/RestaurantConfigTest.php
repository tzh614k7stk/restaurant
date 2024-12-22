<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\RestaurantConfig;
use Illuminate\Support\Facades\DB;

class RestaurantConfigTest extends TestCase
{
    use RefreshDatabase;

    private $employee;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();

        $this->employee = User::factory()->create();
        DB::table('employees')->insert([
            'user_id' => $this->employee->id,
            'admin' => true
        ]);
    }

    public function test_can_save_config()
    {
        $response = $this->actingAs($this->employee)
            ->postJson('/api/admin/save_config', [
                'key' => 'test_key',
                'value' => 'test_value'
            ]);

        $response->assertSuccessful();
        $this->assertDatabaseHas('restaurant_config', [
            'name' => 'test_key',
            'value' => 'test_value'
        ]);
    }

    public function test_can_update_existing_config()
    {
        RestaurantConfig::create([
            'name' => 'test_key',
            'value' => 'old_value'
        ]);

        $response = $this->actingAs($this->employee)
            ->postJson('/api/admin/save_config', [
                'key' => 'test_key',
                'value' => 'new_value'
            ]);

        $response->assertSuccessful();
        $this->assertDatabaseHas('restaurant_config', [
            'name' => 'test_key',
            'value' => 'new_value'
        ]);
    }

    public function test_can_create_delete_duration()
    {
        $response = $this->actingAs($this->employee)
            ->postJson('/api/admin/delete_duration', [
                'duration' => 1.5
            ]);
        $response->assertSuccessful();

        $response = $this->actingAs($this->employee)
            ->postJson('/api/admin/create_duration', [
                'duration' => 1.5
            ]);
        $response->assertSuccessful();

        $config = RestaurantConfig::where('name', 'durations')->first();
        $durations = json_decode($config->value);
        $this->assertTrue(in_array(1.5, $durations));
    }

    public function test_rejects_invalid_duration_decimal()
    {
        $response = $this->actingAs($this->employee)
            ->postJson('/api/admin/create_duration', [
                'duration' => 1.7
            ]);

        $response->assertStatus(422);
    }
}
