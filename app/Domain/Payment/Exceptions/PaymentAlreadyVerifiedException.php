<?php

declare(strict_types=1);

namespace App\Domain\Payment\Exceptions;

use Exception;

class PaymentAlreadyVerifiedException extends Exception
{
    public function __construct(string $message = '')
    {
        parent::__construct($message ?: 'Pembayaran sudah diverifikasi sebelumnya.');
    }
}
