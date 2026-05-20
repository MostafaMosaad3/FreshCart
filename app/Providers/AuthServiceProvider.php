<?php

namespace App\Providers;

use App\Models\Order;
use App\Policies\OrderPolicy;

class AuthServiceProvider
{
    protected $policies = [
        Order::class => OrderPolicy::class,
    ];
}
