<?php

declare(strict_types=1);

namespace App\Domain\Rental\Actions;

use App\Domain\Rental\Exceptions\DocumentAlreadyVerifiedException;
use App\Domain\Rental\Mail\DocumentRejectedMail;
use App\Domain\Rental\Mail\DocumentVerifiedMail;
use App\Domain\Rental\Mail\RentalConfirmedMail;
use App\Domain\Rental\Models\Rental;
use App\Domain\Rental\Models\RentalDocument;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

/**
 * Verify rental document (approve or reject).
 *
 * Handles admin verification of uploaded documents.
 * Auto-confirms rental when all required documents are approved.
 *
 * FR-088, FR-089, FR-090
 */
class VerifyDocument
{
    /**
     * Execute document verification.
     *
     * @param  RentalDocument  $document  The document to verify
     * @param  bool  $approved  True to approve, false to reject
     * @param  string|null  $rejectionReason  Required if rejected
     * @return RentalDocument Updated document record
     */
    public function execute(
        RentalDocument $document,
        bool $approved,
        ?string $rejectionReason = null
    ): RentalDocument {
        return DB::transaction(function () use ($document, $approved, $rejectionReason) {
            // Lock document row first
            $lockedDocument = RentalDocument::lockForUpdate()->findOrFail($document->id);

            // Guard: prevent double verification
            if ($lockedDocument->verification_status === 'approved') {
                throw new DocumentAlreadyVerifiedException(
                    'Dokumen sudah diverifikasi sebelumnya.'
                );
            }

            // Update document verification status
            $lockedDocument->update([
                'verification_status' => $approved ? 'approved' : 'rejected',
                'rejection_reason' => $rejectionReason,
                'verified_at' => now(),
                'verified_by' => auth()->id(),
            ]);

            // Reload rental with all required relationships
            /** @var Rental $rental */
            $rental = $lockedDocument->rental()->with([
                'room.roomType.kost.documentRequirements',
                'rentalDocuments',
            ])->first();

            if ($approved) {
                // Send approval email
                Mail::to($rental->user->email)
                    ->queue(new DocumentVerifiedMail($lockedDocument));

                // Check if ALL required documents are approved
                $this->checkAndConfirmRental($rental);
            } else {
                // Send rejection email
                Mail::to($rental->user->email)
                    ->queue(new DocumentRejectedMail($lockedDocument));
            }

            return $lockedDocument;
        });
    }

    /**
     * Check if all required documents approved and auto-confirm rental.
     */
    private function checkAndConfirmRental(Rental $rental): void
    {
        // Lock rental row to prevent concurrent auto-confirm
        $lockedRental = Rental::lockForUpdate()->findOrFail($rental->id);

        // Only proceed if rental is in documents_pending status
        if ($lockedRental->status !== 'documents_pending') {
            return;
        }

        // Get required document types from kost configuration
        $requiredDocTypes = $lockedRental->room->roomType->kost->documentRequirements
            ->where('is_required', true)
            ->pluck('document_type')
            ->toArray();

        if (empty($requiredDocTypes)) {
            // No documents required, should not happen in normal flow
            return;
        }

        // Query fresh from database within transaction lock
        $approvedDocTypes = DB::table('rental_documents')
            ->where('rental_id', $lockedRental->id)
            ->where('verification_status', 'approved')
            ->pluck('document_type')
            ->toArray();

        // Fix: Use unique check to prevent duplicate document types from passing validation
        $uniqueApproved = array_unique($approvedDocTypes);
        $allApproved = count(array_intersect($requiredDocTypes, $uniqueApproved)) === count($requiredDocTypes);

        if ($allApproved) {
            $this->confirmRental($lockedRental);
        }
    }

    /**
     * Confirm rental and send confirmation email.
     */
    private function confirmRental(Rental $rental): void
    {
        $rental->update([
            'status' => 'confirmed',
            'confirmed_at' => now(),
        ]);

        // Use authenticated admin user (who verified the last document) as changed_by
        // Fallback to system user ID (1) if not authenticated (e.g., automated process)
        $changedBy = auth()->id() ?? 1;

        $rental->statusHistories()->create([
            'status' => 'confirmed',
            'changed_by' => $changedBy,
            'internal_notes' => 'All required documents verified and approved',
        ]);

        // Send confirmation email to tenant
        Mail::to($rental->user->email)
            ->queue(new RentalConfirmedMail($rental));
    }
}
