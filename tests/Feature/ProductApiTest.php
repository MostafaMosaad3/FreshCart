<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ProductApiTest extends TestCase
{

    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();
        $this->artisan('db:seed');
    }
    /**
     * A basic feature test example.
     */
    public function test_example(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
    }

    public function test_returns_paginated_products() : void
    {
        $response = $this->getJson('/api/products');

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'name', 'slug', 'price', 'currency', 'status', 'vendor'],
                ],
                'links',
                'meta',
            ]);
    }


    public function test_includes_vendor_data_not_vendor_id() : void
    {
        $response = $this->getJson('api/products') ;
        $first = $response->json('data.0');

        $this->assertArrayHasKey('vendor', $first);
        $this->assertArrayHasKey('store_name' , $first['vendor']);
        $this->assertArrayNotHasKey('vendor_id' , $first);
    }


    public function test_only_shows_active_products() : void
    {
        $response = $this->getJson('api/products') ;
        $status = collect($response->json('data'))->pluck('status')->unique()->toArray();

        $this->assertSame(['active'], $status);
    }

    public function test_shows_product_detail_with_categories(): void
    {
        $response = $this->getJson('/api/products/organic-honey');

        $response->assertOk()
            ->assertJsonStructure([
                'data' => ['id', 'name', 'vendor', 'categories'],
            ]);

        $this->assertNotEmpty($response->json('data.categories'));
    }

    public function test_returns_404_for_nonexistent_product(): void
    {
        $response = $this->getJson('/api/products/does-not-exist');

        $response->assertNotFound();
    }
}


