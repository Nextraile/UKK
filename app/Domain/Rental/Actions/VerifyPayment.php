<?php

declare(strict_types=1);

namespace App\Domain\Rental\Actions;

use App\Domain\Identity\Models\User;
use App\Domain\Rental\Mail\PaymentVerifiedMail;
use App\Domain\Rental\Models\Payment;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

/**
 * Verify payment (approve).
 *
 * Simple stub implementation for COMP-006.
 * Full payment verification workflow in COMP-007.
 *
 * FR-072: Admin approve payment
 */
class VerifyPayment
{
    /**
     * Execute payment verification.
     *
     * @param  Payment  $payment  Payment to verify
     * @param  User  $admin  Admin who is verifying
     */
    public function execute(Payment $payment, User $admin): void
    {
        DB::transaction(function () use ($payment, $admin) {
            // 1. Update payment status
            $payment->update([
                'status' => 'success',
                'verified_by' => $admin->id,
                'verified_at' => now(),
                'paid_at' => now(),
            ]);

            // 2. Transition rental: pending → paid
            $rental = $payment->rental;
            $rental->update(['status' => 'paid']);

            // 3. Append status history
            $rental->statusHistories()->create([
                'status' => 'paid',
                'changed_by' => $admin->id,
                'internal_notes' => 'Payment verified by admin',
            ]);

            // 4. Send email notification
            Mail::to($rental->user->email)->queue(new PaymentVerifiedMail($rental));
        });
    }
}
