<?php

declare(strict_types=1);

namespace App\Domain\Rental\Actions;

use App\Domain\Identity\Models\User;
use App\Domain\Kost\Models\PriceScheme;
use App\Domain\Kost\Models\Room;
use App\Domain\Rental\Exceptions\InvalidRentalDataException;
use App\Domain\Rental\Exceptions\RoomFullException;
use App\Domain\Rental\Mail\RentalCreatedMail;
use App\Domain\Rental\Models\Payment;
use App\Domain\Rental\Models\Rental;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

/**
 * Create rental with transactional room locking.
 *
 * Implements ADR-010 (pessimistic locking with SELECT...FOR UPDATE)
 * to prevent double booking race conditions.
 *
 * Creates rental + payment atomically in single transaction.
 *
 * FR-061—FR-068, FR-121, FR-122
 */
class CreateRental
{
    /**
     * Execute rental creation.
     *
     * @param  array  $data  Validated data from CreateRentalRequest
     * @return Rental Created rental with payment relationship loaded
     *
     * @throws RoomFullException If room has no capacity
     * @throws InvalidRentalDataException If data validation fails
     */
    public function execute(array $data): Rental
    {
        return DB::transaction(function () use ($data) {
            // 1. Lock room with SELECT...FOR UPDATE (ADR-010)
            // Prevents concurrent booking of same room
            $room = Room::where('id', $data['room_id'])
                ->where('status', 'available')
                ->lockForUpdate()
                ->firstOrFail();

            // 2. Load price scheme
            $priceScheme = PriceScheme::findOrFail($data['price_scheme_id']);

            // 3. Validate price scheme belongs to this room's room type
            if ($priceScheme->room_type_id !== $room->room_type_id) {
                throw InvalidRentalDataException::invalidDuration(
                    'Price scheme tidak sesuai dengan tipe kamar'
                );
            }

            // 4. Check room capacity INSIDE lock (ADR-018)
            // used_slots accessor queries rentals with status pending/paid/confirmed/active
            if ($room->free_slots <= 0) {
                throw RoomFullException::noCapacity($room);
            }

            // 5. Calculate dates
            $startDate = Carbon::parse($data['start_date']);
            $endDate = $this->calculateEndDate(
                $startDate,
                $data['duration'],
                $priceScheme->duration_unit
            );

            // 6. Calculate grand total
            $roomPrice = $priceScheme->price;
            $securityDeposit = $room->roomType->security_deposit;
            $grandTotal = ($roomPrice * $data['duration']) + $securityDeposit;

            // 7. Create rental (status: pending, snapshot data)
            $rental = Rental::create([
                'room_id' => $room->id,
                'user_id' => $data['user_id'],
                'price_scheme_id' => $priceScheme->id,
                'duration_value' => $data['duration'],
                'duration_unit' => $priceScheme->duration_unit,
                'start_date' => $startDate,
                'end_date' => $endDate,
                'room_price' => $roomPrice,
                'security_deposit' => $securityDeposit,
                'grand_total' => $grandTotal,
                'status' => 'pending',
            ]);

            // 8. Create payment record (1:1 with rental)
            Payment::create([
                'rental_id' => $rental->id,
                'qris_image_path' => $room->roomType->kost->qris_image_path,
                'amount' => $grandTotal,
                'status' => 'pending',
                'expired_at' => now()->addHours(48), // FR-121: 48 hour deadline
            ]);

            // 9. Append initial status history
            $rental->statusHistories()->create([
                'status' => 'pending',
                'changed_by' => $data['user_id'],
                'internal_notes' => 'Rental created by tenant',
            ]);

            // Lock released here (transaction commit)

            // 10. Send email notification (async, queued)
            /** @var User $user */
            $user = $rental->user;
            Mail::to($user->email)->queue(new RentalCreatedMail($rental));

            return $rental->fresh(['payment', 'room.roomType.kost', 'statusHistories']);
        });
    }

    /**
     * Calculate end date based on duration and unit.
     *
     * @param  Carbon  $startDate  Contract start date
     * @param  int  $durationValue  Duration value (e.g., 3)
     * @param  string  $durationUnit  Unit: day/week/month
     * @return Carbon Contract end date
     */
    private function calculateEndDate(Carbon $startDate, int $durationValue, string $durationUnit): Carbon
    {
        return match ($durationUnit) {
            'day' => $startDate->copy()->addDays($durationValue),
            'week' => $startDate->copy()->addWeeks($durationValue),
            'month' => $startDate->copy()->addMonths($durationValue),
            default => throw InvalidRentalDataException::invalidDuration("Unit '{$durationUnit}' tidak valid"),
        };
    }
}
