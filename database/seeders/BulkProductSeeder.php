<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Models\Vendor;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BulkProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (! app()->environment('local', 'testing')) {
            return;
        }

        DB::transaction(function () {
            $categoryIds = Category::pluck('id')->all();

            Vendor::factory()->count(20)->create()->each(function (Vendor $vendor) use ($categoryIds) {
                $products = Product::factory()
                    ->count(250)
                    ->for($vendor)
                    ->sequence(
                        ['status' => 'active'],
                        ['status' => 'active'],
                        ['status' => 'active'],
                        ['status' => 'active'],
                        ['status' => 'active'],
                        ['status' => 'active'],
                        ['status' => 'active'],
                        ['status' => 'draft'],
                        ['status' => 'draft'],
                        ['status' => 'inactive'],
                    )
                    ->create();

                $products->each(function (Product $p) use ($categoryIds) {
                    $p->categories()->attach($categoryIds[array_rand($categoryIds)]);
                });
            });
        });
    }
}
