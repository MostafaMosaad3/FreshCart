<?php

namespace App\Providers;

use App\Contracts\PaymentGatewayInterface;
use App\Services\CodPaymentGateway;
use App\Services\StripePaymentGateway;
use Illuminate\Support\ServiceProvider;

class PaymentServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(PaymentGatewayInterface::class, function () {
            return match (config('payment.gateway')) {
                'stripe' => new StripePaymentGateway(
                    config('services.stripe.secret', 'sk_test_fake'),
                ) ,
                'cod' => new CodPaymentGateway ,
                default => throw new \InvalidArgumentException('Unknown payment gateway'.config('payment.gateway')),
            };
        });
    }
}
