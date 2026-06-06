<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ScopesTest extends TestCase
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

    public function test_active_scope_returns_only_active_products(): void
    {
        Product::factory()->count(2)->active()->create();
        Product::factory()->create();

        $this->assertSame(2, Product::active()->count());
    }

    public function test_min_price_filters_by_minimum_price(): void
    {
        Product::factory()->create(['price' => 20]);
        Product::factory()->create(['price' => 150]);
        Product::factory()->create(['price' => 200]);

        $this->assertSame(2, Product::minPrice(100)->count());
    }

    public function test_from_unverified_vendor_filters_via_relationship(): void
    {
        $verified = Vendor::factory()->verified()->create();
        $unverified = Vendor::factory()->unverified()->create();

        Product::factory()->for($verified)->create();
        Product::factory()->for($unverified)->create();

        $this->assertSame(1, Product::fromUnVerifiedVendor()->count());
    }

    public function test_vendor_verified_scope_returns_only_verified(): void
    {
        Vendor::factory()->count(2)->verified()->create();
        Vendor::factory()->count(2)->create();

        $this->assertSame(2, Vendor::verified()->count());
    }

    public function test_category_top_level_returns_only_parents(): void
    {
        $parent = Category::factory()->create();
        Category::factory()->count(2)->create(['parent_id' => $parent->id]);

        $this->assertSame(1, Category::topLevel()->count());
    }

    public function test_vendor_owned_scope_filters_when_authenticated_as_vendor_user(): void
    {
        $user = User::factory()->create();

        $vendor = Vendor::factory()->for($user)->create();
        $otherVendors = Vendor::factory()->create();

        $products = Product::factory()->count(2)->for($vendor)->create();
        $otherProduct = Product::factory()->count(3)->for($otherVendors)->create();

        $this->actingAs($user);

        $this->assertSame(2, $products->count());
        $this->assertSame(3, $otherProduct->count());

    }
}
