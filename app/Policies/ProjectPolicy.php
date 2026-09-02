<?php

namespace App\Policies;

use App\Models\Project;
use App\Models\User;
use App\Support\Rbac;

class ProjectPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can(Rbac::PERM_PROJECTS_VIEW);
    }

    public function view(User $user, Project $project): bool
    {
        return $user->can(Rbac::PERM_PROJECTS_VIEW);
    }

    public function create(User $user): bool
    {
        return $user->can(Rbac::PERM_PROJECTS_MANAGE);
    }

    public function update(User $user, Project $project): bool
    {
        return $user->can(Rbac::PERM_PROJECTS_MANAGE);
    }

    public function delete(User $user, Project $project): bool
    {
        return $user->can(Rbac::PERM_PROJECTS_MANAGE);
    }

    public function restore(User $user, Project $project): bool
    {
        return $user->can(Rbac::PERM_PROJECTS_MANAGE);
    }
}
