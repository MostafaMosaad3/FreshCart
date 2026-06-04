<?php

namespace Tests\Feature\Review;

use App\Models\Order;
use App\Models\Product;
use App\Models\Review;
use Illuminate\Database\ClassMorphViolationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MorphMapTest extends TestCase
{
    use RefreshDatabase;

    public function test_reviewable_type_stores_the_short_alias(): void
    {
        $product = Product::factory()->create();
        $review = Review::factory()->forProduct($product)->create();

        $this->assertSame('product', $review->fresh()->reviewable_type);
        $this->assertInstanceOf(Product::class, $review->reviewable);
    }

    public function test_an_unmapped_model_throws(): void
    {
        // Order is NOT in the morph map. Under enforceMorphMap, resolving the morph
        // alias for an unmapped class throws — that is the guard against ghost types.
        $this->expectException(ClassMorphViolationException::class);

        (new Order)->getMorphClass();
    }
}
