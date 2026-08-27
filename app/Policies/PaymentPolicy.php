<?php

declare(strict_types=1);

namespace App\Policies;

use App\Domain\Identity\Models\User;
use App\Domain\Rental\Models\Payment;

/**
 * Authorization policy for Payment model.
 *
 * Controls access to payment verification actions (approve, reject).
 * Only admin who owns the kost can verify payments for rentals in that kost.
 */
class PaymentPolicy
{
    /**
     * Determine if user can verify payment (approve/reject).
     *
     * Only admin who owns the kost can verify payments for rentals in that kost.
     *
     * @param  User  $user  The user attempting to verify
     * @param  Payment  $payment  The payment to verify
     * @return bool True if admin owns the kost, false otherwise
     */
    public function verify(User $user, Payment $payment): bool
    {
        if ($user->role !== 'admin') {
            return false;
        }

        return $payment->rental->room->roomType->kost->user_id === $user->id;
    }
}
