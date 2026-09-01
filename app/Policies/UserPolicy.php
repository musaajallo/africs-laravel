<?php

namespace App\Policies;

use App\Models\User;
use App\Support\Rbac;

class UserPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can(Rbac::PERM_USERS_VIEW);
    }

    public function view(User $user, User $model): bool
    {
        return $user->can(Rbac::PERM_USERS_VIEW);
    }

    public function create(User $user): bool
    {
        return $user->can(Rbac::PERM_USERS_MANAGE);
    }

    public function update(User $user, User $model): bool
    {
        return $user->can(Rbac::PERM_USERS_MANAGE)
            && $this->canActOn($user, $model);
    }

    /**
     * "Delete" in the UI means deactivate. A user cannot deactivate their
     * own account.
     */
    public function delete(User $user, User $model): bool
    {
        return $user->can(Rbac::PERM_USERS_MANAGE)
            && $user->isNot($model)
            && $this->canActOn($user, $model);
    }

    public function restore(User $user, User $model): bool
    {
        return $this->update($user, $model);
    }

    /**
     * Only a super-admin may modify another super-admin's account.
     */
    protected function canActOn(User $user, User $model): bool
    {
        if ($model->hasRole(Rbac::ROLE_SUPER_ADMIN)) {
            return $user->hasRole(Rbac::ROLE_SUPER_ADMIN);
        }

        return true;
    }
}
