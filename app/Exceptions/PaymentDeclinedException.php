<?php

namespace App\Exceptions;

class PaymentDeclinedException extends CheckoutException
{
    public function __construct(string $message = 'Payment was declined.')
    {
        parent::__construct($message);
    }

    public function statusCode(): int
    {
        return 402;
    }

    public function errorCode(): string
    {
        return 'payment_declined';
    }
}
