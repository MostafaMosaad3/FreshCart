<?php

namespace Tests\Feature\Observer;

use App\Models\Product;
use App\Models\Review;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ObserverConcurrencyTest extends TestCase
{
    use RefreshDatabase;

    // Pragmatic test: full parallel testing needs pcntl_fork or a separate harness.
    // As a proxy, verify the final aggregate is correct after back-to-back writes —
    // the lockForUpdate path is exercised even when the writes run sequentially.
    public function test_it_keeps_the_aggregate_correct_across_back_to_back_writes(): void
    {
        $product = Product::factory()->create();

        Review::factory()->forProduct($product)->create(['rating' => 5]);
        Review::factory()->forProduct($product)->create(['rating' => 3]);
        Review::factory()->forProduct($product)->create(['rating' => 4]);

        $this->assertSame(3, $product->fresh()->reviews_count);
        $this->assertSame('4.00', $product->fresh()->rating_avg);   // (5+3+4)/3 = 4.00
    }
}
