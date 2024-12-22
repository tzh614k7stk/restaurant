<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class EmployeeManagementTest extends TestCase
{
    use RefreshDatabase;

    private $admin;
    private $employee;
    private $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();

        //create admin
        $this->admin = User::factory()->create();
        DB::table('employees')->insert([
            'user_id' => $this->admin->id,
            'admin' => true
        ]);

        //create regular employee
        $this->employee = User::factory()->create();
        DB::table('employees')->insert([
            'user_id' => $this->employee->id,
            'admin' => false
        ]);

        //create regular user
        $this->user = User::factory()->create();
    }

    public function test_non_admin_cannot_add_employee()
    {
        $response = $this->actingAs($this->employee)
            ->postJson('/api/admin/add_employee', [
                'id' => $this->user->id
            ]);

        $response->assertStatus(401);
    }

    public function test_admin_can_add_employee()
    {
        $response = $this->actingAs($this->admin)
            ->postJson('/api/admin/add_employee', [
                'id' => $this->user->id
            ]);

        $response->assertSuccessful();
        $this->assertDatabaseHas('employees', ['user_id' => $this->user->id]);
    }

    public function test_cannot_add_duplicate_employee()
    {
        $response = $this->actingAs($this->admin)
            ->postJson('/api/admin/add_employee', [
                'id' => $this->employee->id
            ]);

        $response->assertStatus(422);
    }

    public function test_non_admin_cannot_remove_employee()
    {
        $employee_id = DB::table('employees')
            ->where('user_id', $this->employee->id)
            ->value('id');

        $response = $this->actingAs($this->employee)
            ->postJson('/api/admin/remove_employee', [
                'id' => $employee_id
            ]);

        $response->assertStatus(401);
    }

    public function test_admin_can_remove_employee()
    {
        $employee_id = DB::table('employees')
            ->where('user_id', $this->employee->id)
            ->value('id');

        $response = $this->actingAs($this->admin)
            ->postJson('/api/admin/remove_employee', [
                'id' => $employee_id
            ]);

        $response->assertSuccessful();
        $this->assertDatabaseMissing('employees', ['id' => $employee_id]);
    }
}
