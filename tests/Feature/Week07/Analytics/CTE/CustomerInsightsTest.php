<?php

namespace Tests\Feature\Week07\Analytics\CTE;

use App\Enums\OrderStatus;
use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use App\Repositories\AnalyticsRepository;
use Illuminate\Support\Carbon;
use Tests\Feature\Week07\Analytics\AnalyticsTestCase;

class CustomerInsightsTest extends AnalyticsTestCase
{
    public function test_it_summarises_orders_lifetime_value_and_tenure(): void
    {
        $user = User::factory()->create();

        $this->order($user, 100, now()->subDays(30), OrderStatus::Delivered);
        $this->order($user, 200, now()->subDays(10), OrderStatus::Delivered);
        $this->order($user, 300, now()->subDays(5), OrderStatus::Delivered);

        $insights = app(AnalyticsRepository::class)->customerInsights($user->id);

        $this->assertSame(3, (int) $insights->total_orders);
        $this->assertSame(600.0, (float) $insights->lifetime_value);
        $this->assertSame(200.0, (float) $insights->avg_order_value);
        $this->assertSame(30, (int) $insights->days_as_customer);   // oldest order, 30 days ago
    }

    public function test_it_counts_only_delivered_orders(): void
    {
        $user = User::factory()->create();

        $this->order($user, 100, now()->subDays(3), OrderStatus::Delivered);
        $this->order($user, 999, now()->subDays(2), OrderStatus::Cancelled);
        $this->order($user, 999, now()->subDays(1), OrderStatus::Pending);

        $insights = app(AnalyticsRepository::class)->customerInsights($user->id);

        $this->assertSame(1, (int) $insights->total_orders);
        $this->assertSame(100.0, (float) $insights->lifetime_value);
    }

    public function test_it_ranks_favorite_categories_by_spend(): void
    {
        $user = User::factory()->create();

        $electronics = Category::factory()->create(['name' => 'Electronics']);
        $books = Category::factory()->create(['name' => 'Books']);

        // Electronics spend 300 > Books spend 100 -> Electronics first.
        $this->spendInCategory($user, $electronics, 300);
        $this->spendInCategory($user, $books, 100);

        $insights = app(AnalyticsRepository::class)->customerInsights($user->id);

        $this->assertSame('Electronics, Books', $insights->favorite_categories);
    }

    public function test_a_customer_with_no_orders_returns_null(): void
    {
        $user = User::factory()->create();

        $insights = app(AnalyticsRepository::class)->customerInsights($user->id);

        $this->assertNull($insights);
    }

    private function order(
        User $user,
        float $total,
        Carbon $date,
        OrderStatus $status = OrderStatus::Delivered,
    ): Order {
        return Order::factory()->for($user)->create([
            'status' => $status,
            'total' => $total,
            'created_at' => $date,
        ]);
    }

    private function spendInCategory(User $user, Category $category, float $amount): void
    {
        $product = Product::factory()->active()->create();
        $product->categories()->attach($category->id);
        $variant = $product->variants()->firstOrFail();

        $order = $this->order($user, $amount, now()->subDays(5), OrderStatus::Delivered);

        OrderItem::factory()->create([
            'order_id' => $order->id,
            'variant_id' => $variant->id,
            'quantity' => 1,
            'unit_price' => $amount,
            'line_total' => $amount,
        ]);
    }
}
