<?php

declare(strict_types=1);

namespace App\Domain\Kost\Actions;

use App\Domain\Kost\Exceptions\InvalidKostTransitionException;
use App\Domain\Kost\Mail\KostRejectedMail;
use App\Domain\Kost\Models\Kost;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

/**
 * Reject a kost submission with reason (Super Admin only).
 *
 * Transition: Pending Review → Rejected
 * Side effects: Store rejection reason, set rejected_at timestamp, notify owner via email
 *
 * FR-018: Super Admin review submitted kosts
 * FR-023: Rejection with reason notification
 */
class RejectKost
{
    /**
     * Execute the rejection action.
     *
     * @param  Kost  $kost  The kost being rejected
     * @param  string  $reason  Rejection reason (required, min 10 chars per FR-023)
     * @return Kost The rejected kost instance
     *
     * @throws InvalidKostTransitionException If status != pending_review
     * @throws \InvalidArgumentException If reason empty or too short
     */
    public function execute(Kost $kost, string $reason): Kost
    {
        // Validate rejection reason
        if (empty(trim($reason))) {
            throw new \InvalidArgumentException('Rejection reason is required');
        }

        if (mb_strlen(trim($reason)) < 10) {
            throw new \InvalidArgumentException('Rejection reason must be at least 10 characters');
        }

        // Guard: only pending_review can be rejected
        if ($kost->status !== 'pending_review') {
            throw InvalidKostTransitionException::cannotReject($kost);
        }

        return DB::transaction(function () use ($kost, $reason) {
            $kost->status = 'rejected';
            $kost->rejected_at = now();
            $kost->rejected_reason = trim($reason);
            $kost->save();

            // Send rejection notification to kost owner
            Mail::to($kost->owner->email)->queue(new KostRejectedMail($kost));

            return $kost->fresh();
        });
    }
}
