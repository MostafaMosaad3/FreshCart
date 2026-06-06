<?php

namespace Tests\Feature\Observer;

use App\Models\Review;
use App\Models\Vendor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VendorReviewObserverTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_maintains_cached_aggregates_on_a_vendor(): void
    {
        $vendor = Vendor::factory()->create();

        Review::factory()->forVendor($vendor)->create(['rating' => 5]);
        Review::factory()->forVendor($vendor)->create(['rating' => 3]);

        $vendor->refresh();
        $this->assertSame('4.00', $vendor->rating_avg);

    }

    public function test_it_resets_vendor_aggregates_when_the_last_review_is_deleted(): void
    {
        $vendor = Vendor::factory()->create();
        $review = Review::factory()->forVendor($vendor)->create(['rating' => 5]);

        $review->delete();
        $vendor->refresh();

        $this->assertSame('0.00', $vendor->rating_avg);
        $this->assertSame(0, $vendor->reviews_count);
    }
}
