<?php

namespace Tests\Feature\Review;

use App\Models\Order;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CreateProductReviewTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Build a user who has actually purchased the given product
     * (a delivered order whose item points at one of the product's variants).
     */
    private function purchaser(Product $product): User
    {
        $user = User::factory()->create();
        $variant = ProductVariant::factory()->create(['product_id' => $product->id]);

        // order_items reference variant_id (not product_id) and need a line_total.
        Order::factory()->for($user)->create(['status' => 'delivered'])
            ->items()->create([
                'variant_id' => $variant->id,
                'quantity' => 1,
                'unit_price' => 100,
                'line_total' => 100,
            ]);

        return $user;
    }

    public function test_a_purchaser_can_review_a_product(): void
    {
        $product = Product::factory()->create();
        $user = $this->purchaser($product);

        $this->actingAs($user)
            ->postJson("/api/products/{$product->id}/reviews", ['rating' => 5, 'comment' => 'great'])
            ->assertCreated();

        $this->assertSame(1, Review::count());
        $this->assertSame('product', Review::first()->reviewable_type);   // short alias from morph map
    }

    public function test_a_non_purchaser_is_rejected(): void
    {
        $product = Product::factory()->create();

        $this->actingAs(User::factory()->create())
            ->postJson("/api/products/{$product->id}/reviews", ['rating' => 5])
            ->assertForbidden();
    }

    public function test_a_second_review_on_the_same_product_is_rejected(): void
    {
        $product = Product::factory()->create();
        $user = $this->purchaser($product);
        Review::factory()->forProduct($product)->create(['user_id' => $user->id, 'rating' => 3]);

        $this->actingAs($user)
            ->postJson("/api/products/{$product->id}/reviews", ['rating' => 5])
            ->assertForbidden();
    }

    public function test_rating_out_of_range_is_rejected(): void
    {
        $product = Product::factory()->create();
        $user = $this->purchaser($product);

        $this->actingAs($user)
            ->postJson("/api/products/{$product->id}/reviews", ['rating' => 6])
            ->assertStatus(422);
    }
}
