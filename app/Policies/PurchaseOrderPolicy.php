<?php

namespace App\Policies;

use App\Models\PurchaseOrder;
use App\Models\User;

class PurchaseOrderPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->isAdmin() || $user->isChecker() || $user->isSupplier();
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, PurchaseOrder $purchaseOrder): bool
    {
        return $user->isAdmin() || $user->isChecker() || ($user->isSupplier() && $purchaseOrder->supplier_id === $user->supplier_id);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->isAdmin() || $user->isChecker();
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, PurchaseOrder $purchaseOrder): bool
    {
        return ($user->isAdmin() || $user->isChecker()) && ($purchaseOrder->status === \App\Enums\PurchaseOrderStatus::PENDING);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, PurchaseOrder $purchaseOrder): bool
    {
        return ($user->isAdmin() || $user->isChecker()) && ($purchaseOrder->status === \App\Enums\PurchaseOrderStatus::PENDING);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, PurchaseOrder $purchaseOrder): bool
    {
        return $user->isAdmin() || $user->isChecker();
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, PurchaseOrder $purchaseOrder): bool
    {
        return ($user->isAdmin() || $user->isChecker()) && ($purchaseOrder->status === \App\Enums\PurchaseOrderStatus::PENDING);
    }
}
