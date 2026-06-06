<?php

namespace Tests\Feature\Observer;

use App\Models\Product;
use App\Models\Review;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ObserverTransactionTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_rolls_back_the_aggregate_update_if_the_transaction_throws(): void
    {
        $product = Product::factory()->create(['rating_avg' => 0, 'reviews_count' => 0]);

        try {
            DB::transaction(function () use ($product) {
                Review::factory()->forProduct($product)->create(['rating' => 5]);
                throw new \RuntimeException('boom');
            });
        } catch (\RuntimeException) {
            // swallowed on purpose
        }

        $product->refresh();
        $this->assertSame('0.00', $product->rating_avg);
        $this->assertSame(0, $product->reviews_count);
        $this->assertSame(0, Review::count());
    }
}
