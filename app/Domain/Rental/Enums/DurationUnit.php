<?php

declare(strict_types=1);

namespace App\Domain\Rental\Enums;

enum DurationUnit: string
{
    case DAY = 'day';
    case WEEK = 'week';
    case MONTH = 'month';

    /**
     * Get the Indonesian label for the duration unit.
     */
    public function label(): string
    {
        return match ($this) {
            self::DAY => 'hari',
            self::WEEK => 'minggu',
            self::MONTH => 'bulan',
        };
    }
}
