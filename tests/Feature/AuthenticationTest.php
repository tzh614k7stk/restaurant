<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Employee;

class AuthenticationTest extends TestCase
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

    public function test_guest_redirected_to_login()
    {
        $response = $this->get('/admin');
        $response->assertRedirect('/login');
    }

    public function test_non_employee_cannot_access_admin()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/admin');
        $response->assertRedirect('/');
    }

    public function test_employee_can_access_admin()
    {
        $response = $this->actingAs($this->employee)->get('/admin');
        $response->assertSuccessful();
        $response->assertViewIs('admin');
    }

    public function test_redirects_work()
    {
        $response = $this->get('/employees');
        $response->assertRedirect('/login');
        
        $response = $this->actingAs($this->employee)->get('/employees');
        $response->assertRedirect('/admin');

        $response = $this->get('/home');
        $response->assertRedirect('/');

        $response = $this->get('/information');
        $response->assertRedirect('/about');

        $response = $this->get('/logout');
        $response->assertRedirect('/');
    }
}
