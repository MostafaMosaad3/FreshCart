<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\Vendor;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {

        $name = $this->faker->words(3, true);

        return [
            'vendor_id' => Vendor::factory() ,
            'name' => $name,
            'slug' => Str::slug($name) . '-' . Str::random(3),
            'description' => $this->faker->paragraph(),
            'price'=> $this->faker->randomFloat(2, 1, 9999),
            'status'=> 'draft' ,
            'is_featured' => false,
        ];
    }


    public function featured() :static
    {
        return $this->state(fn() => ['is_featured' => true]);
    }


    public function active() :static
    {
        return $this->state(fn() => ['status' => 'active']);
    }

    public function configure()
    {
        return $this->afterCreating(function (Product $product) {
            $product->variants()->create([
                'sku'        => "SKU-{$product->id}-DEFAULT",
                'name'       => 'Default',
                'attributes' => [],
                'price'      => $product->price,
                'stock'      => fake()->numberBetween(0, 50),
                'version'    => 0,
            ]);
        });
    }
}
