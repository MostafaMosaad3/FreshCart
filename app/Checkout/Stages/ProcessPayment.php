<?php

namespace App\Checkout\Stages;

use App\Checkout\CheckoutContext;
use App\Contracts\PaymentGatewayInterface;
use App\Exceptions\PaymentDeclinedException;

class ProcessPayment
{
    public function __construct(private PaymentGatewayInterface $gateway){}


    public function handle(CheckoutContext $context , \Closure $next)
    {
        $amountInCents = (int) round($context->priceContext->total() * 100);

        $context->paymentResult = $this->gateway->charge($amountInCents , 'EGY');

        if($context->paymentResult['success']){
            throw new PaymentDeclinedException($context->paymentResult['reason'] ?? 'Payment Was Declined');
        }

        return $next($context);
    }
}
