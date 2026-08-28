<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Domain\Rental\Models\Rental;
use App\Domain\Review\Models\Review;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Review>
 */
class ReviewFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<Review>
     */
    protected $model = Review::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $hasKostRating = $this->faker->boolean(80); // 80% chance
        $hasRoomRating = $hasKostRating ? $this->faker->boolean(70) : true; // Ensure at least one rating

        return [
            'rental_id' => Rental::factory(),
            'kost_rating' => $hasKostRating ? $this->faker->numberBetween(1, 5) : null,
            'kost_comment' => $hasKostRating ? $this->faker->optional(0.8)->paragraph() : null,
            'room_rating' => $hasRoomRating ? $this->faker->numberBetween(1, 5) : null,
            'room_comment' => $hasRoomRating ? $this->faker->optional(0.8)->paragraph() : null,
            'images' => $this->faker->optional(0.6)->randomElements([
                'review-images/1/image1.jpg',
                'review-images/1/image2.jpg',
                'review-images/1/image3.jpg',
            ], $this->faker->numberBetween(1, 3)),
        ];
    }

    /**
     * State: Review with only kost rating (no room rating).
     */
    public function kostOnly(): static
    {
        return $this->state(fn (array $attributes) => [
            'kost_rating' => $this->faker->numberBetween(1, 5),
            'kost_comment' => $this->faker->paragraph(),
            'room_rating' => null,
            'room_comment' => null,
        ]);
    }

    /**
     * State: Review with only room rating (no kost rating).
     */
    public function roomOnly(): static
    {
        return $this->state(fn (array $attributes) => [
            'kost_rating' => null,
            'kost_comment' => null,
            'room_rating' => $this->faker->numberBetween(1, 5),
            'room_comment' => $this->faker->paragraph(),
        ]);
    }

    /**
     * State: Review with no images.
     */
    public function noImages(): static
    {
        return $this->state(fn (array $attributes) => [
            'images' => null,
        ]);
    }

    /**
     * State: Review with maximum images (5).
     */
    public function maxImages(): static
    {
        return $this->state(fn (array $attributes) => [
            'images' => [
                'review-images/1/image1.jpg',
                'review-images/1/image2.jpg',
                'review-images/1/image3.jpg',
                'review-images/1/image4.jpg',
                'review-images/1/image5.jpg',
            ],
        ]);
    }
}
