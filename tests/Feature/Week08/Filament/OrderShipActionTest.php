<?php

namespace Tests\Feature\Week08\Filament;

use App\Enums\OrderStatus;
use App\Filament\Resources\Orders\Pages\ListOrders;
use App\Models\Order;
use App\Models\User;
use Filament\Actions\Testing\TestAction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Livewire\Livewire;
use Tests\TestCase;

class OrderShipActionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs(User::factory()->admin()->create());
        Event::fake();
    }

    public function test_it_ships_a_paid_order_via_the_panel_action(): void
    {
        $order = Order::factory()->create(['status' => 'paid']);

        Livewire::test(ListOrders::class)
            ->callAction(TestAction::make('ship')->table($order), data: ['tracking_number' => 'TRK123']);

        $this->assertSame(OrderStatus::Shipped, $order->fresh()->status);
        $this->assertSame('TRK123', $order->fresh()->tracking_number);
    }

    public function test_it_hides_the_ship_action_for_non_paid_orders(): void
    {
        $order = Order::factory()->create(['status' => 'pending']);

        Livewire::test(ListOrders::class)
            ->assertActionHidden(TestAction::make('ship')->table($order));
    }
}
