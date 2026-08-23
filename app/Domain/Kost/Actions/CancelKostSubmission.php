<?php

declare(strict_types=1);

namespace App\Domain\Kost\Actions;

use App\Domain\Kost\Exceptions\InvalidKostTransitionException;
use App\Domain\Kost\Models\Kost;
use Illuminate\Support\Facades\DB;

/**
 * Cancel kost submission and revert to draft (Admin only).
 *
 * Transition: Pending Review → Draft
 * Side effects: Clear submitted_at timestamp
 * NO email notification to SuperAdmin (per business requirement)
 *
 * FR-016, FR-023: Admin can withdraw submission before approval
 */
class CancelKostSubmission
{
    /**
     * Execute the cancellation action.
     *
     * @param  Kost  $kost  The kost submission being cancelled
     * @return Kost The updated kost instance
     *
     * @throws InvalidKostTransitionException If status != pending_review
     */
    public function execute(Kost $kost): Kost
    {
        if ($kost->status !== 'pending_review') {
            throw InvalidKostTransitionException::cannotCancel($kost);
        }

        return DB::transaction(function () use ($kost) {
            $kost->status = 'draft';
            $kost->submitted_at = null;
            $kost->save();

            return $kost->fresh();
        });
    }
}
