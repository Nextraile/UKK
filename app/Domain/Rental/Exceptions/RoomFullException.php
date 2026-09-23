<?php

declare(strict_types=1);

namespace App\Domain\Rental\Exceptions;

use App\Domain\RoomInventory\Models\Room;
use Carbon\Carbon;
use Exception;

class RoomFullException extends Exception
{
    public static function noCapacity(Room $room): self
    {
        return new self(
            "Kamar {$room->code} sudah penuh. Tidak ada slot tersedia."
        );
    }

    /**
     * Exception for room full during a specific date period.
     */
    public static function noCapacityForPeriod(Room $room, Carbon $startDate, Carbon $endDate): self
    {
        return new self(
            "Kamar {$room->code} penuh untuk periode ".
            $startDate->format('d M Y').' - '.$endDate->format('d M Y').
            '. Silakan pilih tanggal lain atau kamar lain.'
        );
    }
}
