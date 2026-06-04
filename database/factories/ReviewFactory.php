<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\Review;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Review>
 */
class ReviewFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        // reviews is polymorphic: the parent is set via forProduct()/forVendor().
        return [
            'user_id' => User::factory(),
            'rating' => $this->faker->numberBetween(1, 5),
            'comment' => $this->faker->optional(0.7)->sentence(),
        ];
    }

    public function forProduct(Product $product): static
    {
        return $this->state([
            'reviewable_type' => 'product',
            'reviewable_id' => $product->id,
        ]);
    }

    public function forVendor(Vendor $vendor): static
    {
        return $this->state([
            'reviewable_type' => 'vendor',
            'reviewable_id' => $vendor->id,
        ]);
    }
}
