<?php

declare(strict_types=1);

namespace App\Domain\Rental\Exceptions;

use Exception;

class InvalidDocumentTypeException extends Exception
{
    public static function notRequired(string $documentType): self
    {
        return new self("Tipe dokumen '{$documentType}' tidak diperlukan untuk kost ini.");
    }

    public static function notFound(string $documentType): self
    {
        return new self("Tipe dokumen '{$documentType}' tidak ditemukan.");
    }
}
