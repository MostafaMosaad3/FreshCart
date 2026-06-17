<?php

namespace Tests\Feature\Week07\Analytics\CTE;

use App\Enums\OrderStatus;
use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Repositories\AnalyticsRepository;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Tests\Feature\Week07\Analytics\AnalyticsTestCase;

class CategoryPerformanceTreeTest extends AnalyticsTestCase
{
    public function test_a_parent_aggregates_product_counts_across_its_whole_subtree(): void
    {
        // root -> child -> grandchild
        $root = Category::factory()->create(['parent_id' => null]);
        $child = Category::factory()->create(['parent_id' => $root->id]);
        $grandchild = Category::factory()->create(['parent_id' => $child->id]);

        $this->attachActiveProducts($child, 2);
        $this->attachActiveProducts($grandchild, 1);

        $rows = app(AnalyticsRepository::class)->categoryPerformanceTree();
        $rootRow = $rows->firstWhere('id', $root->id);

        // 0 (root) + 2 (child) + 1 (grandchild) = 3
        $this->assertSame(3, (int) $rootRow->total_products);
    }

    public function test_a_root_with_no_products_still_appears_with_zero_counts(): void
    {
        $root = Category::factory()->create(['parent_id' => null]);

        $rows = app(AnalyticsRepository::class)->categoryPerformanceTree();
        $rootRow = $rows->firstWhere('id', $root->id);

        $this->assertNotNull($rootRow);
        $this->assertSame(0, (int) $rootRow->total_products);
        $this->assertSame(0.0, (float) $rootRow->total_revenue);
    }

    public function test_it_sums_delivered_revenue_across_the_subtree(): void
    {
        $root = Category::factory()->create(['parent_id' => null]);
        $child = Category::factory()->create(['parent_id' => $root->id]);

        [$product] = $this->attachActiveProducts($child, 1);
        $variant = $product->variants()->firstOrFail();

        $order = Order::factory()->create(['status' => OrderStatus::Delivered, 'total' => 250]);
        OrderItem::factory()->create([
            'order_id' => $order->id,
            'variant_id' => $variant->id,
            'quantity' => 1,
            'unit_price' => 250,
            'line_total' => 250,
        ]);

        $rows = app(AnalyticsRepository::class)->categoryPerformanceTree();
        $rootRow = $rows->firstWhere('id', $root->id);

        $this->assertSame(250.0, (float) $rootRow->total_revenue);
    }

    public function test_a_cycle_in_the_tree_does_not_cause_a_runaway(): void
    {
        $a = Category::factory()->create(['parent_id' => null]);
        $b = Category::factory()->create(['parent_id' => $a->id]);

        // Force a cycle: a's parent becomes b — data corruption the query must survive.
        DB::table('categories')->where('id', $a->id)->update(['parent_id' => $b->id]);

        $rows = app(AnalyticsRepository::class)->categoryPerformanceTree();

        // Both nodes are now non-root, so neither is returned as a root row. The
        // guarantee under test is that the recursion terminated and returned a
        // result instead of hanging on the cycle.
        $this->assertInstanceOf(Collection::class, $rows);
    }

    /**
     * @return array<int, Product>
     */
    private function attachActiveProducts(Category $category, int $count): array
    {
        $products = [];

        for ($i = 0; $i < $count; $i++) {
            $product = Product::factory()->active()->create();
            $product->categories()->attach($category->id);
            $products[] = $product;
        }

        return $products;
    }
}
