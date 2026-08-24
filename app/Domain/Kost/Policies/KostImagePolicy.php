<?php

declare(strict_types=1);

namespace App\Domain\Kost\Policies;

use App\Domain\Identity\Models\User;
use App\Domain\Kost\Models\Kost;
use App\Domain\Kost\Models\KostImage;

/**
 * Authorization policy for KostImage resource.
 *
 * KostImage operations follow Kost ownership rules:
 * - Admin can manage images for their own kosts
 * - Only in draft/rejected status (same as Kost update policy)
 */
class KostImagePolicy
{
    /**
     * Determine if user can upload images for the kost.
     *
     * Admin: can upload if kost owner AND status is draft/rejected
     *
     * @param  User  $user  The authenticated user
     * @param  Kost  $kost  The kost to upload image for
     */
    public function upload(User $user, Kost $kost): bool
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
     * Determine if user can delete the image.
     *
     * Admin: can delete if kost owner AND status is draft/rejected
     *
     * @param  User  $user  The authenticated user
     * @param  KostImage  $image  The image to delete
     */
    public function delete(User $user, KostImage $image): bool
    {
        $kost = $image->kost;

        if (! $user->isAdmin()) {
            return false;
        }

        if ($kost->user_id !== $user->id) {
            return false;
        }

        return $kost->isDraft() || $kost->isRejected();
    }

    /**
     * Determine if user can set thumbnail.
     *
     * Admin: can set thumbnail if kost owner AND status is draft/rejected
     *
     * @param  User  $user  The authenticated user
     * @param  KostImage  $image  The image to set as thumbnail
     */
    public function setThumbnail(User $user, KostImage $image): bool
    {
        $kost = $image->kost;

        if (! $user->isAdmin()) {
            return false;
        }

        if ($kost->user_id !== $user->id) {
            return false;
        }

        return $kost->isDraft() || $kost->isRejected();
    }

    /**
     * Determine if user can update sort order.
     *
     * Admin: can reorder if kost owner AND status is draft/rejected
     *
     * @param  User  $user  The authenticated user
     * @param  Kost  $kost  The kost owning the images
     */
    public function updateSortOrder(User $user, Kost $kost): bool
    {
        if (! $user->isAdmin()) {
            return false;
        }

        if ($kost->user_id !== $user->id) {
            return false;
        }

        return $kost->isDraft() || $kost->isRejected();
    }
}
