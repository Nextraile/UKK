<?php

declare(strict_types=1);

namespace App\Policies;

use App\Domain\Identity\Models\User;
use App\Domain\Kost\Models\PriceScheme;
use App\Domain\Kost\Models\RoomType;

class PriceSchemePolicy
{
    /**
     * Determine if admin can view any price schemes for this room type.
     */
    public function viewAny(User $user, RoomType $roomType): bool
    {
        $roomType->loadMissing('kost');

        return $user->role === 'admin' && $user->id === $roomType->kost->user_id;
    }

    /**
     * Determine if admin can create price schemes for this room type.
     */
    public function create(User $user, RoomType $roomType): bool
    {
        $roomType->loadMissing('kost');

        return $user->role === 'admin' && $user->id === $roomType->kost->user_id;
    }

    /**
     * Determine if admin can update this price scheme.
     */
    public function update(User $user, PriceScheme $priceScheme): bool
    {
        $priceScheme->loadMissing('roomType.kost');

        return $user->role === 'admin' && $user->id === $priceScheme->roomType->kost->user_id;
    }

    /**
     * Determine if admin can delete this price scheme.
     */
    public function delete(User $user, PriceScheme $priceScheme): bool
    {
        $priceScheme->loadMissing('roomType.kost');

        return $user->role === 'admin' && $user->id === $priceScheme->roomType->kost->user_id;
    }
}
