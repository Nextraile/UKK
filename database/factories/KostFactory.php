<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Domain\Identity\Models\User;
use App\Domain\Kost\Models\Kost;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Kost>
 */
class KostFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<Kost>
     */
    protected $model = Kost::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory()->admin(),
            'name' => fake()->company().' Kost',
            'slug' => null, // Auto-generated in model boot()
            'description' => fake()->paragraph(3),
            'contact_number' => fake()->numerify('08##########'),
            'facilities' => ['WiFi', 'AC', 'Kasur', 'Lemari'],
            'rules' => ['No smoking', 'No pets', 'Quiet hours 22:00-06:00'],
            'qris_image_path' => null,
            'bank_name' => fake()->randomElement(['BCA', 'BNI', 'Mandiri', 'BRI']),
            'account_number' => fake()->numerify('##########'),
            'account_holder_name' => fake()->name(),
            'status' => 'draft',
            'published_at' => null,
            'approved_at' => null,
            'approved_by' => null,
            'rejected_reason' => null,
        ];
    }

    /**
     * Indicate kost is draft (default state).
     */
    public function draft(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'draft',
        ]);
    }

    /**
     * Indicate kost is pending review.
     */
    public function pendingReview(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'pending_review',
        ]);
    }

    /**
     * Indicate kost is approved.
     */
    public function approved(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'approved',
            'approved_at' => now(),
            'approved_by' => User::factory()->superAdmin(),
        ]);
    }

    /**
     * Indicate kost is active (published).
     */
    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'active',
            'approved_at' => now()->subDays(2),
            'approved_by' => User::factory()->superAdmin(),
            'published_at' => now()->subDay(),
        ]);
    }

    /**
     * Indicate kost is rejected.
     */
    public function rejected(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'rejected',
            'rejected_reason' => 'Data tidak lengkap. Mohon lengkapi alamat dan foto.',
        ]);
    }
}
