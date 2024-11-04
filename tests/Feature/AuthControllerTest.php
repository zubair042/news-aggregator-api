<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class AuthControllerTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_can_register_a_user()
    {
        $response = $this->postJson('/api/register', [
            'name' => 'Test User',
            'email' => 'testuser@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'user' => [
                        'id',
                        'name',
                        'email',
                    ],
                    'access_token',
                    'token_type',
                ]
            ]);
    }

    #[Test]
    public function it_returns_validation_errors_on_registration()
    {
        $response = $this->postJson('/api/register', [
            'name' => '',
            'email' => 'invalid-email',
            'password' => 'short',
        ]);

        $response->assertStatus(422)
            ->assertJsonStructure(['success', 'message', 'errors']);
    }

    #[Test]
    public function it_can_login_a_user()
    {
        $user = User::factory()->create([
            'password' => bcrypt('password')
        ]);

        $response = $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'user' => [
                        'id',
                        'name',
                        'email',
                    ],
                    'access_token',
                    'token_type',
                ]
            ]);
    }

    #[Test]
    public function it_returns_error_on_failed_login()
    {
        $user = User::factory()->create([
            'password' => bcrypt('password')
        ]);

        $response = $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => 'wrong-password',
        ]);

        $response->assertStatus(401)
            ->assertJson([
                'success' => false,
                'message' => 'The provided credentials are incorrect.',
            ]);
    }

    #[Test]
    public function it_can_logout_a_user()
    {
        $this->withoutMiddleware(); // Skip authentication middleware for this test
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/logout');

        $response->assertStatus(200)
            ->assertJson(['success' => true, 'message' => 'Successfully logged out']);
    }

    #[Test]
    public function it_can_update_user_password()
    {
        $user = User::factory()->create([
            'password' => bcrypt('oldpassword')
        ]);
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/update-password', [
            'current_password' => 'oldpassword',
            'new_password' => 'newpassword',
            'new_password_confirmation' => 'newpassword',
        ]);

        $response->assertStatus(200)
            ->assertJson(['success' => true, 'message' => 'Password has been updated successfully']);
    }

    #[Test]
    public function it_returns_error_if_current_password_is_incorrect()
    {
        $user = User::factory()->create([
            'password' => bcrypt('oldpassword')
        ]);
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/update-password', [
            'current_password' => 'wrongpassword',
            'new_password' => 'newpassword',
            'new_password_confirmation' => 'newpassword',
        ]);

        $response->assertStatus(403)
            ->assertJson(['success' => false, 'message' => 'The current password is incorrect.']);
    }

    #[Test]
    public function it_returns_validation_errors_on_password_update()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/update-password', [
            'current_password' => 'oldpassword',
            'new_password' => 'short',
        ]);

        $response->assertStatus(422)
            ->assertJsonStructure(['success', 'message', 'errors']);
    }
}
