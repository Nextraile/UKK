<?php

declare(strict_types=1);

namespace App\Domain\Rental\Actions;

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
            // Update document verification status
            $document->update([
                'verification_status' => $approved ? 'approved' : 'rejected',
                'rejection_reason' => $rejectionReason,
                'verified_at' => now(),
                'verified_by' => auth()->id(),
            ]);

            // Reload rental with all required relationships
            /** @var Rental $rental */
            $rental = $document->rental()->with([
                'room.roomType.kost.documentRequirements',
                'rentalDocuments',
            ])->first();

            if ($approved) {
                // Send approval email
                Mail::to($rental->user->email)
                    ->queue(new DocumentVerifiedMail($document));

                // Check if ALL required documents are approved
                $this->checkAndConfirmRental($rental);
            } else {
                // Send rejection email
                Mail::to($rental->user->email)
                    ->queue(new DocumentRejectedMail($document));
            }

            return $document;
        });
    }

    /**
     * Check if all required documents approved and auto-confirm rental.
     *
     * @param  Rental  $rental
     */
    private function checkAndConfirmRental($rental): void
    {
        // Refresh rental to get latest status from DB
        $rental->refresh();

        // Only proceed if rental is in documents_pending status
        if ($rental->status !== 'documents_pending') {
            return;
        }

        // Get required document types from kost configuration
        $requiredDocTypes = $rental->room->roomType->kost->documentRequirements
            ->where('is_required', true)
            ->pluck('document_type')
            ->toArray();

        if (empty($requiredDocTypes)) {
            // No documents required, should not happen in normal flow
            return;
        }

        // Query fresh from database to get latest verification statuses within transaction
        // Use DB query builder to ensure we're reading committed data within the transaction
        $approvedDocTypes = DB::table('rental_documents')
            ->where('rental_id', $rental->id)
            ->where('verification_status', 'approved')
            ->pluck('document_type')
            ->toArray();

        $allApproved = empty(array_diff($requiredDocTypes, $approvedDocTypes));

        if ($allApproved) {
            $this->confirmRental($rental);
        }
    }

    /**
     * Confirm rental and send confirmation email.
     *
     * @param  Rental  $rental
     */
    private function confirmRental($rental): void
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
