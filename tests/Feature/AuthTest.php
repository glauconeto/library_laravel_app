<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::where('email', 'bob@example.com')->first();
        if (!$this->user) {
            $this->user = User::create([
                'name' => 'Bob',
                'email' => 'bob@example.com',
                'password' => bcrypt('password123'),
            ]);
        }
    }

    /**
     * Test login with valid credentials
     */
    public function test_login_with_valid_credentials(): void
    {
        $response = $this->postJson('/login', [
            'email' => $this->user->email,
            'password' => 'password123',
        ]);

        $response->assertStatus(200);
    }

    /**
     * Test login with invalid credentials
     */
    public function test_login_with_invalid_credentials(): void
    {
        $response = $this->postJson('/login', [
            'email' => 'test@example.com',
            'password' => 'wrong-password',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['email']);
    }

    /**
     * Test login with missing email
     */
    public function test_login_with_missing_email(): void
    {
        $response = $this->postJson('/login', [
            'password' => 'password',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['email']);
    }

    /**
     * Test login with missing password
     */
    public function test_login_with_missing_password(): void
    {
        $response = $this->postJson('/login', [
            'email' => 'test@example.com',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['password']);
    }

    /**
     * Test logout
     */
    public function test_logout(): void
    {
        $this->actingAs($this->user);
        $response = $this->post('/logout');

        // Fortify returns 302 redirect for web requests, 204 for JSON
        $response->assertStatus(302);
        $this->assertGuest();
    }

    /**
     * Test password reset link request
     */
    public function test_password_reset_link_request(): void
    {
        $response = $this->postJson('/forgot-password', [
            'email' => 'bob@example.com',
        ]);

        $response->assertStatus(200);
    }

    /**
     * Test password reset with valid token
     */
    public function test_password_reset_with_valid_token(): void
    {
        $token = app(\Illuminate\Auth\Passwords\PasswordBroker::class)->createToken($this->user);

        $response = $this->postJson('/reset-password', [
            'email' => 'bob@example.com',
            'password' => 'new-password',
            'password_confirmation' => 'new-password',
            'token' => $token,
        ]);

        // Fortify returns 200 for JSON requests on success
        $response->assertStatus(200);
    }

    /**
     * Test password reset with invalid token
     */
    public function test_password_reset_with_invalid_token(): void
    {
        $response = $this->postJson('/reset-password', [
            'email' => 'bob@example.com',
            'token' => 'invalid-token',
            'password' => 'new-password',
            'password_confirmation' => 'new-password',
        ]);

        $response->assertStatus(422);
    }

    /**
     * Test register with valid data
     */
    public function test_register_with_valid_data(): void
    {
        $response = $this->postJson('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('users', [
            'email' => 'test@example.com',
        ]);
    }

    /**
     * Test register with invalid email
     */
    public function test_register_with_invalid_email(): void
    {
        $response = $this->postJson('/register', [
            'name' => 'Test User',
            'email' => 'invalid-email',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertStatus(422);
    }

    /**
     * Test register with mismatched passwords
     */
    public function test_register_with_mismatched_passwords(): void
    {
        $response = $this->postJson('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'different-password',
        ]);

        $response->assertStatus(422);
    }
}
