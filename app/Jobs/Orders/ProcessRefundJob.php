<?php

namespace App\Jobs\Orders;

use App\Contracts\PaymentGatewayInterface;
use App\Models\Order;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class ProcessRefundJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public function backoff(): array
    {
        return [30, 120, 300];
    }

    /**
     * Create a new job instance.
     */
    public function __construct(public Order $order)
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(PaymentGatewayInterface $gateway): void
    {
        if (! $this->order->transaction_id) {
            Log::warning('ProcessRefundJob: order has no transaction_id; skipping', [
                'order_id' => $this->order->id,
            ]);

            return;
        }

        if ($this->order->refunded_at !== null) {
            return;
        }

        $amountCents = (int) round($this->order->total * 100);

        $result = $gateway->refund($this->order->transaction_id, $amountCents);

        if (! $result['success']) {
            throw new \RuntimeException(
                'Refund failed: '.($result['reason'] ?? 'unknown')
            );
        }

        $this->order->update([
            'refunded_at' => now(),
            'refund_id' => $result['refund_id'] ?? null,
        ]);
    }

    public function failed(\Throwable $e): void
    {
        Log::critical('ProcessRefundJob failed permanently', [
            'order_id' => $this->order->id,
            'error' => $e->getMessage(),
        ]);

        // In production: notify finance team via Slack or email
    }
}
