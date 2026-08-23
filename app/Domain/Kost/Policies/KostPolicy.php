<?php

declare(strict_types=1);

namespace App\Domain\Kost\Policies;

use App\Domain\Identity\Models\User;
use App\Domain\Kost\Models\Kost;

/**
 * Authorization policy for Kost resource.
 *
 * Admin can CRUD their own kost (ownership check).
 * Super Admin can approve/reject submissions.
 */
class KostPolicy
{
    /**
     * Determine if user can view any kosts.
     *
     * Admin: view their own kosts only
     * Super Admin: view all submissions
     *
     * @param  User  $user  The authenticated user.
     */
    public function viewAny(User $user): bool
    {
        return $user->isAdmin() || $user->isSuperAdmin();
    }

    /**
     * Determine if user can view the kost.
     *
     * Admin: view if owner
     * Super Admin: view all
     *
     * @param  User  $user  The authenticated user.
     * @param  Kost  $kost  The kost being viewed.
     */
    public function view(User $user, Kost $kost): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        return $user->isAdmin() && $kost->user_id === $user->id;
    }

    /**
     * Determine if user can create kosts.
     *
     * Only Admin can create kosts.
     *
     * @param  User  $user  The authenticated user.
     */
    public function create(User $user): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine if user can update the kost.
     *
     * Admin: can update if owner AND status is draft/rejected
     * Super Admin: cannot update (not owner)
     *
     * @param  User  $user  The authenticated user.
     * @param  Kost  $kost  The kost being updated.
     */
    public function update(User $user, Kost $kost): bool
    {
        if (! $user->isAdmin()) {
            return false;
        }

        if ($kost->user_id !== $user->id) {
            return false;
        }

        return $kost->isDraft() || $kost->isRejected();
    }

    /**
     * Determine if user can submit kost for review.
     *
     * Admin: can submit if owner AND status is draft
     *
     * @param  User  $user  The authenticated user.
     * @param  Kost  $kost  The kost being submitted.
     */
    public function submit(User $user, Kost $kost): bool
    {
        if (! $user->isAdmin()) {
            return false;
        }

        if ($kost->user_id !== $user->id) {
            return false;
        }

        return $kost->isDraft();
    }

    /**
     * Determine if user can approve kost submission.
     *
     * Only Super Admin can approve, and status must be pending_review.
     *
     * @param  User  $user  The authenticated user.
     * @param  Kost  $kost  The kost being approved.
     */
    public function approve(User $user, Kost $kost): bool
    {
        if (! $user->isSuperAdmin()) {
            return false;
        }

        return $kost->isPendingReview();
    }

    /**
     * Determine if user can reject kost submission.
     *
     * Only Super Admin can reject, and status must be pending_review.
     *
     * @param  User  $user  The authenticated user.
     * @param  Kost  $kost  The kost being rejected.
     */
    public function reject(User $user, Kost $kost): bool
    {
        if (! $user->isSuperAdmin()) {
            return false;
        }

        return $kost->isPendingReview();
    }

    /**
     * Determine if user can publish kost.
     *
     * Admin: can publish if owner AND status is approved
     *
     * @param  User  $user  The authenticated user.
     * @param  Kost  $kost  The kost being published.
     */
    public function publish(User $user, Kost $kost): bool
    {
        if (! $user->isAdmin()) {
            return false;
        }

        if ($kost->user_id !== $user->id) {
            return false;
        }

        return $kost->isApproved();
    }

    /**
     * Determine if user can cancel kost submission.
     *
     * Admin: can cancel if owner AND status is pending_review
     *
     * @param  User  $user  The authenticated user.
     * @param  Kost  $kost  The kost being cancelled.
     */
    public function cancel(User $user, Kost $kost): bool
    {
        if (! $user->isAdmin()) {
            return false;
        }

        if ($kost->user_id !== $user->id) {
            return false;
        }

        return $kost->isPendingReview();
    }

    /**
     * Determine if user can delete the kost.
     *
     * Admin: can delete if owner AND status is draft/rejected
     * (soft delete only, see ARCHITECTURE.md ADR-009)
     *
     * @param  User  $user  The authenticated user.
     * @param  Kost  $kost  The kost being deleted.
     */
    public function delete(User $user, Kost $kost): bool
    {
        if (! $user->isAdmin()) {
            return false;
        }

        if ($kost->user_id !== $user->id) {
            return false;
        }

        return $kost->isDraft() || $kost->isRejected();
    }

    /**
     * Determine if user can restore soft-deleted kost.
     *
     * @param  User  $user  The authenticated user.
     * @param  Kost  $kost  The kost being restored.
     */
    public function restore(User $user, Kost $kost): bool
    {
        return $user->isAdmin() && $kost->user_id === $user->id;
    }

    /**
     * Determine if user can permanently delete the kost.
     *
     * No one can force delete (preserve historical data).
     *
     * @param  User  $user  The authenticated user.
     * @param  Kost  $kost  The kost being force deleted.
     */
    public function forceDelete(User $user, Kost $kost): bool
    {
        return false; // Hard delete disabled per ADR-009
    }
}
