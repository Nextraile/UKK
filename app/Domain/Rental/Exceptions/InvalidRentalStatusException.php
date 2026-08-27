<?php

declare(strict_types=1);

namespace App\Domain\Rental\Exceptions;

use App\Domain\Rental\Models\Rental;
use Exception;

class InvalidRentalStatusException extends Exception
{
    public static function cannotUploadDocument(Rental $rental): self
    {
        return new self("Dokumen hanya dapat diupload untuk rental dengan status paid atau documents_pending. Status saat ini: {$rental->status}");
    }

    public static function cannotVerifyDocument(Rental $rental): self
    {
        return new self("Dokumen hanya dapat diverifikasi untuk rental dengan status documents_pending atau confirmed. Status saat ini: {$rental->status}");
    }
}
