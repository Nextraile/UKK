<?php

declare(strict_types=1);

namespace App\Domain\Kost\Actions;

use App\Domain\Identity\Models\User;
use App\Domain\Kost\Exceptions\InvalidKostTransitionException;
use App\Domain\Kost\Mail\KostApprovedMail;
use App\Domain\Kost\Models\Kost;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

/**
 * Approve a kost submission (Super Admin only).
 *
 * Transition: Pending Review → Approved
 * Side effects: Set approved_at timestamp, record approver, notify owner via email
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
     * @param  User  $superAdmin  The super admin approving the kost
     * @return Kost The approved kost instance
     *
     * @throws InvalidKostTransitionException If status != pending_review
     */
    public function execute(Kost $kost, User $superAdmin): Kost
    {
        return DB::transaction(function () use ($kost, $superAdmin) {
            // Lock the kost row for update (pessimistic locking for concurrency)
            $kost = Kost::lockForUpdate()->findOrFail($kost->id);

            // Guard: only pending_review can be approved
            if ($kost->status !== 'pending_review') {
                throw InvalidKostTransitionException::cannotApprove($kost);
            }

            $kost->status = 'approved';
            $kost->approved_at = now();
            $kost->approved_by = $superAdmin->id;
            $kost->rejected_reason = null; // Clear any previous rejection reason
            $kost->rejected_by = null; // Clear any previous rejection
            $kost->save();

            // Send approval notification to kost owner
            Mail::to($kost->owner->email)->queue(new KostApprovedMail($kost));

            return $kost->fresh();
        });
    }
}
