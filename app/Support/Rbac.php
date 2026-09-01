<?php

namespace App\Support;

/**
 * Central definition of the application's roles and permissions.
 *
 * Permissions are the fine-grained capabilities checked in policies and
 * middleware. Roles are named bundles of permissions assigned to users.
 * The CMS and Console panels each gate on their own `*.access` permission.
 *
 * As ERP modules are built out, add their permissions here and fold them
 * into the relevant roles, then re-run `db:seed --class=RolesAndPermissionsSeeder`.
 */
final class Rbac
{
    /** Full access to everything; bypasses individual permission checks. */
    public const ROLE_SUPER_ADMIN = 'super-admin';

    /** Can sign in to the CMS panel and manage website content. */
    public const ROLE_CMS_MANAGER = 'cms-manager';

    /** Can sign in to the Console panel and use ERP features. */
    public const ROLE_CONSOLE_MANAGER = 'console-manager';

    /** Gate permission for the CMS panel (/cms). */
    public const PERM_CMS_ACCESS = 'cms.access';

    /** Gate permission for the Console panel (/console). */
    public const PERM_CONSOLE_ACCESS = 'console.access';

    /**
     * Every permission the application knows about.
     *
     * @return list<string>
     */
    public static function permissions(): array
    {
        return [
            self::PERM_CMS_ACCESS,
            self::PERM_CONSOLE_ACCESS,
        ];
    }

    /**
     * Role => permissions map. `super-admin` is intentionally omitted; it is
     * granted a blanket pass via a Gate::before hook in AppServiceProvider.
     *
     * @return array<string, list<string>>
     */
    public static function roles(): array
    {
        return [
            self::ROLE_CMS_MANAGER => [
                self::PERM_CMS_ACCESS,
            ],
            self::ROLE_CONSOLE_MANAGER => [
                self::PERM_CONSOLE_ACCESS,
            ],
        ];
    }
}
