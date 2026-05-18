<?php

namespace App\Services;

use App\Contracts\PaymentGatewayInterface;

class FakePaymentGateway implements PaymentGatewayInterface
{
    private static bool $shouldSucceed = true;

    public static function shouldSucceed(): void
    {
        self::$shouldSucceed = true;
    }

    public static function shouldFail(): void
    {
        self::$shouldSucceed = false;
    }

    public function charge(int $amountInCents, string $currency = 'EGP'): array
    {
        if (! self::$shouldSucceed) {
            return ['success' => false, 'reason' => 'Fake gateway forced failure'];
        }

        return [
            'success'        => true,
            'transaction_id' => 'FAKE-' . uniqid(),
        ];
    }

    public function refund(string $transactionId, int $amountInCents): array
    {
        if (! self::$shouldSucceed) {
            return ['success' => false, 'reason' => 'Fake gateway forced refund failure'];
        }

        return [
            'success'   => true,
            'refund_id' => 'FAKE-R-' . uniqid(),
        ];
    }
}
