<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Domain\Identity\Models\User;
use App\Domain\Rental\Models\Rental;
use App\Domain\Rental\Models\RentalStatusHistory;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<RentalStatusHistory>
 */
class RentalStatusHistoryFactory extends Factory
{
    protected $model = RentalStatusHistory::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'rental_id' => Rental::factory(),
            'status' => 'pending',
            'changed_by' => User::factory(),
            'internal_notes' => $this->faker->sentence(),
            'created_at' => now(),
        ];
    }

    /**
     * Status: pending
     */
    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'pending',
            'internal_notes' => 'Rental created by tenant',
        ]);
    }

    /**
     * Status: paid
     */
    public function paid(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'paid',
            'internal_notes' => 'Payment verified by admin',
        ]);
    }

    /**
     * Status: rejected
     */
    public function rejected(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'rejected',
            'internal_notes' => 'Payment rejected: Invalid proof of payment',
        ]);
    }

    /**
     * Status: documents_pending
     */
    public function documentsPending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'documents_pending',
            'internal_notes' => 'First document uploaded by tenant',
        ]);
    }

    /**
     * Status: confirmed
     */
    public function confirmed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'confirmed',
            'internal_notes' => 'All required documents approved',
        ]);
    }

    /**
     * Status: active
     */
    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'active',
            'internal_notes' => 'Rental activated on start date',
        ]);
    }

    /**
     * Status: completed
     */
    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'completed',
            'internal_notes' => 'Rental completed on end date',
        ]);
    }

    /**
     * Status: cancelled
     */
    public function cancelled(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'cancelled',
            'internal_notes' => 'Rental cancelled by tenant',
        ]);
    }

    /**
     * System user (for automated transitions)
     */
    public function bySystem(): static
    {
        return $this->state(fn (array $attributes) => [
            'changed_by' => 1, // System user ID
        ]);
    }
}
