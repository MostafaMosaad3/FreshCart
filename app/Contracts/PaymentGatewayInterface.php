<?php

namespace App\Contracts;

interface PaymentGatewayInterface
{
    public function charge(int $amountInCents, string $currency = 'EGY'): array;

    public function refund(string $transactionId, int $amountInCents): array;
}
