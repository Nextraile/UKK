<?php

declare(strict_types=1);

namespace App\Domain\Rental\Exceptions;

use Exception;

class DocumentAlreadyVerifiedException extends Exception
{
    public function __construct(string $message = '')
    {
        parent::__construct($message ?: 'Dokumen sudah diverifikasi sebelumnya.');
    }
}
