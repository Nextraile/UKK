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
     * Updates payment with rejection reason, transitions rental to rejected,
     * appends status history, and queues email notification.
     *
     * @param  Payment  $payment  Payment to reject
     * @param  string  $reason  Rejection reason (min 10 chars)
     * @param  User  $admin  Admin who is rejecting the payment
     */
    public function execute(Payment $payment, string $reason, User $admin): void
    {
        DB::transaction(function () use ($payment, $reason, $admin) {
            // 1. Update payment rejection reason
            $payment->update([
                'rejection_reason' => $reason,
            ]);

            // 2. Transition rental: pending/paid → rejected
            $rental = $payment->rental;
            $rental->update(['status' => 'rejected']);

            // 3. Append status history
            $rental->statusHistories()->create([
                'status' => 'rejected',
                'changed_by' => $admin->id,
                'internal_notes' => "Payment rejected by admin. Reason: {$reason}",
            ]);

            // 4. Send email notification
            Mail::to($rental->user->email)->queue(new PaymentRejectedMail($rental));
        });
    }
}
