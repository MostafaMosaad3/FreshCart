<?php

namespace Database\Factories;

use App\Models\Coupon;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Coupon>
 */
class CouponFactory extends Factory
{
    protected $model = Coupon::class;

    public function definition(): array
    {
        return [
            'code' => strtoupper($this->faker->unique()->bothify('SAVE####')),
            'strategy' => 'percentage',
            'config' => ['value' => 10],
            'used_count' => 0,
            'is_active' => true,
        ];
    }
}
