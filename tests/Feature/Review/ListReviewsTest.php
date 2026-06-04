<?php

namespace Tests\Feature\Review;

use App\Models\Product;
use App\Models\Review;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ListReviewsTest extends TestCase
{
    use RefreshDatabase;

    public function test_product_index_returns_only_that_products_reviews(): void
    {
        $a = Product::factory()->create();
        $b = Product::factory()->create();
        Review::factory()->forProduct($a)->count(2)->create();
        Review::factory()->forProduct($b)->count(3)->create();

        $this->actingAs(User::factory()->create())
            ->getJson("/api/products/{$a->id}/reviews")
            ->assertOk()
            ->assertJsonCount(2, 'data');
    }

    public function test_my_reviews_span_products_and_vendors_in_one_query_set(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();
        $vendor = Vendor::factory()->create();
        Review::factory()->forProduct($product)->create(['user_id' => $user->id]);
        Review::factory()->forVendor($vendor)->create(['user_id' => $user->id]);

        DB::enableQueryLog();
        $res = $this->actingAs($user)->getJson('/api/me/reviews');
        $count = count(DB::getQueryLog());
        DB::disableQueryLog();

        $res->assertOk()->assertJsonCount(2, 'data');
        // base + products + vendors + users + pagination count — polymorphic, not N+1.
        $this->assertLessThanOrEqual(6, $count);
    }
}
