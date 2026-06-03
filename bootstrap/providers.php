<?php

use App\Providers\AppServiceProvider;
use App\Providers\DiscountServiceProvider;
use App\Providers\EventServiceProvider;
use App\Providers\PaymentServiceProvider;
use App\Providers\PricingServiceProvider;

return [
    AppServiceProvider::class,
    DiscountServiceProvider::class,
    EventServiceProvider::class,
    PaymentServiceProvider::class,
    PricingServiceProvider::class,
];
