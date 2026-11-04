<?php

namespace Database\Factories;

use App\Models\Representative;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Representative>
 */
class RepresentativeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'region' => fake()->randomElement(['DKI Jakarta', 'Jawa Timur', 'Bali']),
            'name' => fake()->name(),
            'address' => fake()->address(),
            'is_active' => true,
        ];
    }
}
