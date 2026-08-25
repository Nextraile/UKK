<?php

declare(strict_types=1);

namespace App\Policies;

use App\Domain\Identity\Models\User;
use App\Domain\Kost\Models\Kost;
use App\Domain\Kost\Models\Room;

class RoomPolicy
{
    /**
     * Determine if admin can view any rooms for this kost.
     */
    public function viewAny(User $user, Kost $kost): bool
    {
        return $user->role === 'admin' && $user->id === $kost->user_id;
    }

    /**
     * Determine if admin can view this room.
     */
    public function view(User $user, Room $room): bool
    {
        return $user->role === 'admin' && $user->id === $room->kost->user_id;
    }

    /**
     * Determine if admin can create rooms for this kost.
     */
    public function create(User $user, Kost $kost): bool
    {
        return $user->role === 'admin' && $user->id === $kost->user_id;
    }

    /**
     * Determine if admin can update this room.
     */
    public function update(User $user, Room $room): bool
    {
        return $user->role === 'admin' && $user->id === $room->kost->user_id;
    }

    /**
     * Determine if admin can delete this room.
     */
    public function delete(User $user, Room $room): bool
    {
        return $user->role === 'admin' && $user->id === $room->kost->user_id;
    }

    /**
     * Determine if admin can set room to unavailable.
     *
     * FR-046: Room can only be set unavailable if no active/reserved rentals exist.
     *
     * TODO: COMP-006 - Implement proper validation after Rental model exists.
     * Current: Stub always returns true (no validation until rentals exist).
     */
    public function setUnavailable(User $user, Room $room): bool
    {
        if ($user->role !== 'admin' || $user->id !== $room->kost->user_id) {
            return false;
        }

        // TODO: COMP-006 - Add validation: return $room->used_slots === 0;
        return true; // Stub: always allow until COMP-006
    }
}
