<?php

namespace Tests\Feature\Observer;

use App\Models\Product;
use App\Models\Review;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReviewObserverAggregateTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_sets_rating_avg_and_reviews_count_when_a_review_is_created(): void
    {
        $product = Product::factory()->create();

        Review::factory()->forProduct($product)->create(['rating' => 5]);
        Review::factory()->forProduct($product)->create(['rating' => 3]);

        $product->refresh();

        $this->assertSame('4.00', $product->rating_avg);
        $this->assertSame(2, $product->reviews_count);
    }

    public function test_it_recomputes_when_a_review_rating_is_updated(): void
    {
        $product = Product::factory()->create();
        $review = Review::factory()->forProduct($product)->create(['rating' => 5]);

        $review->update(['rating' => 3]);

        $this->assertSame('3.00', $product->fresh()->rating_avg);
    }

    public function test_it_skips_recompute_when_only_the_comment_changes(): void
    {
        $product = Product::factory()->create();
        $review = Review::factory()->forProduct($product)->create(['rating' => 4]);
        $before = $product->fresh()->updated_at;
        sleep(1);

        $review->update(['comment' => 'edited comment, rating unchanged']);

        // Product wasn't touched (updated_at unchanged) → observer correctly short-circuited.
        $this->assertEquals(
            $before->toIso8601String(),
            $product->fresh()->updated_at->toIso8601String()
        );
    }

    public function test_it_recomputes_when_a_review_is_deleted(): void
    {
        $product = Product::factory()->create();
        Review::factory()->forProduct($product)->create(['rating' => 4]);
        $review = Review::factory()->forProduct($product)->create(['rating' => 2]);

        $review->delete();

        $product->refresh();
        $this->assertSame('4.00', $product->rating_avg);
        $this->assertSame(1, $product->reviews_count);
    }

    public function test_it_sets_rating_avg_to_zero_when_all_reviews_deleted(): void
    {
        $product = Product::factory()->create();
        $r1 = Review::factory()->forProduct($product)->create();
        $r2 = Review::factory()->forProduct($product)->create();

        $r1->delete();
        $r2->delete();

        $product->refresh();
        $this->assertSame('0.00', $product->rating_avg);
        $this->assertSame(0, $product->reviews_count);
    }
}
