<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\Vendor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RelationshipEdgeCaseTest extends TestCase
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

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('db:seed');
    }

    public function test_returns_null_vendor_for_orphaned_products(): void
    {
        $orphan = Product::where('name', 'Orphaned Product')->first();

        $this->assertNull($orphan->vendor);
    }

    public function test_vendor_products_relation_can_be_counted_without_loading(): void
    {
        $sara = Vendor::where('store_name', "Sara's Gourmet")->withCount('products')->first();

        $this->assertSame(3, $sara->products_count);
        $this->assertFalse($sara->relationLoaded('products'));
    }
}
