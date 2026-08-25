<?php

declare(strict_types=1);

namespace App\Policies;

use App\Domain\Identity\Models\User;
use App\Domain\Kost\Models\Kost;
use App\Domain\Kost\Models\RoomType;

/**
 * Authorization policy for RoomType resource.
 *
 * Admin can manage room types for kosts they own.
 */
class RoomTypePolicy
{
    /**
     * Determine if admin can view any room types for this kost.
     *
     * @param  User  $user  The authenticated user
     * @param  Kost  $kost  The kost to view room types for
     */
    public function viewAny(User $user, Kost $kost): bool
    {
        return $user->role === 'admin' && $user->id === $kost->user_id;
    }

    /**
     * Determine if admin can view this room type.
     *
     * @param  User  $user  The authenticated user
     * @param  RoomType  $roomType  The room type being viewed
     */
    public function view(User $user, RoomType $roomType): bool
    {
        return $user->role === 'admin' && $user->id === $roomType->kost->user_id;
    }

    /**
     * Determine if admin can create room types for this kost.
     *
     * @param  User  $user  The authenticated user
     * @param  Kost  $kost  The kost to create room type for
     */
    public function create(User $user, Kost $kost): bool
    {
        return $user->role === 'admin' && $user->id === $kost->user_id;
    }

    /**
     * Determine if admin can update this room type.
     *
     * @param  User  $user  The authenticated user
     * @param  RoomType  $roomType  The room type being updated
     */
    public function update(User $user, RoomType $roomType): bool
    {
        return $user->role === 'admin' && $user->id === $roomType->kost->user_id;
    }

    /**
     * Determine if admin can delete this room type.
     *
     * @param  User  $user  The authenticated user
     * @param  RoomType  $roomType  The room type being deleted
     */
    public function delete(User $user, RoomType $roomType): bool
    {
        return $user->role === 'admin' && $user->id === $roomType->kost->user_id;
    }
}
