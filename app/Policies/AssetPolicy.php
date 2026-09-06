<?php

namespace App\Policies;

use App\Models\Asset;
use App\Models\User;
use App\Support\Rbac;

class AssetPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can(Rbac::PERM_ASSETS_VIEW);
    }

    public function view(User $user, Asset $asset): bool
    {
        return $user->can(Rbac::PERM_ASSETS_VIEW);
    }

    public function create(User $user): bool
    {
        return $user->can(Rbac::PERM_ASSETS_MANAGE);
    }

    public function update(User $user, Asset $asset): bool
    {
        return $user->can(Rbac::PERM_ASSETS_MANAGE);
    }

    public function delete(User $user, Asset $asset): bool
    {
        return $user->can(Rbac::PERM_ASSETS_MANAGE);
    }

    public function restore(User $user, Asset $asset): bool
    {
        return $user->can(Rbac::PERM_ASSETS_MANAGE);
    }

    /** Assign, return, change status. */
    public function manage(User $user, Asset $asset): bool
    {
        return $user->can(Rbac::PERM_ASSETS_MANAGE);
    }
}
