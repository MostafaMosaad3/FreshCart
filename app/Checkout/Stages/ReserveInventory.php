<?php

namespace App\Checkout\Stages;

use App\Checkout\CheckoutContext;
use App\Exceptions\OutOfStockException;
use App\Models\ProductVariant;

class ReserveInventory
{
    public function handle(CheckoutContext $context , \Closure $next)
    {
        $variantIds = $context->cart->items->pluck('variant_id')
            ->values()->all() ;

        $context->lockedVariants = ProductVariant::whereIn('id' , $variantIds)
            ->orderBy('id')
            ->lockForUpdate()
            ->get()
            ->keyBy('id') ;


        foreach($context->cart->items as $item){
            $variant = $context->lockedVariants[$item->variant_id] ?? null;
            if(!$variant){
                throw new OutOfStockException("Variant #{$item->variant_id} is out of stock.");
            }
        }

        return $next($context);
    }
}
