<?php

namespace App\Listeners\Orders;

use App\Events\Orders\OrderPlaced;
use App\Mail\Order\OrderPlacedMail;
use App\Models\Order;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendOrderPlacedEmail implements ShouldQueue
{
    use InteractsWithQueue;

    public int $tries = 3;

    public function backoff(): array
    {
        return [10, 60, 300];
    }

    public function handle(OrderPlaced $event)
    {
        $logged = DB::table('email_logs')->insertOrIgnore([
            'event_class' => OrderPlaced::class,
            'subject_type' => Order::class,
            'subject_id' => $event->order->id,
            'listener' => static::class,
            'sent_at' => now(),
        ]);

        if ($logged === 0) {
            return;
        }

        Mail::to($event->order->user)->send(new OrderPlacedMail($event->order));
    }

    public function failed(OrderPlaced $event, \Throwable $e): void
    {
        Log::critical('SendOrderPlacedEmail failed permanently', [
            'order_id' => $event->order->id,
            'error' => $e->getMessage(),
        ]);
    }
}
