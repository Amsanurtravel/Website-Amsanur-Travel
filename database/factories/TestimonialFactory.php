<?php

namespace Database\Factories;

use App\Models\Testimonial;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Testimonial>
 */
class TestimonialFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'city' => fake()->city(),
            'trip' => 'Umroh '.fake()->monthName().' 2025',
            'quote' => fake()->paragraph(),
            'is_published' => true,
        ];
    }
}
