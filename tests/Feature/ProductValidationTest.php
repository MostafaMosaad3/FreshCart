<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ProductValidationTest extends TestCase
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

    public function authed(User $user)
    {
        $token = $user->createToken('token' , [$user->role])->plainTextToken;
        return ['authorization' => "Bearer {$token}" , 'accept' => 'application/json'];
    }

    public function test_rejects_unauthenticated_product_creation() :void
    {
        $this->postJson('/api/products', [])->assertUnauthorized();
    }

    public function test_forbids_customer_from_creating_product() :void
    {
        $customer = User::factory()->create();

        $this->withHeaders($this->authed($customer))
            ->postJson('/api/products', [])->assertForbidden();
    }

    public function test_requires_the_core_product_fields(): void
    {
        $user = User::factory()->vendor()->create();
        $vendor = Vendor::factory()->for($user)->create();

        $this->withHeaders($this->authed($user))
            ->postJson('/api/products', [])
            ->assertJsonValidationErrors(['name', 'price', 'category_ids']);

    }

    public function test_rejects_compare_price_less_than_price() :void
    {
        $user = User::factory()->vendor()->create();
        Vendor::factory()->for($user)->create();
        $categories = Category::factory()->create();


        $this->withHeaders($this->authed($user))
            ->postJson('/api/products', [
                'name'          => 'Test',
                'slug'          => 'test-' . uniqid(),
                'description'   => 'A',
                'price'         => 100,
                'compare_price' => 50,
                'category_ids'  => [$categories->id],
            ])->assertJsonValidationErrors('compare_price');;
    }

    public function test_rejects_invalid_status(): void
    {
        $user = User::factory()->vendor()->create();
        Vendor::factory()->for($user)->create();
        $cat  = Category::factory()->create();

        $this->withHeaders($this->authed($user))
            ->postJson('/api/products', [
                'name'         => 'Test',
                'slug'         => 'test-' . uniqid(),
                'description'  => 'A',
                'price'        => 100,
                'status'       => 'not-a-real-status',
                'category_ids' => [$cat->id],
            ])
            ->assertJsonValidationErrors('status');
    }

    public function test_accepts_valid_product_input(): void
    {
        $user = User::factory()->vendor()->create();
        Vendor::factory()->for($user)->create();
        $cat  = Category::factory()->create();

        $this->withHeaders($this->authed($user))
            ->postJson('/api/products', [
                'name'         => 'Test Product',
                'slug'         => 'test-product-' . uniqid(),
                'description'  => 'Valid description',
                'price'        => 150,
                'category_ids' => [$cat->id],
            ])
            ->assertCreated();
    }





}
