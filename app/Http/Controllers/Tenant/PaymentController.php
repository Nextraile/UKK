<?php

declare(strict_types=1);

namespace App\Http\Controllers\Tenant;

use App\Domain\Rental\Models\Rental;
use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\UploadProofOfPaymentRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;
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

        $file = $request->file('proof');
        $filename = Str::uuid().'.'.$file->guessExtension();
        $path = $file->storeAs('payment-proofs', $filename, 'private');

        $rental->payment->update([
            'proof_of_payment_path' => $path,
            'rejection_reason' => null, // Clear rejection reason on re-upload
        ]);

        return redirect()
            ->route('rentals.show', $rental)
            ->with('success', 'Bukti pembayaran berhasil diupload. Menunggu verifikasi admin.');
    }

    /**
     * Download/view payment proof with authorization.
     *
     * Serves payment proof file from private storage after verifying
     * tenant owns the rental OR admin owns the kost. Returns 404 if proof not uploaded yet.
     * Returns inline response (not download) so images can be displayed in browser.
     *
     * @param  Rental  $rental  The rental to view/download proof for
     *
     * @throws HttpException 404 if proof not found
     */
    public function downloadProof(Rental $rental): StreamedResponse
    {
        $this->authorize('viewPaymentProof', $rental);

        if (! $rental->payment->proof_of_payment_path) {
            abort(404, 'Payment proof not uploaded yet');
        }

        // Use response() instead of download() to serve inline (displays in browser)
        // This allows images to be shown in <img> tags, same as document display
        return Storage::disk('private')->response($rental->payment->proof_of_payment_path);
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
    public function downloadQris(Rental $rental): StreamedResponse
    {
        $this->authorize('view', $rental);

        if (! $rental->payment->qris_image_path) {
            abort(404, 'QRIS not configured for this kost');
        }

        return Storage::disk('private')->response($rental->payment->qris_image_path, null, [
            'Content-Type' => 'image/png',
        ]);
    }
}
