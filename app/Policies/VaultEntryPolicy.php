<?php

namespace App\Policies;

use App\Models\User;
use App\Models\VaultEntry;
use App\Support\Rbac;

class VaultEntryPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can(Rbac::PERM_VAULT_VIEW);
    }

    public function view(User $user, VaultEntry $entry): bool
    {
        return $user->can(Rbac::PERM_VAULT_VIEW);
    }

    public function create(User $user): bool
    {
        return $user->can(Rbac::PERM_VAULT_MANAGE);
    }

    public function update(User $user, VaultEntry $entry): bool
    {
        return $user->can(Rbac::PERM_VAULT_MANAGE);
    }

    public function delete(User $user, VaultEntry $entry): bool
    {
        return $user->can(Rbac::PERM_VAULT_MANAGE);
    }

    public function restore(User $user, VaultEntry $entry): bool
    {
        return $user->can(Rbac::PERM_VAULT_MANAGE);
    }

    /** Reveal secret values, export the vault. */
    public function reveal(User $user, VaultEntry $entry): bool
    {
        return $user->can(Rbac::PERM_VAULT_MANAGE);
    }
}
