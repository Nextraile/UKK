<?php

declare(strict_types=1);

namespace App\Http\Controllers\Tenant;

use App\Domain\Rental\Models\Rental;
use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\UploadProofOfPaymentRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class PaymentController extends Controller
{
    /**
     * Show payment page with QRIS and upload form.
     *
     * FR-069: Display QRIS + bank info
     */
    public function show(Rental $rental): View
    {
        $this->authorize('view', $rental);
        abort_if($rental->status !== 'pending', 403, 'Payment hanya untuk rental dengan status pending');

        return view('tenant.payments.show', compact('rental'));
    }

    /**
     * Upload proof of payment.
     *
     * FR-070: Tenant upload proof
     * FR-075: Re-upload clears rejection_reason
     */
    public function uploadProof(UploadProofOfPaymentRequest $request, Rental $rental): RedirectResponse
    {
        $this->authorize('uploadPayment', $rental);

        $path = $request->file('proof')->store('payment-proofs', 'private');

        $rental->payment->update([
            'proof_of_payment_path' => $path,
            'rejection_reason' => null, // Clear rejection reason on re-upload
        ]);

        return redirect()
            ->route('rentals.show', $rental)
            ->with('success', 'Bukti pembayaran berhasil diupload. Menunggu verifikasi admin.');
    }
}
