<?php

declare(strict_types=1);

namespace App\Domain\Rental\Actions;

use App\Domain\Rental\Exceptions\InvalidRentalStatusException;
use App\Domain\Rental\Mail\RentalCancelledAdminNotificationMail;
use App\Domain\Rental\Mail\RentalCancelledMail;
use App\Domain\Rental\Models\Rental;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

/**
 * Cancel a rental manually by tenant.
 *
 * FR-094: Manual cancellation by tenant before start_date
 * FR-095: Cancellation side effects (status history, emails, room release)
 */
class CancelRental
{
    /**
     * Cancel a rental manually by tenant.
     *
     * @param  Rental  $rental  The rental to cancel
     * @param  int  $userId  Tenant user ID (for authorization verification)
     * @param  string|null  $cancellationReason  Optional reason from tenant
     *
     * @throws InvalidRentalStatusException If rental cannot be cancelled
     * @throws \RuntimeException If user is not rental owner
     */
    public function execute(
        Rental $rental,
        int $userId,
        ?string $cancellationReason = null
    ): Rental {
        // Authorization check
        if ($rental->user_id !== $userId) {
            throw new \RuntimeException('Cannot cancel rental that does not belong to you');
        }

        // Status validation
        $cancellableStatuses = ['pending', 'paid', 'documents_pending', 'confirmed'];
        if (! in_array($rental->status, $cancellableStatuses)) {
            throw new InvalidRentalStatusException(
                "Rental tidak dapat dibatalkan. Status saat ini: {$rental->status}. Hanya rental dengan status pending, paid, documents_pending, atau confirmed yang dapat dibatalkan."
            );
        }

        // Date validation - cannot cancel after start_date has passed
        if ($rental->start_date->isPast()) {
            throw new InvalidRentalStatusException(
                'Rental tidak dapat dibatalkan karena tanggal mulai sudah terlewat.'
            );
        }

        return DB::transaction(function () use ($rental, $userId, $cancellationReason) {
            // 1. Update rental status to cancelled
            $rental->update([
                'status' => 'cancelled',
                'cancelled_at' => now(),
                'cancelled_reason' => $cancellationReason,
            ]);

            // 2. Record status history
            $notes = $cancellationReason
                ? "Dibatalkan oleh tenant. Alasan: {$cancellationReason}"
                : 'Dibatalkan oleh tenant.';

            $rental->statusHistories()->create([
                'status' => 'cancelled',
                'changed_by' => $userId,
                'internal_notes' => $notes,
            ]);

            // 3. Send notification emails (queued)
            // Email to tenant (confirmation)
            Mail::to($rental->user->email)->queue(new RentalCancelledMail($rental));

            // Email to kost admin (notification)
            $kostOwner = $rental->room->roomType->kost->owner;
            Mail::to($kostOwner->email)->queue(new RentalCancelledAdminNotificationMail($rental));

            // 4. Room occupancy is automatically released via calculated attribute
            // No need to manually decrement - ADR-017: Real-time availability calculation

            return $rental->fresh();
        });
    }
}
