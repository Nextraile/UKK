<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Domain\Identity\Models\User;
use App\Domain\Kost\Models\Kost;
use App\Domain\Kost\Models\PriceScheme;
use App\Domain\Kost\Models\Room;
use App\Domain\Kost\Models\RoomType;
use App\Domain\Rental\Models\Payment;
use App\Domain\Rental\Models\Rental;
use App\Domain\Rental\Models\RentalStatusHistory;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Rental>
 */
class RentalFactory extends Factory
{
    protected $model = Rental::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        // Create tenant if not exists
        $tenant = User::factory()->create(['role' => 'user', 'email_verified_at' => now()]);

        // Create admin (kost owner)
        $admin = User::factory()->create(['role' => 'admin', 'email_verified_at' => now()]);

        // Create kost with approved status
        $kost = Kost::factory()->create([
            'user_id' => $admin->id,
            'status' => 'active',
        ]);

        // Create room type
        $roomType = RoomType::factory()->create([
            'kost_id' => $kost->id,
        ]);

        // Create room
        $room = Room::factory()->create([
            'kost_id' => $kost->id,
            'room_type_id' => $roomType->id,
            'status' => 'available',
        ]);

        // Create price scheme
        $priceScheme = PriceScheme::factory()->create([
            'room_type_id' => $roomType->id,
            'duration_value' => 1,
            'duration_unit' => 'month',
            'price' => 1500000,
        ]);

        $startDate = now()->addDays(5);
        $durationValue = 1;
        $roomPrice = 1500000;
        $securityDeposit = 500000;
        $grandTotal = $roomPrice + $securityDeposit;
        $endDate = $startDate->copy()->addMonths($durationValue);

        return [
            'room_id' => $room->id,
            'user_id' => $tenant->id,
            'price_scheme_id' => $priceScheme->id,
            'duration_value' => $durationValue,
            'duration_unit' => 'month',
            'room_price' => $roomPrice,
            'security_deposit' => $securityDeposit,
            'grand_total' => $grandTotal,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'status' => 'pending',
        ];
    }

    /**
     * Configure the model factory.
     */
    public function configure(): static
    {
        return $this->afterCreating(function (Rental $rental) {
            // Create initial status history (use tenant as changed_by)
            RentalStatusHistory::create([
                'rental_id' => $rental->id,
                'status' => 'pending',
                'changed_by' => $rental->user_id,
                'internal_notes' => 'Initial rental creation',
            ]);

            // Create payment record
            Payment::create([
                'rental_id' => $rental->id,
                'qris_image_path' => 'qris/default.png',
                'amount' => $rental->grand_total,
                'expired_at' => now()->addHours(48),
            ]);
        });
    }

    /**
     * Indicate that the rental is pending (default state).
     */
    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'pending',
        ]);
    }

    /**
     * Indicate that the rental is paid.
     */
    public function paid(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'paid',
        ]);
    }

    /**
     * Indicate that the rental is confirmed.
     */
    public function confirmed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'confirmed',
        ]);
    }

    /**
     * Indicate that the rental is active.
     */
    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'active',
            'start_date' => now()->subDays(5),
            'end_date' => now()->addMonths(1)->subDays(5),
        ]);
    }

    /**
     * Indicate that the rental is completed.
     */
    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'completed',
            'start_date' => now()->subMonths(2),
            'end_date' => now()->subMonths(1),
        ]);
    }

    /**
     * Indicate that the rental is cancelled.
     */
    public function cancelled(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'cancelled',
            'cancelled_reason' => 'Cancelled by tenant',
            'cancelled_at' => now(),
        ]);
    }
}
