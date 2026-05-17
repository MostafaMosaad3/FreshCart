<?php

namespace App\Checkout\Stages;

use App\Checkout\CheckoutContext;

class ClearCart
{
    public function handle(CheckoutContext $context , \Closure $next){
        $context->cart->items()->delete();

        return $next($context);
    }
}
