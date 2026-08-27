<?php

declare(strict_types=1);

namespace App\Http\Controllers\Tenant;

use App\Domain\Rental\Models\Rental;
use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\UploadProofOfPaymentRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpKernel\Exception\HttpException;

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

        // Eager load kost for bank info display (FR-069)
        $rental->load('room.roomType.kost', 'payment');

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

    /**
     * Download payment proof with authorization.
     *
     * Serves payment proof file from private storage after verifying
     * tenant owns the rental. Returns 404 if proof not uploaded yet.
     *
     * @param  Rental  $rental  The rental to download proof for
     *
     * @throws HttpException 404 if proof not found
     */
    public function downloadProof(Rental $rental): BinaryFileResponse
    {
        $this->authorize('view', $rental);

        if (! $rental->payment->proof_of_payment_path) {
            abort(404, 'Payment proof not uploaded yet');
        }

        $path = storage_path('app/private/'.$rental->payment->proof_of_payment_path);

        if (! file_exists($path)) {
            abort(404, 'Payment proof file not found');
        }

        return response()->file($path, [
            'Content-Type' => 'image/jpeg',
        ]);
    }

    /**
     * Download QRIS image with authorization.
     *
     * Serves QRIS image from private storage after verifying tenant
     * owns the rental. Returns 404 if QRIS not configured for kost.
     *
     * @param  Rental  $rental  The rental to download QRIS for
     *
     * @throws HttpException 404 if QRIS not found
     */
    public function downloadQris(Rental $rental): BinaryFileResponse
    {
        $this->authorize('view', $rental);

        if (! $rental->payment->qris_image_path) {
            abort(404, 'QRIS not configured for this kost');
        }

        $path = storage_path('app/private/'.$rental->payment->qris_image_path);

        if (! file_exists($path)) {
            abort(404, 'QRIS image not found');
        }

        return response()->file($path, [
            'Content-Type' => 'image/png',
        ]);
    }
}
