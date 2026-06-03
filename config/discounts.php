<?php

use App\Pricing\Discounts\BuyXGetYDiscount;
use App\Pricing\Discounts\FixedAmountDiscount;
use App\Pricing\Discounts\PercentageDiscount;

return [
    'strategies' => [
        'percentage' => PercentageDiscount::class,
        'fixed_amount' => FixedAmountDiscount::class,
        'buy_x_get_y' => BuyXGetYDiscount::class,
    ],
];
