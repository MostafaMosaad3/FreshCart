<?php

namespace Tests\Feature\Events;

use App\Events\Orders\OrderCancelled;
use App\Events\Orders\OrderPaid;
use App\Events\Orders\OrderPlaced;
use App\Exceptions\IllegalTransitionException;
use App\Listeners\Orders\IncrementOrderMetrics;
use App\Listeners\Orders\LogStateTransition;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\ProductVariant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class OrderEventsTest extends TestCase
{
    use RefreshDatabase;

    public function test_dispatches_order_paid_when_pending_order_marked_paid(): void
    {
        Event::fake([OrderPaid::class]);

        $order = Order::factory()->pending()->create();
        $order->markAsPaid();

        Event::assertDispatched(
            OrderPaid::class,
            fn ($e) => $e->order->is($order)
        );
    }

    public function test_does_not_dispatch_order_paid_on_illegal_transition(): void
    {
        Event::fake([OrderPaid::class]);

        $order = Order::factory()->delivered()->create();

        $this->expectException(IllegalTransitionException::class);

        try {
            $order->markAsPaid();
        } finally {
            Event::assertNotDispatched(OrderPaid::class);
        }
    }

    public function test_holds_events_until_transaction_commits(): void
    {
        Event::fake([OrderCancelled::class]);

        $order = Order::factory()->pending()->create();
        OrderItem::factory()->for($order)->create([
            'variant_id' => ProductVariant::factory()->create(['stock' => 3])->id,
            'quantity' => 2,
        ]);

        try {
            DB::transaction(function () use ($order) {
                $order->cancel('test');   // fires OrderCancelled
                throw new \RuntimeException('Simulated failure after dispatch');
            });
        } catch (\RuntimeException) {
            // expected
        }

        // ShouldDispatchAfterCommit must suppress the event on rollback
        Event::assertNotDispatched(OrderCancelled::class);
    }

    public function test_increment_order_metrics_bumps_daily_counter(): void
    {
        Cache::flush();

        $order = Order::factory()->paid()->create();

        (new IncrementOrderMetrics)->handle(new OrderPlaced($order));

        $key = 'analytics.daily_orders.'.now()->format('Y-m-d');
        $this->assertSame(1, (int) Cache::get($key));
    }

    public function test_log_state_transition_writes_an_order_events_row(): void
    {
        $order = Order::factory()->pending()->create();

        (new LogStateTransition)->handle(new OrderPaid($order));

        $this->assertSame(
            1,
            DB::table('order_events')->where('order_id', $order->id)->count()
        );
    }
}
