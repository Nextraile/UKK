<?php

declare(strict_types=1);

namespace App\Domain\Kost\Actions;

use App\Domain\Kost\Exceptions\InvalidKostSubmissionException;
use App\Domain\Kost\Mail\KostSubmittedMail;
use App\Domain\Kost\Models\Kost;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

/**
 * Action to submit kost for Super Admin review.
 *
 * Validates completeness before transitioning Draft → Pending Review.
 * FR-016, FR-017: Data wajib lengkap (nama, alamat, kategori, ≥1 room type)
 */
class SubmitKostForReview
{
    /**
     * Execute the action.
     *
     * @param  Kost  $kost  The kost being submitted
     *
     * @throws InvalidKostSubmissionException If validation fails
     */
    public function execute(Kost $kost): void
    {
        if (! $kost->isDraft()) {
            throw InvalidKostSubmissionException::invalidStatus($kost->status);
        }

        $this->validateRequiredData($kost);

        DB::transaction(function () use ($kost) {
            $kost->status = 'pending_review';
            $kost->save();

            // Send notification to Super Admin
            $superAdminEmail = config('mail.super_admin_email');
            if ($superAdminEmail) {
                Mail::to($superAdminEmail)->send(new KostSubmittedMail($kost));
            }
        });
    }

    /**
     * Validate kost has all required data before submission.
     *
     * @param  Kost  $kost  The kost being validated
     *
     * @throws InvalidKostSubmissionException If required data is missing
     */
    private function validateRequiredData(Kost $kost): void
    {
        $missingFields = [];

        if (empty($kost->name)) {
            $missingFields[] = 'Nama kost';
        }

        // Check 1:1 address relationship
        if (! $kost->address()->exists()) {
            $missingFields[] = 'Alamat kost';
        }

        // Check M:N categories relationship (≥1 required)
        if ($kost->categories()->count() === 0) {
            $missingFields[] = 'Kategori kost (minimal 1)';
        }

        // Check 1:N room types relationship (≥1 required)
        if ($kost->roomTypes()->count() === 0) {
            $missingFields[] = 'Tipe kamar (minimal 1)';
        }

        // COMP-003: QRIS wajib untuk submit
        if (empty($kost->qris_image_path)) {
            $missingFields[] = 'Gambar QRIS pembayaran';
        }

        // COMP-003: Document requirements ≥1 wajib
        if ($kost->documentRequirements()->count() === 0) {
            $missingFields[] = 'Persyaratan dokumen penyewa (minimal 1)';
        }

        // Kost images optional (not blocking submit)

        if (! empty($missingFields)) {
            throw InvalidKostSubmissionException::missingRequiredData($missingFields);
        }
    }
}
