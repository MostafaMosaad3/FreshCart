<?php

namespace App\Listeners\Orders;

use Illuminate\Support\Facades\DB;

class LogStateTransition
{
    public function handle($event): void
    {
        DB::table('order_events')->insert([
            'order_id' => $event->order->id,
            'event_type' => class_basename($event),
            'metadata' => json_encode([
                'status_after' => $event->order->status->value,
                'reason' => $event->reason ?? null,
                'tracking' => $event->trackingNumber ?? null,
            ]),
            'actor_id' => auth()->id(),
            'created_at' => now(),
        ]);
    }
}
