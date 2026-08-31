<?php

declare(strict_types=1);

namespace App\Domain\Rental\Actions;

use App\Domain\Identity\Models\User;
use App\Domain\Rental\Mail\PaymentRejectedMail;
use App\Domain\Rental\Models\Payment;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

/**
 * Reject payment with reason.
 *
 * FR-073: Admin reject payment with reason (wajib)
 */
class RejectPayment
{
    /**
     * Execute payment rejection.
     *
     * Updates payment with rejection reason, keeps rental status as pending,
     * appends status history, and queues email notification.
     *
     * Per FR-073, FR-074, FR-075: Tenant must be able to view rejection reason
     * and re-upload payment proof. Rental status remains 'pending' to allow re-upload.
     *
     * @param  Payment  $payment  Payment to reject
     * @param  string  $reason  Rejection reason (min 10 chars)
     * @param  User  $admin  Admin who is rejecting the payment
     */
    public function execute(Payment $payment, string $reason, User $admin): void
    {
        DB::transaction(function () use ($payment, $reason, $admin) {
            // 1. Update payment rejection reason (clear proof to allow re-upload)
            $payment->update([
                'rejection_reason' => $reason,
                'proof_of_payment_path' => null,
                'paid_at' => null,
            ]);

            // 2. Keep rental status as 'pending' to allow tenant re-upload
            $rental = $payment->rental;

            // 3. Append status history (informational, not state transition)
            $rental->statusHistories()->create([
                'status' => 'pending',
                'changed_by' => $admin->id,
                'internal_notes' => "Payment rejected by admin. Reason: {$reason}",
            ]);

            // 4. Send email notification
            Mail::to($rental->user->email)->queue(new PaymentRejectedMail($rental));
        });
    }
}
