<?php

namespace App\Exceptions;

use RuntimeException;

class UnknownDiscountStrategyException extends RuntimeException
{
    public function __construct(string $strategy)
    {
        parent::__construct(
            "No discount strategy registered for '{$strategy}'. Add it to config/discounts.php."
        );
    }
}
