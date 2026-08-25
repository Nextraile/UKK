<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Domain\Kost\Models\RoomType;
use App\Domain\Kost\Models\RoomTypeImage;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<RoomTypeImage>
 */
class RoomTypeImageFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<RoomTypeImage>
     */
    protected $model = RoomTypeImage::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'room_type_id' => RoomType::factory(),
            'image_path' => 'room-type-images/'.fake()->uuid().'.jpg',
            'is_thumbnail' => false,
            'sort_order' => 1,
        ];
    }

    /**
     * Mark this image as a thumbnail.
     */
    public function thumbnail(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_thumbnail' => true,
        ]);
    }
}
