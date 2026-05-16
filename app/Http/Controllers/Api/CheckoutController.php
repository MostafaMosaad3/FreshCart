<?php

namespace App\Http\Controllers\Api;

use App\Exceptions\OptimisticLockException;
use App\Exceptions\OutOfStockException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Checkout\PlaceOrderRequest;
use App\Http\Resources\OrderResource;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\ProductVariant;
use App\Pricing\BasePrice;
use App\Pricing\PriceCalculatorInterface;
use App\Pricing\PriceContext;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CheckoutController extends Controller
{

    public function __construct(
        private PriceCalculatorInterface $calculator
    ) {}


    public function place(PlaceOrderRequest $request)
    {
        $user = $request->user();

        $order = DB::transaction(function () use ($user, $request) {
            $cart = $user->cart()->with('items')->firstOrFail(); ;

            if($cart->items->isEmpty()){
                throw ValidationException::withMessages([
                    'cart' => 'Cart is empty'
                ]);
            }

            // deterministic lock order : sort by variant id
            $variantIds = $cart->items->pluck('variant_id')->sort()->values();

            // single query to lock all variants at once
            $variants = ProductVariant::whereIn('id' , $variantIds)
                ->orderBy('id')
                ->lockForUpdate()
                ->get()
                ->keyBy('id');


            //  Validate each cart item's quantity is still available
            foreach ($cart->items as $item) {
                $variant = $variants[$item->variant_id];

                if ($item->quantity > $variant->stock) {
                    throw new OutOfStockException($variant->name);
                }
                $variant->decrement('stock', $item->quantity);
            }


            // create the order + items
            $context = $this->calculator->calculate(new PriceContext(
                items:   $cart->items,
                address: $user->defaultAddress,
                coupon:  null,
            ));

            $order = Order::create([
                'user_id'      => $user->id,
                'order_number' => $this->generateOrderNumber(),
                'status'       => 'pending',
                'subtotal'     => $context->subtotal,
                'tax'          => $context->tax,
                'shipping'     => $context->shipping,
                'discount'     => $context->discount,
                'total'        => $context->total(),
                'shipping_address_id' => $user->defaultAddress?->id,
                'placed_at'    => now(),
            ]);

            foreach ($cart->items as $item) {
                OrderItem::create([
                    'order_id'   => $order->id,
                    'variant_id' => $item->variant_id,
                    'quantity'   => $item->quantity,
                    'unit_price' => $item->unit_price,
                    'line_total' => $item->quantity * $item->unit_price,
                ]);
            }

            // clear the cart
            $cart->items()->delete();

            return $order->fresh('items') ;

        } , 3 );

        return new OrderResource($order);
    }


    private function generateOrderNumber(): string
    {
        for ($i = 0; $i < 3; $i++) {
            $number = 'ORD-' . now()->year . '-' . str_pad(
                    (string) random_int(10000, 99999), 5, '0', STR_PAD_LEFT
                );

            if (!Order::where('order_number', $number)->exists()) {
                return $number;
            }
        }
        throw new \RuntimeException('Unable to generate unique order number.');
    }


    public function placeOptimistic(PlaceOrderRequest $request): OrderResource
    {
        $user = $request->user();
        $cart = $user->cart()->with('items.variant')->firstOrFail();

        if ($cart->items->isEmpty()) {
            throw ValidationException::withMessages(['cart' => ['Your cart is empty.']]);
        }

        $attempts    = 0;
        $maxAttempts = 3;

        while ($attempts < $maxAttempts) {
            try {
                $order = DB::transaction(function () use ($user, $cart) {
                    foreach ($cart->items as $item) {
                        $variant = $item->variant;
                        $variant->refresh();                      // latest stock + version

                        $newStock = $variant->stock - $item->quantity;
                        if ($newStock < 0) {
                            throw new OutOfStockException($variant->name);
                        }

                        $updated = ProductVariant::where('id', $variant->id)
                            ->where('version', $variant->version)
                            ->update([
                                'stock'   => $newStock,
                                'version' => $variant->version + 1,
                            ]);

                        if ($updated === 0) {
                            throw new OptimisticLockException();   // someone raced — retry whole tx
                        }
                    }

                    $subtotal = $cart->items->sum(fn($i) => $i->quantity * $i->unit_price);

                    $order = Order::create([
                        'user_id'      => $user->id,
                        'order_number' => $this->generateOrderNumber(),
                        'status'       => 'pending',
                        'subtotal'     => $subtotal,
                        'tax'          => 0,
                        'shipping'     => 0,
                        'discount'     => 0,
                        'total'        => $subtotal,
                        'placed_at'    => now(),
                    ]);

                    foreach ($cart->items as $item) {
                        OrderItem::create([
                            'order_id'   => $order->id,
                            'variant_id' => $item->variant_id,
                            'quantity'   => $item->quantity,
                            'unit_price' => $item->unit_price,
                            'line_total' => $item->quantity * $item->unit_price,
                        ]);
                    }

                    $cart->items()->delete();

                    return $order->fresh('items.variant');
                });

                return new OrderResource($order);
            } catch (OptimisticLockException $e) {
                $attempts++;
            }
        }

        throw new \RuntimeException('Too much contention; please try again.');
    }
}
