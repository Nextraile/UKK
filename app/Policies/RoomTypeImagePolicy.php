<?php

declare(strict_types=1);

namespace App\Policies;

use App\Domain\Identity\Models\User;
use App\Domain\Kost\Models\RoomType;
use App\Domain\Kost\Models\RoomTypeImage;

/**
 * Authorization policy for RoomTypeImage resource.
 *
 * RoomTypeImage operations follow RoomType ownership rules:
 * - Admin can manage images for their own room types
 * - Ownership is determined by the parent Kost
 */
class RoomTypeImagePolicy
{
    /**
     * Determine if admin can create images for this room type.
     *
     * Admin: can upload if kost owner
     *
     * Note: Caller should eager load 'kost' relationship to avoid N+1
     *
     * @param  User  $user  The authenticated user
     * @param  RoomType  $roomType  The room type to upload image for
     */
    public function create(User $user, RoomType $roomType): bool
    {
        if ($user->role !== 'admin') {
            return false;
        }

        return $roomType->kost->user_id === $user->id;
    }

    /**
     * Determine if admin can delete this room type image.
     *
     * Admin: can delete if kost owner
     *
     * Note: Caller should eager load 'roomType.kost' relationship to avoid N+1
     *
     * @param  User  $user  The authenticated user
     * @param  RoomTypeImage  $roomTypeImage  The image to delete
     */
    public function delete(User $user, RoomTypeImage $roomTypeImage): bool
    {
        if ($user->role !== 'admin') {
            return false;
        }

        return $roomTypeImage->roomType->kost->user_id === $user->id;
    }
}
