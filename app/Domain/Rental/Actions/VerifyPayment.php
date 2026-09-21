<?php

declare(strict_types=1);

namespace App\Domain\Rental\Actions;

use App\Domain\Identity\Models\User;
use App\Domain\Payment\Exceptions\PaymentAlreadyVerifiedException;
use App\Domain\Payment\Mail\PaymentVerifiedMail;
use App\Domain\Payment\Models\Payment;
use App\Domain\Rental\Models\Rental;
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
     * Updates payment status to verified, transitions rental to paid,
     * appends status history, and queues email notification.
     *
     * @param  Payment  $payment  Payment to verify
     * @param  User  $admin  Admin who is verifying
     */
    public function execute(Payment $payment, User $admin): void
    {
        DB::transaction(function () use ($payment, $admin) {
            // Lock payment row first
            $payment = Payment::lockForUpdate()->findOrFail($payment->id);

            // Guard: prevent double verification
            if ($payment->status === 'success') {
                throw new PaymentAlreadyVerifiedException(
                    'Pembayaran sudah diverifikasi pada '.
                    $payment->verified_at->format('d/m/Y H:i').
                    ' oleh admin.'
                );
            }

            // 1. Update payment status
            $payment->update([
                'status' => 'success',
                'verified_by' => $admin->id,
                'verified_at' => now(),
                'paid_at' => now(),
            ]);

            // 2. Lock rental before updating
            /** @var Rental $rental */
            $rental = $payment->rental()->lockForUpdate()->firstOrFail();

            // Only update if still in pending status
            if ($rental->status === 'pending') {
                $rental->update(['status' => 'paid']);

                // 3. Append status history
                $rental->statusHistories()->create([
                    'status' => 'paid',
                    'changed_by' => $admin->id,
                    'internal_notes' => 'Payment verified by admin',
                ]);
            }

            // 4. Send email notification
            Mail::to($rental->user->email)->queue(new PaymentVerifiedMail($rental));
        });
    }
}
