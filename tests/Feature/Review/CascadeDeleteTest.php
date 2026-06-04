<?php

namespace Tests\Feature\Review;

use App\Models\Product;
use App\Models\Review;
use App\Models\Vendor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CascadeDeleteTest extends TestCase
{
    use RefreshDatabase;

    public function test_deleting_a_product_cleans_its_reviews(): void
    {
        $product = Product::factory()->create();
        Review::factory()->forProduct($product)->count(3)->create();

        $this->assertSame(3, Review::count());

        $product->delete();   // fires the deleting hook on Product

        $this->assertSame(0, Review::count());
    }

    public function test_deleting_a_vendor_cleans_its_reviews(): void
    {
        $vendor = Vendor::factory()->create();
        Review::factory()->forVendor($vendor)->count(2)->create();

        $vendor->delete();   // fires the deleting hook on Vendor

        $this->assertSame(0, Review::count());
    }
}
