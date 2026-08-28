<?php

declare(strict_types=1);

namespace App\Policies;

use App\Domain\Identity\Models\User;
use App\Domain\Rental\Models\Rental;
use App\Domain\Review\Models\Review;

class ReviewPolicy
{
    /**
     * Determine if the user can create a review for the rental.
     *
     * Authorization rules:
     * - User must be authenticated
     * - User must be the tenant (owner) of the rental
     * - Rental must be in 'completed' status
     * - Rental must not already have a review
     */
    public function create(User $user, Rental $rental): bool
    {
        // Must be the rental owner
        if ($user->id !== $rental->user_id) {
            return false;
        }

        // Rental must be completed
        if ($rental->status !== 'completed') {
            return false;
        }

        // Must not already have a review
        if ($rental->review()->exists()) {
            return false;
        }

        return true;
    }

    /**
     * Determine if the user can update the review.
     *
     * Authorization rules:
     * - User must be authenticated
     * - User must be the author of the review (via rental ownership)
     */
    public function update(User $user, Review $review): bool
    {
        // Must be the review author (via rental ownership)
        return $user->id === $review->rental->user_id;
    }

    /**
     * Determine if the user can delete the review.
     *
     * Authorization rules:
     * - User must be authenticated
     * - User must be the author of the review (via rental ownership)
     */
    public function delete(User $user, Review $review): bool
    {
        // Must be the review author (via rental ownership)
        return $user->id === $review->rental->user_id;
    }
}
