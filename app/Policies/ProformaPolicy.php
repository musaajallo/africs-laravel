<?php

namespace App\Policies;

use App\Models\Proforma;
use App\Models\User;
use App\Support\Rbac;

class ProformaPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can(Rbac::PERM_PROFORMAS_VIEW);
    }

    public function view(User $user, Proforma $proforma): bool
    {
        return $user->can(Rbac::PERM_PROFORMAS_VIEW);
    }

    public function create(User $user): bool
    {
        return $user->can(Rbac::PERM_PROFORMAS_MANAGE);
    }

    /** Only a draft can be edited. */
    public function update(User $user, Proforma $proforma): bool
    {
        return $user->can(Rbac::PERM_PROFORMAS_MANAGE) && $proforma->isEditable();
    }

    public function delete(User $user, Proforma $proforma): bool
    {
        return $user->can(Rbac::PERM_PROFORMAS_MANAGE);
    }

    public function restore(User $user, Proforma $proforma): bool
    {
        return $user->can(Rbac::PERM_PROFORMAS_MANAGE);
    }

    /** Change status, send, or convert. */
    public function manage(User $user, Proforma $proforma): bool
    {
        return $user->can(Rbac::PERM_PROFORMAS_MANAGE);
    }
}
