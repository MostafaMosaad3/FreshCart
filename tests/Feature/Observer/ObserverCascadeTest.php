<?php

namespace Tests\Feature\Observer;

use App\Models\Product;
use App\Models\Review;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ObserverCascadeTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_cascade_deletes_reviews_via_the_product_observer_deleting_hook(): void
    {
        $product = Product::factory()->create();
        Review::factory()->forProduct($product)->count(3)->create();

        $product->delete();

        $this->assertSame(0, Review::count());
    }
}
