<?php

namespace Database\Factories;

use App\Models\Package;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Package>
 */
class PackageFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'type' => Package::UMRAH,
            'name' => 'Umroh '.fake()->unique()->words(2, true),
            'departure_date' => today()->addDays(fake()->numberBetween(20, 200)),
            'duration_days' => 12,
            'hotel_makkah' => 'Hotel Makkah',
            'hotel_makkah_stars' => 3,
            'hotel_madinah' => 'Hotel Madinah',
            'hotel_madinah_stars' => 3,
            'airline' => 'Saudia',
            'route_departure' => 'Jakarta - Jeddah',
            'route_return' => 'Jeddah - Jakarta',
            'currency' => 'IDR',
            'price_quad' => 35_000_000,
            'price_triple' => 36_000_000,
            'price_double' => 38_000_000,
            'extra_costs' => [['label' => 'Perlengkapan dan asuransi', 'amount' => 1_750_000]],
            'highlights' => ['Umroh 3 kali'],
            'includes' => ['Tiket pesawat kelas ekonomi pulang-pergi', 'Visa umroh'],
            'excludes' => ['Paspor dan vaksin'],
            'published_at' => now()->subDay(),
        ];
    }

    public function draft(): static
    {
        return $this->state(fn (array $attributes) => ['published_at' => null]);
    }

    public function departed(): static
    {
        return $this->state(fn (array $attributes) => ['departure_date' => today()->subDays(10)]);
    }

    public function hajiKhusus(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => Package::HAJI_KHUSUS,
            'name' => 'Haji Khusus '.fake()->unique()->words(2, true),
            'departure_date' => null,
            'departure_note' => 'Mengikuti nomor porsi',
            'duration_days' => 25,
            'duration_approximate' => true,
            'currency' => 'USD',
            'price_quad' => 16_500,
            'price_triple' => 17_500,
            'price_double' => 19_500,
            'extra_costs' => [['label' => "Dam haji tamattu'", 'amount' => 200]],
        ]);
    }
}
