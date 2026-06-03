<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegisterValidationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * A basic feature test example.
     */
    public function test_example(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
    }

    public function test_rejects_registration_without_an_email(): void
    {
        $this->postJson('api/register', [
            'name' => 'test',
            'password' => 'password',
            'password_confirmation' => 'password',
        ])->assertStatus(422)->assertJsonValidationErrors('email');
    }

    public function test_rejects_an_invalid_email_format(): void
    {
        $this->postJson('api/register', [
            'name' => 'Test',
            'email' => 'not-an-email',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ])->assertJsonValidationErrors('email');
    }

    public function test_rejects_a_short_password(): void
    {
        $this->postJson('/api/register', [
            'name' => 'Test',
            'email' => 'x@x.com',
            'password' => '123',
            'password_confirmation' => '123',
        ])->assertJsonValidationErrors('password');
    }

    public function test_rejects_a_duplicate_email(): void
    {
        User::factory()->create(['email' => 'taken@x.com']);
        $this->postJson('/api/register', [
            'name' => 'Test',
            'email' => 'taken@x.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ])->assertJsonValidationErrors('email');
    }

    public function test_rejects_non_egyptian_phone_numbers(): void
    {
        $this->postJson('/api/register', [
            'name' => 'Test',
            'email' => 'x@x.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'phone' => '+13235551234',
        ])->assertJsonValidationErrors('phone');
    }

    public function test_normalize_email_before_storing(): void
    {
        $this->postJson('/api/register', [
            'name' => 'Sara',
            'email' => '  SARA@X.COM  ',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ])->assertCreated();

        $this->assertTrue(User::Where('email', 'sara@x.com')->exists());
    }
}
