<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ProductAuthorizationTest extends TestCase
{

    use RefreshDatabase;    /**
     * A basic feature test example.
     */
    public function test_example(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
    }

    private function tokenFor(User $user): string
    {
        return $user->createToken('test')->plainTextToken;
    }

    public function test_admin_can_update_anyProduct() : void
    {
        $user = User::factory()->admin()->create();
        $vendor = Vendor::factory()->create() ;
        $product = Product::factory()->for($vendor)->create();

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->tokenFor($user))
            ->putJson("api/products/{$product->id}" , ['name' => 'renamed by admin']) ;

        $response->assertOk() ;
        $this->assertSame('Renamed by admin', $product->fresh()->name);
    }


    public function test_vendor_can_update_thier_own_product() : void
    {
        $user = User::factory()->vendor()->create() ;
        $vendor = Vendor::factory()->for($user)->create() ;
        $product = Product::factory()->for($vendor)->create() ;

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->tokenFor($user))
            ->putJson("api/products/ {$product->id}" , ['name' => 'renamed by vendor']) ;

        $response->assertOk() ;
    }

    public function test_vendor_cannot_update_another_vendors_product() : void
    {
        $sara = User::factory()->vendor()->create() ;
        $saraVendor = Vendor::factory()->for($sara)->create() ;

        $omar = User::factory()->vendor()->create() ;
        $omarVendor = Vendor::factory()->for($omar)->create() ;
        $omarProduct = Product::factory()->for($omarVendor)->create() ;

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->tokenFor($sara))
            ->putJson("api/products/{$omarProduct->id}" , ['name' => 'renamed by Sare']) ;

        $response->assertForbidden();
        $response->assertNotSame('Hacked By Sare' . $product->fresh()->name);
    }

    public function test_customer_cannot_create_products(): void
    {
        $customer = User::factory()->customer()->create();

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->tokenFor($customer))
            ->postJson('/api/products', [
                'name'        => 'X',
                'slug'        => 'x-' . uniqid(),
                'description' => 'Y',
                'price'       => 10,
            ]);

        $response->assertForbidden();
    }

    public function test_unauthenticated_user_cannot_access_update(): void
    {
        $product = Product::factory()->create();

        $this->putJson("/api/products/{$product->id}", ['name' => 'X'])
            ->assertUnauthorized();
    }

    public function test_vendor_sees_only_their_own_products_in_index(): void
    {
        $sara       = User::factory()->vendor()->create();
        $saraVendor = Vendor::factory()->for($sara)->create();
        Product::factory()->for($saraVendor)->count(3)->create();

        $omar       = User::factory()->vendor()->create();
        $omarVendor = Vendor::factory()->for($omar)->create();
        Product::factory()->for($omarVendor)->count(2)->create();

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->tokenFor($sara))
            ->getJson('/api/products');

        $response->assertOk();

        $vendorIds = collect($response->json('data'))->pluck('vendor.id')->unique()->values()->toArray();
        $this->assertSame([$saraVendor->id], $vendorIds);
    }

}
