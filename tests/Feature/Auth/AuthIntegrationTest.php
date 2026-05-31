<?php

namespace Tests\Feature\Auth;

use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthIntegrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_full_week_2_story_works_end_to_end(): void
    {
        // ---- Step 1: Register a vendor ----
        $registerResponse = $this->postJson('api/register', [
            'name' => 'Sara Vendor',
            'email' => 'sara@shop.test',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
            'role' => 'vendor',
            'phone' => '01012345678',
            'store_name' => 'Sara Store',
        ]);

        $registerResponse->assertStatus(201)
            ->assertJsonStructure(['data' => ['user' => ['id', 'email', 'role'], 'token']]);

        $vendorToken = $registerResponse->json('data.token');
        $vendorUser = User::where('email', 'sara@shop.test')->firstOrFail();

        // ---- Step 2: Login with WRONG password — generic error, no token ----
        $this->postJson('api/login', [
            'email' => 'sara@shop.test',
            'password' => 'WrongPassword!',
        ])->assertStatus(422)
            ->assertJsonMissingPath('data.token')
            ->assertJsonPath('errors.email.0', 'These credentials do not match our records.');

        // ---- Step 3: Login with CORRECT password ----
        $loginResponse = $this->postJson('api/login', [
            'email' => 'sara@shop.test',
            'password' => 'Password123!',
        ])->assertStatus(200);

        $freshToken = $loginResponse->json('data.token');
        $this->assertNotEquals($vendorToken, $freshToken, 'Each login should mint a new token');

        // ---- Step 4: /api/me with token returns the user ----
        $this->withToken($freshToken)
            ->getJson('api/me')
            ->assertStatus(200)
            ->assertJsonPath('data.email', 'sara@shop.test');

        // ---- Step 5: Customer cannot create a product (policy denies) ---
        $customer = User::factory()->customer()->create();
        $customerToken = $customer->createToken('test')->plainTextToken;

        $this->app['auth']->forgetGuards();
        $this->withToken($customerToken)
            ->postJson('/api/products', $this->productPayload())
            ->assertStatus(403);

        // ---- Step 6: Vendor CAN create a product ----
        $this->app['auth']->forgetGuards();
        $createResponse = $this->withToken($freshToken)
            ->postJson('/api/products', $this->productPayload())
            ->assertStatus(201);

        $productId = $createResponse->json('data.id');
        $this->assertDatabaseHas('products', [
            'id' => $productId,
            'vendor_id' => $vendorUser->vendor->id,
        ]);

        // ---- Step 7: ANOTHER vendor cannot edit Sara's product ----
        $otherVendor = User::factory()->vendor()->create();
        $otherVendorToken = $otherVendor->createToken('test')->plainTextToken;

        $this->app['auth']->forgetGuards();
        $this->withToken($otherVendorToken)
            ->putJson("/api/products/{$productId}", ['name' => 'Hijacked'])
            ->assertStatus(403);

        // ---- Step 8: Admin can edit ANY product ----
        $admin = User::factory()->admin()->create();
        $adminToken = $admin->createToken('test')->plainTextToken;

        $this->app['auth']->forgetGuards();
        $this->withToken($adminToken)
            ->putJson("/api/products/{$productId}", ['name' => 'Admin Override'])
            ->assertStatus(200)
            ->assertJsonPath('data.name', 'Admin Override');

        // ---- Step 9: Vendor logs out — same token returns 401 ----
        $this->app['auth']->forgetGuards();
        $this->withToken($freshToken)
            ->postJson('/api/logout')
            ->assertStatus(204);

        $this->app['auth']->forgetGuards();

        $this->withToken($freshToken)
            ->getJson('/api/me')
            ->assertStatus(401);

    }

    private function productPayload(): array
    {
        $category = Category::factory()->create();

        return [
            'name' => 'Premium Olive Oil 1L',
            'price' => 250.00,
            'compare_price' => 300.00,
            'stock' => 50,
            'status' => 'active',
            'category_ids' => [$category->id],
        ];
    }
}
