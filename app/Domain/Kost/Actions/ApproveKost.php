<?php

declare(strict_types=1);

namespace App\Domain\Kost\Actions;

use App\Domain\Kost\Exceptions\InvalidKostTransitionException;
use App\Domain\Kost\Mail\KostApprovedMail;
use App\Domain\Kost\Models\Kost;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

/**
 * Approve a kost submission (Super Admin only).
 *
 * Transition: Pending Review → Approved
 * Side effects: Set approved_at timestamp, notify owner via email
 *
 * FR-018: Super Admin review submitted kosts
 * FR-019: Approval transitions to Approved status
 */
class ApproveKost
{
    /**
     * Execute the approval action.
     *
     * @param  Kost  $kost  The kost being approved
     * @return Kost The approved kost instance
     *
     * @throws InvalidKostTransitionException If status != pending_review
     */
    public function execute(Kost $kost): Kost
    {
        // Guard: only pending_review can be approved
        if ($kost->status !== 'pending_review') {
            throw InvalidKostTransitionException::cannotApprove($kost);
        }

        return DB::transaction(function () use ($kost) {
            $kost->status = 'approved';
            $kost->approved_at = now();
            $kost->rejected_reason = null; // Clear any previous rejection reason
            $kost->save();

            // Send approval notification to kost owner
            Mail::to($kost->owner->email)->send(new KostApprovedMail($kost));

            return $kost->fresh();
        });
    }
}
