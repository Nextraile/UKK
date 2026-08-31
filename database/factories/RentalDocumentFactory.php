<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Domain\Identity\Models\User;
use App\Domain\Rental\Models\Rental;
use App\Domain\Rental\Models\RentalDocument;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;

/**
 * @extends Factory<RentalDocument>
 */
class RentalDocumentFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<Model>
     */
    protected $model = RentalDocument::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'rental_id' => Rental::factory(),
            'document_type' => $this->faker->randomElement(['KTP', 'Passport', 'NPWP', 'KK', 'Student Card']),
            'document_path' => 'rental-documents/test-'.$this->faker->uuid().'.pdf',
            'uploaded_at' => now(),
            'verification_status' => 'pending',
            'rejection_reason' => null,
            'verified_at' => null,
            'verified_by' => null,
        ];
    }

    /**
     * Indicate that the document is approved.
     */
    public function approved(): static
    {
        return $this->state(fn (array $attributes) => [
            'verification_status' => 'approved',
            'verified_at' => now(),
            'verified_by' => User::factory(),
            'rejection_reason' => null,
        ]);
    }

    /**
     * Indicate that the document is verified (alias for approved).
     */
    public function verified(): static
    {
        return $this->approved();
    }

    /**
     * Indicate that the document is rejected.
     */
    public function rejected(string $reason = 'Dokumen tidak jelas, mohon upload ulang dengan kualitas lebih baik.'): static
    {
        return $this->state(fn (array $attributes) => [
            'verification_status' => 'rejected',
            'rejection_reason' => $reason,
            'verified_at' => now(),
            'verified_by' => User::factory(),
        ]);
    }
}
