<?php

namespace Tests\Feature\Week07\Search;

use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductSearchSortTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['scout.driver' => 'collection']);
    }

    public function test_it_sorts_by_price_ascending_when_requested(): void
    {
        Product::factory()->create(['name' => 'Honey A', 'price' => 300, 'status' => 'active']);
        Product::factory()->create(['name' => 'Honey B', 'price' => 100, 'status' => 'active']);
        Product::factory()->create(['name' => 'Honey C', 'price' => 200, 'status' => 'active']);

        $res = $this->getJson('/api/search/products?q=honey&sort=price_asc');

        $prices = collect($res->json('data'))->pluck('price')->map(fn ($p) => (float) $p)->all();
        $this->assertSame([100.0, 200.0, 300.0], $prices);
    }
}
