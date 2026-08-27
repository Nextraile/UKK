<?php

declare(strict_types=1);

namespace App\Domain\Rental\Exceptions;

use Exception;

class InvalidRentalDataException extends Exception
{
    public static function invalidStartDate(string $reason): self
    {
        return new self("Tanggal mulai tidak valid: {$reason}");
    }

    public static function invalidDuration(string $reason): self
    {
        return new self("Durasi tidak valid: {$reason}");
    }
}
