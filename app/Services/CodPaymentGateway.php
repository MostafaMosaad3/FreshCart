<?php

namespace App\Services;

use App\Contracts\PaymentGatewayInterface;
use Illuminate\Support\Facades\Log;

class CodPaymentGateway implements PaymentGatewayInterface
{

    public function charge(int $amountInCents, string $currency = 'EGY'): array
    {
        Log::info("[COD] Order placed, payment on delivery — {$amountInCents} {$currency}");

        return [
            'success'        => true,
            'transaction_id' => 'COD-' . uniqid(),
        ];
    }

    public function refund(string $transactionId, int $amountInCents): array
    {
        Log::info("[COD] Cannot refund cash payment: {$transactionId}");

        return [
            'success' => false,
            'reason'  => 'COD payments cannot be refunded digitally.',
        ];
    }
}
