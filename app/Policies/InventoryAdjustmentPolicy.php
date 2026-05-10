<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\InventoryAdjustment;
use Illuminate\Auth\Access\HandlesAuthorization;

class InventoryAdjustmentPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:InventoryAdjustment');
    }

    public function view(AuthUser $authUser, InventoryAdjustment $inventoryAdjustment): bool
    {
        return $authUser->can('View:InventoryAdjustment');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:InventoryAdjustment');
    }

    public function update(AuthUser $authUser, InventoryAdjustment $inventoryAdjustment): bool
    {
        return $authUser->can('Update:InventoryAdjustment');
    }

    public function delete(AuthUser $authUser, InventoryAdjustment $inventoryAdjustment): bool
    {
        return $authUser->can('Delete:InventoryAdjustment');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:InventoryAdjustment');
    }

    public function restore(AuthUser $authUser, InventoryAdjustment $inventoryAdjustment): bool
    {
        return $authUser->can('Restore:InventoryAdjustment');
    }

    public function forceDelete(AuthUser $authUser, InventoryAdjustment $inventoryAdjustment): bool
    {
        return $authUser->can('ForceDelete:InventoryAdjustment');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:InventoryAdjustment');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:InventoryAdjustment');
    }

    public function replicate(AuthUser $authUser, InventoryAdjustment $inventoryAdjustment): bool
    {
        return $authUser->can('Replicate:InventoryAdjustment');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:InventoryAdjustment');
    }

}