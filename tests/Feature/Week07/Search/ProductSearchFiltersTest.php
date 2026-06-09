<?php

namespace Tests\Feature\Week07\Search;

use App\Models\Product;
use App\Models\Vendor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductSearchFiltersTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['scout.driver' => 'collection']);
    }

    public function test_it_filters_by_vendor_id(): void
    {
        $v1 = Vendor::factory()->create();
        $v2 = Vendor::factory()->create();

        Product::factory()->for($v1)->create(['name' => 'Honey A', 'status' => 'active']);
        Product::factory()->for($v2)->create(['name' => 'Honey B', 'status' => 'active']);

        $res = $this->getJson("/api/search/products?q=honey&vendor_id={$v1->id}");

        $res->assertJsonCount(1, 'data');
        $this->assertSame('Honey A', $res->json('data.0.name'));
    }

    public function test_it_applies_price_range_filter(): void
    {
        Product::factory()->create(['name' => 'Cheap Honey', 'price' => 50, 'status' => 'active']);
        Product::factory()->create(['name' => 'Expensive Honey', 'price' => 500, 'status' => 'active']);

        $res = $this->getJson('/api/search/products?q=honey&price_max=100');

        $res->assertJsonCount(1, 'data');
        $this->assertSame('Cheap Honey', $res->json('data.0.name'));
    }
}
