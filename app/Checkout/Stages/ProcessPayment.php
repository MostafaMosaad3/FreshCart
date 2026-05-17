<?php

namespace App\Checkout\Stages;

use App\Checkout\CheckoutContext;
use App\Exceptions\PaymentDeclinedException;

class ProcessPayment
{
    public function handle(CheckoutContext $context , \Closure $next)
    {
        // Week 5 replaces this with a proper Strategy-based PaymentGateway call.
        // For today, simulate a successful charge.
        $context->paymentResult = [
            'success'        => true,
            'transaction_id' => 'TEST-' . uniqid(),
        ];

        if (!$context->paymentResult['success']) {
            throw new PaymentDeclinedException();
        }

        return $next($context);

    }
}
