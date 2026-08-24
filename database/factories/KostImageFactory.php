<?php

namespace Database\Factories;

use App\Domain\Kost\Models\Kost;
use App\Domain\Kost\Models\KostImage;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<KostImage>
 */
class KostImageFactory extends Factory
{
    protected $model = KostImage::class;

    public function definition(): array
    {
        return [
            'kost_id' => Kost::factory(),
            'image_path' => 'kost-images/sample-'.fake()->uuid().'.jpg',
            'is_thumbnail' => false,
            'sort_order' => fake()->numberBetween(1, 10),
        ];
    }

    public function thumbnail(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_thumbnail' => true,
            'sort_order' => 1,
        ]);
    }
}
