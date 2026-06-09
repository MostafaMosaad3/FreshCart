<?php

namespace Tests\Feature\Week07\Search;

use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductSearchBasicTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['scout.driver' => 'collection']);
    }

    public function test_it_finds_a_product_by_exact_name_match(): void
    {
        Product::factory()->create(['name' => 'Organic Honey', 'status' => 'active']);
        Product::factory()->create(['name' => 'Dark Chocolate', 'status' => 'active']);

        $res = $this->getJson('/api/search/products?q=honey');

        $res->assertOk();
        $res->assertJsonCount(1, 'data');
        $this->assertSame('Organic Honey', $res->json('data.0.name'));
    }

    public function test_it_excludes_draft_products(): void
    {
        Product::factory()->create(['name' => 'Organic Honey', 'status' => 'draft']);

        $res = $this->getJson('/api/search/products?q=honey');

        $res->assertOk();
        $res->assertJsonCount(0, 'data');
    }
}
