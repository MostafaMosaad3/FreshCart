<?php

namespace App\Checkout\Stages;

use App\Checkout\CheckoutContext;
use Illuminate\Validation\ValidationException;

class ValidateCart
{
    public function handle(CheckoutContext $context , \Closure $next)
    {
        $context->cart = $context->user->cart()->with('items.variant')
            ->firstOrFail() ;

        if($context->cart->items->isEmpty())
        {
            throw ValidationException::withMessages([
                'cart' => 'The cart is empty.',
            ]) ;
        }

        return $next($context);

    }
}
