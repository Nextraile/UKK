<?php

declare(strict_types=1);

namespace App\Domain\Kost\Policies;

use App\Domain\Identity\Models\User;
use App\Domain\Kost\Models\KostDocumentRequirement;

/**
 * Authorization policy for KostDocumentRequirement resource.
 *
 * Document requirement operations follow Kost ownership rules:
 * - Admin can manage requirements for their own kosts
 * - Only in draft/rejected status (same as Kost update policy)
 */
class KostDocumentRequirementPolicy
{
    /**
     * Determine if user can view the document requirement.
     *
     * Admin: can view if kost owner
     * Super Admin: can view all
     *
     * @param  User  $user  The authenticated user
     * @param  KostDocumentRequirement  $requirement  The requirement being viewed
     */
    public function view(User $user, KostDocumentRequirement $requirement): bool
    {
        $kost = $requirement->kost;

        if ($user->isSuperAdmin()) {
            return true;
        }

        return $user->isAdmin() && $kost->user_id === $user->id;
    }

    /**
     * Determine if user can update the document requirement.
     *
     * Admin: can update if kost owner AND status is draft/rejected
     *
     * @param  User  $user  The authenticated user
     * @param  KostDocumentRequirement  $requirement  The requirement being updated
     */
    public function update(User $user, KostDocumentRequirement $requirement): bool
    {
        $kost = $requirement->kost;

        if (! $user->isAdmin()) {
            return false;
        }

        if ($kost->user_id !== $user->id) {
            return false;
        }

        return $kost->isDraft() || $kost->isRejected();
    }

    /**
     * Determine if user can delete the document requirement.
     *
     * Admin: can delete if kost owner AND status is draft/rejected
     *
     * @param  User  $user  The authenticated user
     * @param  KostDocumentRequirement  $requirement  The requirement being deleted
     */
    public function delete(User $user, KostDocumentRequirement $requirement): bool
    {
        $kost = $requirement->kost;

        if (! $user->isAdmin()) {
            return false;
        }

        if ($kost->user_id !== $user->id) {
            return false;
        }

        return $kost->isDraft() || $kost->isRejected();
    }
}
