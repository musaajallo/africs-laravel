<?php

namespace App\Support;

use App\Models\User;

final class PanelRouter
{
    /**
     * The route a user should land on after authenticating, based on which
     * panels they can access. Console takes precedence over CMS; users with
     * neither are sent to the public site.
     */
    public static function homeFor(?User $user): string
    {
        if ($user?->can(Rbac::PERM_CONSOLE_ACCESS)) {
            return route('console.dashboard');
        }

        if ($user?->can(Rbac::PERM_CMS_ACCESS)) {
            return route('cms.dashboard');
        }

        return '/';
    }
}
