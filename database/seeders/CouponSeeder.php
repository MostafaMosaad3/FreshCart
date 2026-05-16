<?php

namespace Database\Seeders;

use App\Models\Coupon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CouponSeeder extends Seeder
{
    public function run(): void
    {
        Coupon::updateOrCreate(
            ['code' => 'WELCOME10'],
            ['type' => 'percent', 'value' => 10],
        );

        Coupon::updateOrCreate(
            ['code' => 'FLAT50'],
            ['type' => 'fixed', 'value' => 50],
        );
    }
}
