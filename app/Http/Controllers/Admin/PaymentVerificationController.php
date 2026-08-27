<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Domain\Identity\Models\User;
use App\Domain\Rental\Actions\RejectPayment;
use App\Domain\Rental\Actions\VerifyPayment;
use App\Domain\Rental\Models\Payment;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\RejectPaymentRequest;
use Illuminate\Http\RedirectResponse;

class PaymentVerificationController extends Controller
{
    /**
     * Approve payment.
     *
     * FR-072: Admin approve payment
     */
    public function approve(Payment $payment): RedirectResponse
    {
        /** @var User $admin */
        $admin = auth()->user();
        abort_if($payment->rental->room->roomType->kost->user_id !== $admin->id, 403);

        app(VerifyPayment::class)->execute($payment, $admin);

        return redirect()
            ->route('admin.rentals.show', $payment->rental)
            ->with('success', 'Pembayaran berhasil diverifikasi.');
    }

    /**
     * Reject payment with reason.
     *
     * FR-073: Admin reject payment with reason
     */
    public function reject(RejectPaymentRequest $request, Payment $payment): RedirectResponse
    {
        /** @var User $admin */
        $admin = auth()->user();
        abort_if($payment->rental->room->roomType->kost->user_id !== $admin->id, 403);

        app(RejectPayment::class)->execute($payment, $request->validated('rejection_reason'), $admin);

        return redirect()
            ->route('admin.rentals.show', $payment->rental)
            ->with('success', 'Pembayaran ditolak. Tenant akan diberitahu untuk upload ulang.');
    }
}
