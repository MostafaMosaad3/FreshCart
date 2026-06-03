<?php

namespace Tests\Unit;

use App\Enums\OrderStatus;
use App\Exceptions\IllegalTransitionException;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\ProductVariant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StateTransitionTest extends TestCase
{
    use RefreshDatabase;

    public function test_pending_can_move_to_paid(): void
    {
        $order = Order::factory()->pending()->create();
        $order->markAsPaid();

        $this->assertSame(OrderStatus::Paid, $order->fresh()->status);
    }

    public function test_pendoing_cannot_skip_to_delivered(): void
    {
        $order = Order::factory()->pending()->create();

        $this->expectException(IllegalTransitionException::class);

        $order->markAsDelivered();
    }

    public function test_cancelling_a_pending_order_restores_stock(): void
    {
        $variant = ProductVariant::factory()->create(['stock' => 5]);
        $order = Order::factory()->pending()->create();
        OrderItem::factory()->for($order)->create([
            'variant_id' => $variant->id,
            'quantity' => 2,
            'unit_price' => $variant->price,
        ]);

        $variant->decrement('stock', 2);
        $this->assertSame(3, $variant->fresh()->stock);

        $order->cancel('customer requested');

        $this->assertSame(5, $variant->fresh()->stock);
        $this->assertSame(OrderStatus::Cancelled, $order->fresh()->status);
    }

    public function test_delivered_orders_cannot_be_cancelled(): void
    {
        $order = Order::factory()->delivered()->create();

        $this->expectException(IllegalTransitionException::class);

        $order->cancel();
    }

    public function test_follows_the_full_happy_path_lifecycle(): void
    {
        $order = Order::factory()->pending()->create();

        $order->markAsPaid();
        $this->assertSame(OrderStatus::Paid, $order->fresh()->status);

        $order->markAsShipped('TRACK-123');
        $this->assertSame(OrderStatus::Shipped, $order->fresh()->status);
        $this->assertSame('TRACK-123', $order->fresh()->tracking_number);

        $order->markAsDelivered();
        $this->assertSame(OrderStatus::Delivered, $order->fresh()->status);
    }
}
