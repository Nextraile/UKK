<?php

declare(strict_types=1);

namespace App\Domain\Rental\Exceptions;

use App\Domain\Kost\Models\Room;
use Exception;

class RoomFullException extends Exception
{
    public static function noCapacity(Room $room): self
    {
        return new self(
            "Kamar {$room->code} sudah penuh. Tidak ada slot tersedia."
        );
    }
}
