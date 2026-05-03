<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_registers_a_new_user_and_returns_a_token(): void
    {
        $response = $this->postJson('/api/register', [
            'name'                  => 'Test User',
            'email'                 => 'test@freshcart.test',
            'password'              => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertCreated()
            ->assertJsonStructure(['data' => ['user' => ['id', 'name', 'email'], 'token']]);

        $this->assertTrue(User::where('email', 'test@freshcart.test')->exists());
    }

    public function test_reject_login_with_wrong_password(): void
    {
        User::factory()->create([
            'email'    => 'test@freshcart.test',
            'password' => 'right_password',
        ]);

        $response = $this->postJson('/api/login', [
            'email'    => 'test@freshcart.test',
            'password' => 'wrong_password',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('email');

        $this->assertNull($response->json('token'));
    }

    public function test_authenticated_user_can_access_me(): void
    {
        $user  = User::factory()->create();
        $token = $user->createToken('test_token')->plainTextToken;

        $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson('/api/me')
            ->assertOk()
            ->assertJson(['data' => ['id' => $user->id, 'email' => $user->email]]);
    }

    public function test_logout_invalidates_the_token(): void
    {
        $user  = User::factory()->create();
        $token = $user->createToken('test-token')->plainTextToken;

        $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/logout')
            ->assertNoContent();

        $this->app['auth']->forgetGuards();

        $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson('/api/me')
            ->assertUnauthorized();
    }
}
