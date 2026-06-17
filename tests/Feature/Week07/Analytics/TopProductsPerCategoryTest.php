<?php

namespace Tests\Feature\Week07\Analytics;

use App\Models\Category;
use App\Models\Product;
use App\Repositories\AnalyticsRepository;

class TopProductsPerCategoryTest extends AnalyticsTestCase
{
    public function test_it_returns_at_most_n_products_per_category_ordered_by_rating_then_reviews(): void
    {
        $category = Category::factory()->create();

        // 5 products — top 3 should be deterministic via (rating_avg, reviews_count, id).
        $p1 = Product::factory()->active()->create(['rating_avg' => 5.0, 'reviews_count' => 10]);
        $p2 = Product::factory()->active()->create(['rating_avg' => 5.0, 'reviews_count' => 5]);
        $p3 = Product::factory()->active()->create(['rating_avg' => 4.5, 'reviews_count' => 20]);
        $p4 = Product::factory()->active()->create(['rating_avg' => 4.0, 'reviews_count' => 5]);
        $p5 = Product::factory()->active()->create(['rating_avg' => 3.0, 'reviews_count' => 100]);

        foreach ([$p1, $p2, $p3, $p4, $p5] as $p) {
            $p->categories()->attach($category->id);
        }

        $res = app(AnalyticsRepository::class)->topProductsPerCategory(3);

        $forCategory = $res->where('category_id', $category->id)->values();

        $this->assertSame(3, $forCategory->count());
        $this->assertSame($p1->id, (int) $forCategory[0]->id);   // 5.0, 10 reviews
        $this->assertSame($p2->id, (int) $forCategory[1]->id);   // 5.0, 5 reviews
        $this->assertSame($p3->id, (int) $forCategory[2]->id);   // 4.5, 20 reviews
    }

    public function test_it_excludes_non_active_products(): void
    {
        $category = Category::factory()->create();

        // status defaults to 'draft' in the factory.
        Product::factory()->create(['rating_avg' => 5.0])
            ->categories()->attach($category->id);

        $res = app(AnalyticsRepository::class)->topProductsPerCategory(3);

        $this->assertTrue($res->where('category_id', $category->id)->isEmpty());
    }
}
