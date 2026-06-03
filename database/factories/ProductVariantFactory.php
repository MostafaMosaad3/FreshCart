<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<ProductVariant>
 */
class ProductVariantFactory extends Factory
{
    public function definition(): array
    {
        return [
            'product_id' => Product::factory(),
            'sku' => 'SKU-'.Str::upper(Str::random(8)),
            'name' => $this->faker->word(),
            'price' => $this->faker->randomFloat(2, 1, 9999),
            'attributes' => [],
            'stock' => $this->faker->numberBetween(0, 100),
            'version' => 0,
        ];
    }
}
