<?php

namespace App\Policies;

use App\Models\Lead;
use App\Models\User;
use App\Support\Rbac;

class LeadPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can(Rbac::PERM_LEADS_VIEW);
    }

    public function view(User $user, Lead $lead): bool
    {
        return $user->can(Rbac::PERM_LEADS_VIEW);
    }

    public function update(User $user, Lead $lead): bool
    {
        return $user->can(Rbac::PERM_LEADS_MANAGE);
    }

    public function convert(User $user, Lead $lead): bool
    {
        return $user->can(Rbac::PERM_LEADS_MANAGE)
            && $user->can(Rbac::PERM_CLIENTS_MANAGE)
            && ! $lead->isConverted();
    }

    public function delete(User $user, Lead $lead): bool
    {
        return $user->can(Rbac::PERM_LEADS_MANAGE);
    }
}
