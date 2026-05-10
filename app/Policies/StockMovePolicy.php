<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\StockMove;
use Illuminate\Auth\Access\HandlesAuthorization;

class StockMovePolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:StockMove');
    }

    public function view(AuthUser $authUser, StockMove $stockMove): bool
    {
        return $authUser->can('View:StockMove');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:StockMove');
    }

    public function update(AuthUser $authUser, StockMove $stockMove): bool
    {
        return $authUser->can('Update:StockMove');
    }

    public function delete(AuthUser $authUser, StockMove $stockMove): bool
    {
        return $authUser->can('Delete:StockMove');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:StockMove');
    }

    public function restore(AuthUser $authUser, StockMove $stockMove): bool
    {
        return $authUser->can('Restore:StockMove');
    }

    public function forceDelete(AuthUser $authUser, StockMove $stockMove): bool
    {
        return $authUser->can('ForceDelete:StockMove');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:StockMove');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:StockMove');
    }

    public function replicate(AuthUser $authUser, StockMove $stockMove): bool
    {
        return $authUser->can('Replicate:StockMove');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:StockMove');
    }

}