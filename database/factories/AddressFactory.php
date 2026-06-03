<?php

namespace Database\Factories;

use App\Models\Address;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Address>
 */
class AddressFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'label' => $this->faker->randomElement(['Home', 'Work', 'Other']),
            'name' => $this->faker->name(),
            'phone' => $this->faker->phoneNumber(),
            'line1' => $this->faker->streetAddress(),
            'line2' => null,
            'city' => $this->faker->city(),
            'state' => $this->faker->state(),
            'country_code' => 'EG',
            'postal_code' => $this->faker->postcode(),
            'is_default' => false,
        ];
    }
}
