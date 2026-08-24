<?php

namespace Database\Factories;

use App\Domain\Kost\Models\Kost;
use App\Domain\Kost\Models\KostDocumentRequirement;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<KostDocumentRequirement>
 */
class KostDocumentRequirementFactory extends Factory
{
    protected $model = KostDocumentRequirement::class;

    public function definition(): array
    {
        return [
            'kost_id' => Kost::factory(),
            'document_type' => fake()->randomElement(array_keys(config('kost.document_types'))),
            'is_required' => fake()->boolean(70), // 70% chance required
            'reason' => fake()->sentence(),
        ];
    }

    public function required(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_required' => true,
        ]);
    }

    public function optional(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_required' => false,
        ]);
    }
}
