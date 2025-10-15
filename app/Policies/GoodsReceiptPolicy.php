<?php

namespace App\Policies;

use App\Models\GoodsReceipt;
use App\Models\User;

class GoodsReceiptPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->isChecker() || $user->isAdmin();
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, GoodsReceipt $goodReceipt): bool
    {
        return $user->isChecker() || $user->isAdmin();
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->isChecker() || $user->isAdmin();
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, GoodsReceipt $goodReceipt): bool
    {
        return $user->isChecker() || $user->isAdmin() && ($goodReceipt->status === \App\Enums\GoodsReceiptStatus::PENDING);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, GoodsReceipt $goodReceipt): bool
    {
        return $user->isChecker()
        || $user->isAdmin()
        && (($goodReceipt->status === \App\Enums\GoodsReceiptStatus::PENDING));
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, GoodsReceipt $goodReceipt): bool
    {
        return $user->isChecker() || $user->isAdmin();
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, GoodsReceipt $goodReceipt): bool
    {
        return $user->isChecker() || $user->isAdmin() && ($goodReceipt->status === \App\Enums\GoodsReceiptStatus::PENDING && $goodReceipt->status === \App\Enums\GoodsReceiptStatus::COMPLETED);
    }
}
