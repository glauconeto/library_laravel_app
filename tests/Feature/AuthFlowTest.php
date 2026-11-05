<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthFlowTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test login flow.
     */
    public function test_login_flow(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);

        $response = $this->post('/login', [
            'email' => 'test@example.com',
            'password' => 'password',
        ]);

        $response->assertRedirect('/dashboard');
        $this->assertAuthenticatedAs($user);
    }

    /**
     * Test registration flow.
     */
    public function test_registration_flow(): void
    {
        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'newuser@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertRedirect('/dashboard');
        $this->assertDatabaseHas('users', ['email' => 'newuser@example.com']);
    }

    /**
     * Test logout flow.
     */
    public function test_logout_flow(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->post('/logout');

        $response->assertRedirect('/login');
        $this->assertGuest();
    }

    /**
     * Test password reset flow.
     */
    public function test_password_reset_flow(): void
    {
        $user = User::factory()->create([
            'email' => 'reset@example.com',
        ]);

        // Request password reset
        $response = $this->post('/forgot-password', [
            'email' => 'reset@example.com',
        ]);

        $response->assertRedirect('/login');
    }

    /**
     * Test access to authenticated route without login.
     */
    public function test_unauthenticated_access_to_dashboard(): void
    {
        $response = $this->get('/dashboard');

        $response->assertRedirect('/login');
    }
}
