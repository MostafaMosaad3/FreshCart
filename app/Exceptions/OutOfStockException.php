<?php

namespace App\Exceptions;

class OutOfStockException extends CheckoutException
{
    public function __construct(string $variantName = 'item')
    {
        parent::__construct("Not enough stock for {$variantName}.");
    }

    public function statusCode(): int
    {
        return 422;
    }

    public function errorCode(): string
    {
        return 'out_of_stock';
    }
}
