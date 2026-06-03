<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\Vendor;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Vendor>
 */
class VendorFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $storeName = $this->faker->company();

        return [
            'user_id' => User::factory(),
            'store_name' => $storeName,
            'slug' => Str::slug($storeName).'-'.Str::random(4),
            'description' => $this->faker->sentence(),
            'phone' => $this->faker->phoneNumber(),
            'is_verified' => false,

        ];

    }

    public function verified(): static
    {
        return $this->state(fn () => ['is_verified' => true]);
    }

    public function unverified(): static
    {
        return $this->state(fn () => ['is_verified' => false]);
    }
}
