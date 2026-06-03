<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Cart\ApplyCouponRequest;
use App\Models\Cart;
use App\Models\Coupon;
use App\Pricing\Discounts\DiscountStrategyFactory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CartCouponController extends Controller
{
    public function __construct(private DiscountStrategyFactory $factory) {}

    public function apply(ApplyCouponRequest $request): JsonResponse
    {
        $coupon = Coupon::availableFor($request->validated('code'))->first();

        if (! $coupon) {
            return response()->json(['error' => 'Invalid coupon'], 422);
        }

        $cart = Cart::firstOrCreate(['user_id' => $request->user()->id]);

        $result = $coupon->isValidForCart($request->user(), $cart);
        if (! $result->ok) {
            return response()->json(['error' => $result->reason], 422);
        }

        // Single-coupon policy: this replaces any coupon already on the cart.
        $cart->update(['coupon_id' => $coupon->id]);

        return response()->json([
            'success' => true,
            'description' => $this->factory->make($coupon)->describe(),
        ]);
    }

    public function remove(Request $request): JsonResponse
    {
        $cart = Cart::firstOrCreate(['user_id' => $request->user()->id]);

        $cart->update(['coupon_id' => null]);

        return response()->json(null, 204);
    }
}
