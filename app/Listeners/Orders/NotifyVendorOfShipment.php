<?php

namespace App\Listeners\Orders;

use App\Events\Orders\OrderShipped;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class NotifyVendorOfShipment implements ShouldQueue
{
    use InteractsWithQueue;

    public function handle(OrderShipped $event): void
    {
        Log::info("TODO (Day 4): notify vendors of shipped order {$event->order->order_number}");
    }
}
