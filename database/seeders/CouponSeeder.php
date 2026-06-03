<?php

namespace Database\Seeders;

use App\Models\Coupon;
use Illuminate\Database\Seeder;

class CouponSeeder extends Seeder
{
    public function run(): void
    {
        Coupon::updateOrCreate(
            ['code' => 'WELCOME10'],
            ['strategy' => 'percentage', 'config' => ['value' => 10]],
        );

        Coupon::updateOrCreate(
            ['code' => 'FLAT50'],
            ['strategy' => 'fixed_amount', 'config' => ['value' => 50]],
        );
    }
}
