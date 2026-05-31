<?php

use App\Providers\AppServiceProvider;
use App\Providers\EventServiceProvider;
use App\Providers\PaymentServiceProvider;
use App\Providers\PricingServiceProvider;

return [
    AppServiceProvider::class,
    EventServiceProvider::class,
    PaymentServiceProvider::class,
    PricingServiceProvider::class,
];
