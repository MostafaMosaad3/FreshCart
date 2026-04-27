<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class AuthTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    public function test_example(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
    }

    public function test_registers_a_new_user_and_returns_a_token(): void
    {
        $response = $this->postJson('/api/register', [
            'name' => 'test user',
            'email' => 'test@freshcart.test' ,
            'password' => 'password',
            'password_confirmation' => 'password'
        ]) ;

        $response->assertCreated()
            ->asssertJsonStructure(['user' => ['id', 'name', 'email'] , 'token']) ;


        $this->assertTrue(User::where('email', 'test@freshcart.test')->exists());
    }

    public function test_reject_login_with_wrong_password(): void
    {
        $user = User::factory()->create([
            'email' => 'test@freshcart.test' ,
            'password' => 'right_password'
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'test@freshcart.test' ,
            'password' => 'wrong_password'
        ]) ;

        $response->assertStatus(422)
            ->assertJsonValidationErrors('email');


        $this->assertNull($response->json('token'));

    }

    public function test_authenticated_user_can_access_me(): void
    {
        $user = User::factory()->create() ;
        $token = $user->createToken('test_token')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson('/api/me');

        $response->assertOK()
            ->assertJson(['data' => ['id' => $user->id, 'email' => $user->email]]);

    }

    public function test_logout_invalidates_the_token(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test-token')->plainTextToken;

        $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/logout')
            ->assertNoContent();

        $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson('/api/me')
            ->assertUnauthorized();
    }



}
