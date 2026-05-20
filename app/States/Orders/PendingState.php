<?php

namespace App\States\Orders;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\ProductVariant;
use Illuminate\Support\Facades\DB;

class PendingState extends OrderStateBase
{
    public function markAsPaid(Order $order): void
    {
        $order->status = OrderStatus::Paid;
        $order->paid_at = $order->paid_at ?? now();
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
