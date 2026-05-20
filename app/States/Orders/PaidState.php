<?php

namespace App\States\Orders;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\ProductVariant;
use Illuminate\Support\Facades\DB;

class PaidState extends OrderStateBase
{
    public function markAsShipped(Order $order, ?string $trackingNumber = null): void
    {
        $order->status = OrderStatus::Shipped;
        $order->tracking_number = $trackingNumber;
        $order->shipped_at = now();
        $order->save();
    }

    public function cancel(Order $order, ?string $reason = null): void
    {
        DB::transaction(function () use ($order, $reason) {
            $variantIds = $order->items->pluck('variant_id')->sort()->values()->all();
            ProductVariant::whereIn('variant_id', $variantIds)->orderBy('variant_id')
                ->lockForUpdate()
                ->get();

            foreach ($order->items as $item) {
                $item->variant()->increment('stock', $item->quantity);
            }

            $order->status = OrderStatus::Cancelled;
            $order->cancelled_at = now();
            $order->cancellation_reason = $reason;
            $order->save();
        });

    }
}
