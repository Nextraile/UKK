<?php

declare(strict_types=1);

namespace App\Domain\Kost\Exceptions;

use App\Domain\Kost\Models\Kost;
use Exception;

/**
 * Exception thrown when kost state transition is invalid.
 */
class InvalidKostTransitionException extends Exception
{
    /**
     * Create exception for invalid approval attempt.
     */
    public static function cannotApprove(Kost $kost): self
    {
        return new self(
            "Cannot approve kost #{$kost->id}: current status is '{$kost->status}', expected 'pending_review'"
        );
    }

    /**
     * Create exception for invalid rejection attempt.
     */
    public static function cannotReject(Kost $kost): self
    {
        return new self(
            "Cannot reject kost #{$kost->id}: current status is '{$kost->status}', expected 'pending_review'"
        );
    }

    /**
     * Create exception for invalid publish attempt.
     */
    public static function cannotPublish(Kost $kost): self
    {
        return new self(
            "Cannot publish kost #{$kost->id}: current status is '{$kost->status}', expected 'approved'"
        );
    }

    /**
     * Create exception for invalid cancel attempt.
     */
    public static function cannotCancel(Kost $kost): self
    {
        return new self(
            "Cannot cancel kost submission. Current status: {$kost->status}. Only pending_review submissions can be cancelled."
        );
    }
}
