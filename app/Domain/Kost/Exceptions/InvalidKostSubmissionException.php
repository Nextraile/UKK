<?php

declare(strict_types=1);

namespace App\Domain\Kost\Exceptions;

use Exception;

/**
 * Exception thrown when kost submission validation fails.
 */
class InvalidKostSubmissionException extends Exception
{
    /**
     * Create exception for missing required data.
     *
     * @param  array<int, string>  $missingFields
     */
    public static function missingRequiredData(array $missingFields): self
    {
        $fields = implode(', ', $missingFields);

        return new self("Data kost belum lengkap. Harap lengkapi: {$fields}");
    }

    /**
     * Create exception for invalid status.
     */
    public static function invalidStatus(string $currentStatus): self
    {
        return new self("Hanya kost dengan status 'draft' yang dapat disubmit. Status saat ini: {$currentStatus}");
    }
}
