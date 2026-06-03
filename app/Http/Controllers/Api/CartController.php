<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Cart\AddToCartRequest;
use App\Http\Resources\CartItemResource;
use App\Http\Resources\CartResource;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\ProductVariant;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CartController extends Controller
{
    public function show(): CartResource
    {
        $cart = Cart::firstOrCreate(['user_id' => auth()->user()->id]);

        return new CartResource($cart);
    }

    public function addItem(AddToCartRequest $request)
    {
        $data = $request->validated();

        $cartItem = DB::transaction(function () use ($data) {
            $variant = ProductVariant::findOrFail($data['variant_id']);

            if ($variant->stock < $data['quantity']) {
                throw ValidationException::withMessages([
                    'variant_id' => ['Not enough stock for this variant.'],
                ]);
            }

            $cart = Cart::firstOrCreate(['user_id' => auth()->id()]);

            $item = $cart->items()->where('variant_id', $variant->id)->first();

            if ($item) {
                $item->increment('quantity', (int) $data['quantity']);
                $item->update(['unit_price' => $variant->price]);
            } else {
                $item = $cart->items()->create([
                    'variant_id' => $variant->id,
                    'quantity' => (int) $data['quantity'],
                    'unit_price' => $variant->price,
                ]);
            }

            $variant->decrement('stock', $data['quantity']);

            return $item->fresh(['variant.product']);
        });

        return (new CartItemResource($cartItem))
            ->response()
            ->setStatusCode(201);
    }

    public function updateQuantity(int $itemId, AddToCartRequest $request): CartItemResource
    {
        $data = $request->validated();

        $item = DB::transaction(function () use ($itemId, $data) {
            $item = CartItem::with('variant', 'cart')->findOrFail($itemId);

            if ($item->cart->user_id !== auth()->id()) {
                abort(403);
            }

            $delta = $data['quantity'] - $item->quantity;

            if ($delta > 0 && $item->variant->stock < $delta) {
                throw ValidationException::withMessages([
                    'quantity' => ['Not enough stock to increase quantity.'],
                ]);
            }

            $item->update(['quantity' => $data['quantity']]);
            $item->variant->decrement('stock', $delta);

            return $item->fresh(['variant.product']);
        });

        return new CartItemResource($item);
    }

    public function removeItem(int $itemId): JsonResponse
    {
        DB::transaction(function () use ($itemId) {
            $item = CartItem::with('cart', 'variant')->findOrFail($itemId);

            if ($item->cart->user_id != auth()->user()->id) {
                abort(403);
            }

            $item->vairant->increment('stock', $item->quantity);
            $item->delete();
        });

        return response()->json(null, 204);
    }
}
