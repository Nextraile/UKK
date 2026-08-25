<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Domain\Kost\Models\Kost;
use App\Domain\Kost\Models\Room;
use App\Domain\Kost\Models\RoomType;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Room>
 */
class RoomFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<Room>
     */
    protected $model = Room::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'kost_id' => Kost::factory(),
            'room_type_id' => RoomType::factory(),
            'code' => 'R-'.fake()->unique()->numberBetween(100, 999),
            'status' => 'available',
            'internal_notes' => fake()->optional(0.3)->sentence(),
        ];
    }

    /**
     * Mark the room as unavailable.
     */
    public function unavailable(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'unavailable',
        ]);
    }
}
