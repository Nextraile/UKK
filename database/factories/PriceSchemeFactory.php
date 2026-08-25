<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Domain\Kost\Models\PriceScheme;
use App\Domain\Kost\Models\RoomType;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PriceScheme>
 */
class PriceSchemeFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<PriceScheme>
     */
    protected $model = PriceScheme::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $schemes = [
            ['name' => 'Harian', 'unit' => 'day', 'value' => 1, 'price' => fake()->randomElement([50000, 75000, 100000, 150000])],
            ['name' => 'Mingguan', 'unit' => 'week', 'value' => 1, 'price' => fake()->randomElement([300000, 400000, 500000, 600000])],
            ['name' => 'Bulanan', 'unit' => 'month', 'value' => 1, 'price' => fake()->randomElement([1000000, 1500000, 2000000, 2500000])],
            ['name' => '3 Bulan', 'unit' => 'month', 'value' => 3, 'price' => fake()->randomElement([2800000, 4000000, 5500000, 7000000])],
            ['name' => '6 Bulan', 'unit' => 'month', 'value' => 6, 'price' => fake()->randomElement([5500000, 7500000, 10000000, 13000000])],
            ['name' => 'Tahunan', 'unit' => 'month', 'value' => 12, 'price' => fake()->randomElement([10000000, 14000000, 18000000, 24000000])],
        ];

        $scheme = fake()->randomElement($schemes);

        return [
            'room_type_id' => RoomType::factory(),
            'name' => $scheme['name'],
            'description' => fake()->optional(0.3)->sentence(),
            'price' => $scheme['price'],
            'duration_value' => $scheme['value'],
            'duration_unit' => $scheme['unit'],
            'is_active' => true,
        ];
    }

    /**
     * Mark the price scheme as inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }
}
