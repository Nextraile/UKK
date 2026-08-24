<?php

declare(strict_types=1);

namespace App\Domain\Kost\Policies;

use App\Domain\Identity\Models\User;
use App\Domain\Kost\Models\Category;

/**
 * Authorization policy for Category resource.
 *
 * Only SuperAdmin can manage categories.
 * Admin can only assign categories to kosts (handled in KostPolicy).
 */
class CategoryPolicy
{
    /**
     * Determine if user can view any categories.
     *
     * @param  User  $user  The authenticated user.
     */
    public function viewAny(User $user): bool
    {
        return $user->isSuperAdmin();
    }

    /**
     * Determine if user can view the category.
     *
     * @param  User  $user  The authenticated user.
     * @param  Category  $category  The category being viewed.
     */
    public function view(User $user, Category $category): bool
    {
        return $user->isSuperAdmin();
    }

    /**
     * Determine if user can create categories.
     *
     * @param  User  $user  The authenticated user.
     */
    public function create(User $user): bool
    {
        return $user->isSuperAdmin();
    }

    /**
     * Determine if user can update the category.
     *
     * @param  User  $user  The authenticated user.
     * @param  Category  $category  The category being updated.
     */
    public function update(User $user, Category $category): bool
    {
        return $user->isSuperAdmin();
    }

    /**
     * Determine if user can delete the category.
     *
     * @param  User  $user  The authenticated user.
     * @param  Category  $category  The category being deleted.
     */
    public function delete(User $user, Category $category): bool
    {
        return $user->isSuperAdmin();
    }
}
