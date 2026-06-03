<?php

namespace App\Exceptions;

use Throwable;

class IllegalTransitionException extends \RuntimeException
{
    public function __construct(
        string $message = 'Illegal order state transition.',
        int $code = 422,
        ?Throwable $previous = null,
    ) {
        parent::__construct($message, $code, $previous);
    }

    public function render()
    {
        return response()->json([
            'message' => $this->getMessage(),
            'error' => 'illegal_transition',
        ], 422);
    }
}
