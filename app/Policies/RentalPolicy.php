<?php

declare(strict_types=1);

namespace App\Policies;

use App\Domain\Identity\Models\User;
use App\Domain\Rental\Models\Rental;

class RentalPolicy
{
    /**
     * Determine if user can view rental list.
     *
     * Any authenticated user can view their own rental list.
     */
    public function viewAny(User $user): bool
    {
        return $user->role === 'user';
    }

    /**
     * Determine if user can view the rental.
     *
     * Tenant can only view own rentals.
     */
    public function view(User $user, Rental $rental): bool
    {
        return $user->id === $rental->user_id;
    }

    /**
     * Determine if admin can view rental list.
     *
     * Any admin can access rental management.
     */
    public function viewAnyAsAdmin(User $user): bool
    {
        return $user->role === 'admin';
    }

    /**
     * Determine if admin can view rental.
     *
     * Admin can view if they own the kost.
     */
    public function viewAsAdmin(User $user, Rental $rental): bool
    {
        if ($user->role !== 'admin') {
            return false;
        }

        return $rental->room->roomType->kost->user_id === $user->id;
    }

    /**
     * Determine if user can upload payment proof for rental.
     *
     * Only rental owner (tenant) can upload payment proof.
     * Only allowed in pending status.
     */
    public function uploadPayment(User $user, Rental $rental): bool
    {
        // Only rental owner can upload
        if ($rental->user_id !== $user->id) {
            return false;
        }

        // Only allow upload if rental pending
        return $rental->status === 'pending';
    }

    /**
     * Determine if user can upload document for rental.
     *
     * Only rental owner (tenant) can upload documents.
     * Only allowed in paid or documents_pending status.
     */
    public function uploadDocument(User $user, Rental $rental): bool
    {
        // Only rental owner (tenant) can upload
        if ($rental->user_id !== $user->id) {
            return false;
        }

        // Only allow upload in paid or documents_pending status
        return in_array($rental->status, ['paid', 'documents_pending']);
    }

    /**
     * Determine if user can update rental (for document management).
     *
     * Same as uploadDocument - allows tenant to manage documents.
     */
    public function update(User $user, Rental $rental): bool
    {
        return $this->uploadDocument($user, $rental);
    }

    /**
     * Determine if admin can verify document for rental.
     *
     * Admin can verify if they own the kost.
     */
    public function verifyDocument(User $user, Rental $rental): bool
    {
        // Only admin role can verify
        if ($user->role !== 'admin') {
            return false;
        }

        // Only verify documents for rentals in admin's kost
        return $rental->room->roomType->kost->user_id === $user->id;
    }

    /**
     * Determine if user can cancel the rental.
     *
     * FR-094: Tenant can cancel rental before start_date
     */
    public function cancel(User $user, Rental $rental): bool
    {
        // Only rental owner (tenant) can cancel
        if ($rental->user_id !== $user->id) {
            return false;
        }

        // Cannot cancel after start_date
        if ($rental->start_date->isPast()) {
            return false;
        }

        // Can only cancel in specific statuses
        $cancellableStatuses = ['pending', 'paid', 'documents_pending', 'confirmed'];

        return in_array($rental->status, $cancellableStatuses);
    }
}
