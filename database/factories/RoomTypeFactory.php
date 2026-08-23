<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Domain\Kost\Models\Kost;
use App\Domain\Kost\Models\RoomType;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<RoomType>
 */
class RoomTypeFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<RoomType>
     */
    protected $model = RoomType::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->unique()->randomElement(['Single Bed', 'Double Bed', 'Suite', 'Standard', 'Deluxe', 'VIP']);

        return [
            'kost_id' => Kost::factory(),
            'name' => $name,
            'slug' => Str::slug($name),
        ];
    }
}
