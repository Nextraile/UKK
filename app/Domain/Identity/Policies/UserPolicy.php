<?php

declare(strict_types=1);

namespace App\Domain\Identity\Policies;

use App\Domain\Identity\Models\User;

/**
 * Authorization policy for the User model.
 *
 * Governs who can view, create, update, and delete user accounts.
 * Only SuperAdmin can manage admin accounts. Regular users can only
 * manage their own profile.
 */
class UserPolicy
{
    /**
     * Determine if the user can view any models (admin list).
     *
     * Only SuperAdmin can manage admin accounts.
     *
     * @param  User  $user  The authenticated user.
     */
    public function viewAny(User $user): bool
    {
        return $user->isSuperAdmin();
    }

    /**
     * Determine if the user can view the model.
     *
     * Users can view their own profile. SuperAdmin can view any.
     *
     * @param  User  $user  The authenticated user.
     * @param  User  $model  The user being viewed.
     */
    public function view(User $user, User $model): bool
    {
        return $user->isSuperAdmin() || $user->id === $model->id;
    }

    /**
     * Determine if the user can create models (admin accounts).
     *
     * Only SuperAdmin can create Admin accounts.
     *
     * @param  User  $user  The authenticated user.
     */
    public function create(User $user): bool
    {
        return $user->isSuperAdmin();
    }

    /**
     * Determine if the user can update the model.
     *
     * Users can update their own profile. SuperAdmin can update any.
     *
     * @param  User  $user  The authenticated user.
     * @param  User  $model  The user being updated.
     */
    public function update(User $user, User $model): bool
    {
        return $user->isSuperAdmin() || $user->id === $model->id;
    }

    /**
     * Determine if the user can delete the model.
     *
     * Users can soft-delete their own account. SuperAdmin can delete Admin accounts.
     *
     * @param  User  $user  The authenticated user.
     * @param  User  $model  The user being deleted.
     */
    public function delete(User $user, User $model): bool
    {
        return $user->isSuperAdmin() || $user->id === $model->id;
    }
}
