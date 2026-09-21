<?php

declare(strict_types=1);

namespace App\Domain\Rental\Actions;

use App\Domain\Rental\Exceptions\InvalidRentalStatusException;
use App\Domain\Rental\Mail\RentalActivatedMail;
use App\Domain\Rental\Models\Rental;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

/**
 * Activate a rental on start_date.
 *
 * Transition: Confirmed → Active
 * Side effects: Set activated_at timestamp, record status history, notify tenant via email
 *
 * FR-084: Auto-activate rental on start_date
 * COMP-006: Rental Lifecycle Management
 */
class ActivateRental
{
    /**
     * Execute the activation action.
     *
     * Activates a confirmed rental. By default, validates that start_date has passed.
     * Use $manualOverride = true to bypass date validation (admin manual activation).
     *
     * @param  Rental  $rental  The rental to activate
     * @param  bool  $manualOverride  If true, bypass start_date validation
     * @return Rental The activated rental instance
     *
     * @throws InvalidRentalStatusException If status != confirmed or start_date is in future (without override)
     */
    public function execute(Rental $rental, bool $manualOverride = false): Rental
    {
        return DB::transaction(function () use ($rental, $manualOverride) {
            // Lock the rental row for update (pessimistic locking for concurrency)
            $rental = Rental::lockForUpdate()->findOrFail($rental->id);

            // Guard: only confirmed rentals can be activated
            if ($rental->status !== 'confirmed') {
                throw new InvalidRentalStatusException(
                    "Cannot activate rental #{$rental->id}: current status is '{$rental->status}', expected 'confirmed'"
                );
            }

            // Validate start_date has passed (unless manual override)
            if (! $manualOverride && $rental->start_date->isFuture()) {
                throw new InvalidRentalStatusException(
                    "Cannot activate rental #{$rental->id}: start_date is in the future ({$rental->start_date->format('Y-m-d')}). Use manualOverride to force activation."
                );
            }

            // Update rental status
            $rental->update([
                'status' => 'active',
                'activated_at' => now(),
            ]);

            // Record status history
            $notes = $manualOverride
                ? 'Manually activated by admin'
                : 'Auto-activated on start date';

            $rental->statusHistories()->create([
                'status' => 'active',
                'changed_by' => 1, // System user ID (or pass admin ID for manual activation)
                'internal_notes' => $notes,
            ]);

            // Send activation notification to tenant
            Mail::to($rental->user->email)->queue(new RentalActivatedMail($rental));

            return $rental->fresh();
        });
    }
}
