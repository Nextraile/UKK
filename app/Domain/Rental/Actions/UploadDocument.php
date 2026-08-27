<?php

declare(strict_types=1);

namespace App\Domain\Rental\Actions;

use App\Domain\Rental\Exceptions\InvalidDocumentTypeException;
use App\Domain\Rental\Exceptions\InvalidRentalStatusException;
use App\Domain\Rental\Models\Rental;
use App\Domain\Rental\Models\RentalDocument;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

/**
 * Upload or replace rental document.
 *
 * Handles document upload for verified rentals (paid/documents_pending status).
 * If document already exists for the type, replaces it and resets verification.
 *
 * FR-086, FR-087
 */
class UploadDocument
{
    /**
     * Execute document upload.
     *
     * @param  Rental  $rental  The rental to upload document for
     * @param  string  $documentType  Type of document (e.g., 'KTP', 'Passport')
     * @param  UploadedFile  $file  The uploaded file
     * @return RentalDocument Created or updated document record
     *
     * @throws InvalidRentalStatusException If rental status not paid/documents_pending
     * @throws InvalidDocumentTypeException If document type not required by kost
     */
    public function execute(Rental $rental, string $documentType, UploadedFile $file): RentalDocument
    {
        // Guard: Check rental status (must be paid or documents_pending)
        if (! in_array($rental->status, ['paid', 'documents_pending'])) {
            throw InvalidRentalStatusException::cannotUploadDocument($rental);
        }

        // Guard: Validate document_type exists in kost requirements
        $requiredDocs = $rental->room->roomType->kost->documentRequirements()
            ->where('is_required', true)
            ->pluck('document_type')
            ->toArray();

        if (! in_array($documentType, $requiredDocs)) {
            throw InvalidDocumentTypeException::notRequired($documentType);
        }

        // Find existing document of same type for this rental
        $existingDoc = $rental->rentalDocuments()
            ->where('document_type', $documentType)
            ->first();

        if ($existingDoc) {
            // Delete old file if exists
            // @phpstan-ignore-next-line (document_path always set on RentalDocument)
            if ($existingDoc->document_path && Storage::disk('public')->exists($existingDoc->document_path)) {
                Storage::disk('public')->delete($existingDoc->document_path);
            }

            // Reset verification status (allow re-upload)
            $existingDoc->update([
                'document_path' => $file->store('rental-documents', 'public'),
                'uploaded_at' => now(),
                'verification_status' => 'pending',
                'rejection_reason' => null,
                'verified_at' => null,
                'verified_by' => null,
            ]);

            return $existingDoc->fresh() ?? $existingDoc;
        }

        // Create new document record
        $document = $rental->rentalDocuments()->create([
            'document_type' => $documentType,
            'document_path' => $file->store('rental-documents', 'public'),
            'uploaded_at' => now(),
            'verification_status' => 'pending',
        ]);

        // Transition rental to documents_pending if still in paid status
        if ($rental->status === 'paid') {
            $rental->update(['status' => 'documents_pending']);
            $rental->statusHistories()->create([
                'status' => 'documents_pending',
                'changed_by' => auth()->id(),
                'internal_notes' => 'First document uploaded',
            ]);
        }

        /** @var RentalDocument $document */
        return $document;
    }
}
