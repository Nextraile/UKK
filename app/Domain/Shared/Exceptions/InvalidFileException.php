<?php

declare(strict_types=1);

namespace App\Domain\Shared\Exceptions;

use RuntimeException;

/**
 * Exception thrown when a file fails security validation.
 *
 * This exception is raised when uploaded files don't meet security
 * requirements such as MIME type validation, size limits, dimension
 * requirements, or contain malicious content.
 */
class InvalidFileException extends RuntimeException
{
    /**
     * Create a new invalid file exception.
     *
     * @param  string  $message  The error message describing why the file is invalid
     */
    public function __construct(string $message = 'File tidak valid atau tidak memenuhi persyaratan keamanan.')
    {
        parent::__construct($message);
    }
}
