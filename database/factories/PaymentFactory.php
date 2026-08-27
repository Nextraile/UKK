<?php

namespace Database\Factories;

use App\Domain\Rental\Models\Payment;
use App\Domain\Rental\Models\Rental;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Payment>
 */
class PaymentFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<Payment>
     */
    protected $model = Payment::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'rental_id' => Rental::factory(),
            'qris_image_path' => 'qris/test-qris.png',
            'amount' => fake()->randomFloat(2, 100000, 5000000),
            'status' => 'pending',
            'expired_at' => now()->addHours(48),
        ];
    }

    /**
     * Indicate that the payment is successful.
     */
    public function success(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'success',
            'paid_at' => now(),
        ]);
    }
}
