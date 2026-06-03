<?php

namespace App\Pricing\Discounts;

use App\Exceptions\InvalidDiscountConfigException;
use App\Pricing\PriceContext;

class BuyXGetYDiscount extends AbstractDiscount
{
    public function calculate(PriceContext $context): float
    {
        $productIds = $this->config['product_ids'] ?? [];
        $buy = (int) ($this->config['buy'] ?? 0);
        $get = (int) ($this->config['get'] ?? 0);

        if (empty($productIds) || $buy <= 0 || $get <= 0) {
            throw new InvalidDiscountConfigException('buy_x_get_y needs product_ids, buy, get');
        }

        // Expand each cart item by quantity into a flat list of unit prices
        $eligibleUnits = collect($context->cart->items)
            ->filter(fn ($item) => in_array($item->product_id, $productIds, true))
            ->flatMap(fn ($item) => array_fill(0, $item->quantity, (float) $item->unit_price))
            ->sortDesc()
            ->values();

        $groupSize = $buy + $get;
        if ($eligibleUnits->count() < $groupSize) {
            return 0.0;
        }

        // In each (buy+get) group, the cheapest `get` units are free
        $freeUnits = collect();
        foreach ($eligibleUnits->chunk($groupSize) as $group) {
            if ($group->count() < $groupSize) {
                break;
            }   // incomplete group doesn't qualify
            $freeUnits = $freeUnits->concat($group->sortDesc()->values()->slice($buy, $get));
        }

        return $this->cap($freeUnits->sum(), $context->subtotal);
    }

    public function describe(): string
    {
        return "Buy {$this->config['buy']} get {$this->config['get']} free on eligible items";
    }
}
