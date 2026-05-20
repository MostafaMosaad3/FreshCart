<?php

use App\Providers\AppServiceProvider;
use App\Providers\PaymentServiceProvider;
use App\Providers\PricingServiceProvider;

return [
    AppServiceProvider::class,
    PricingServiceProvider::class,
    PaymentServiceProvider::class,
];
