<?php

namespace App\Checkout\Stages;

use App\Checkout\CheckoutContext;
use App\Models\Order;
use App\Models\OrderItem;
use http\Exception\RuntimeException;

class CreateOrder
{
    public function handle(CheckoutContext $context , \Closure $next)
    {
        $context->order = Order::create([
            'user_id'             => $context->user->id,
            'order_number'        => $this->generateNumber(),
            'status'              => 'pending',
            'idempotency_key'     => $context->idempotencyKey,
            'subtotal'            => $context->priceContext->subtotal,
            'tax'                 => $context->priceContext->tax,
            'shipping'            => $context->priceContext->shipping,
            'discount'            => $context->priceContext->discount,
            'total'               => $context->priceContext->total(),
            'shipping_address_id' => $context->user->defaultAddress?->id,
            'transaction_id'      => $context->paymentResult['transaction_id'] ?? null,
            'gateway'             => config('payment.gateway')  ,
            'paid_at'             => now() ,
            'placed_at'           => now(),
        ]) ;


        foreach ($context->cart->items as $item) {
            OrderItem::create([
                'order_id'   => $context->order->id,
                'variant_id' => $item->variant_id,
                'quantity'   => $item->quantity,
                'unit_price' => $item->unit_price,
                'line_total' => $item->quantity * $item->unit_price,
            ]);
        }

        return $next($context);
    }

    private function generateNumber(): string
    {
        for ($i = 0; $i < 3; $i++) {
            $number = 'ORD-' . now()->year . '-' . str_pad(
                    (string) random_int(10000, 99999), 5, '0', STR_PAD_LEFT
                );
            if (!Order::where('order_number', $number)->exists()) {
                return $number;
            }
        }
        throw new RuntimeException('Could not generate unique order number.');
    }






}


