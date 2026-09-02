<?php

namespace App\Policies;

use App\Models\Client;
use App\Models\User;
use App\Support\Rbac;

class ClientPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can(Rbac::PERM_CLIENTS_VIEW);
    }

    public function view(User $user, Client $client): bool
    {
        return $user->can(Rbac::PERM_CLIENTS_VIEW);
    }

    public function create(User $user): bool
    {
        return $user->can(Rbac::PERM_CLIENTS_MANAGE);
    }

    public function update(User $user, Client $client): bool
    {
        return $user->can(Rbac::PERM_CLIENTS_MANAGE);
    }

    /** "Delete" in the UI means archive (soft delete). */
    public function delete(User $user, Client $client): bool
    {
        return $user->can(Rbac::PERM_CLIENTS_MANAGE);
    }

    public function restore(User $user, Client $client): bool
    {
        return $user->can(Rbac::PERM_CLIENTS_MANAGE);
    }
}
