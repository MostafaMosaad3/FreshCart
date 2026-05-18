<?php

namespace App\Services;

use App\Contracts\PaymentGatewayInterface;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class StripePaymentGateway implements PaymentGatewayInterface
{
    public function __construct(private string $apiKey) {}

    public function charge(int $amountInCents, string $currency = 'EGY'): array
    {
        Log::info("[Stripe] charge {$amountInCents}  {$currency}");

        return [
            'success' => true,
            'transaction_id' => 'stripe_' . Str::random(16),
        ] ;
    }

    public function refund(string $transactionId, int $amountInCents, string $currency = 'EGY'): array
    {
         Log::info("[Stripe] Refunded {$amountInCents} For {$currency}");

         return [
             'success' => true,
             'transaction_id' => 'refund_' . Str::random(16),
         ];
    }
}
