<?php

namespace App\Exceptions;

use Exception;

class OutOfStockException extends Exception
{
    public function __construct(string $variantName = 'item')
    {
        parent::__construct("Not enough stock for {$variantName}.");
    }

    public function render()
    {
        return response()->json(['message' => $this->getMessage()], 409);
    }
}
