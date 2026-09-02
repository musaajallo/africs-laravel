<?php

namespace App\Policies;

use App\Models\Tag;
use App\Models\User;
use App\Support\Rbac;

class TagPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can(Rbac::PERM_TAGS_VIEW);
    }

    public function create(User $user): bool
    {
        return $user->can(Rbac::PERM_TAGS_MANAGE);
    }

    public function update(User $user, Tag $tag): bool
    {
        return $user->can(Rbac::PERM_TAGS_MANAGE);
    }

    public function delete(User $user, Tag $tag): bool
    {
        return $user->can(Rbac::PERM_TAGS_MANAGE);
    }
}
