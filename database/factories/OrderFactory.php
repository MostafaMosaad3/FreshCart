<?php

namespace Database\Factories;

use App\Enums\OrderStatus;
use App\Models\Address;
use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Order>
 */
class OrderFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $subtotal = $this->faker->randomFloat(2, 20, 1000);
        $tax = round($subtotal * 0.14, 2);
        $shipping = $this->faker->randomFloat(2, 0, 50);
        $discount = 0;
        $total = round($subtotal + $tax + $shipping - $discount, 2);

        return [
            'user_id' => User::factory(),
            'order_number' => 'ORD-'.strtoupper(Str::random(10)),
            'idempotency_key' => (string) Str::uuid(),
            'shipping_address_id' => Address::factory(),
            'status' => OrderStatus::Pending,
            'subtotal' => $subtotal,
            'tax' => $tax,
            'shipping' => $shipping,
            'discount' => $discount,
            'total' => $total,
            'transaction_id' => null,
            'gateway' => null,
            'paid_at' => null,
            'shipped_at' => null,
            'delivered_at' => null,
            'cancelled_at' => null,
            'tracking_number' => null,
            'cancellation_reason' => null,
            'placed_at' => now(),
        ];
    }

    public function pending(): static
    {
        return $this->state(['status' => OrderStatus::Pending]);
    }

    public function paid(): static
    {
        return $this->state(['status' => OrderStatus::Paid, 'paid_at' => now()]);
    }

    public function shipped(): static
    {
        return $this->state(['status' => OrderStatus::Shipped, 'paid_at' => now(), 'shipped_at' => now()]);
    }

    public function delivered(): static
    {
        return $this->state(['status' => OrderStatus::Delivered, 'paid_at' => now(), 'shipped_at' => now()->subDay(), 'delivered_at' => now()]);
    }
}
