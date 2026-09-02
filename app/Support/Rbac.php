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

    /** View the user & access management screens. */
    public const PERM_USERS_VIEW = 'users.view';

    /** Create, edit, deactivate users and assign their roles. */
    public const PERM_USERS_MANAGE = 'users.manage';

    /** View the client register. */
    public const PERM_CLIENTS_VIEW = 'clients.view';

    /** Create, edit, archive clients and manage their contacts. */
    public const PERM_CLIENTS_MANAGE = 'clients.manage';

    /** View the Settings screen. */
    public const PERM_SETTINGS_VIEW = 'settings.view';

    /** Change company details, currencies, tax and payment defaults. */
    public const PERM_SETTINGS_MANAGE = 'settings.manage';

    /** View the tag list. */
    public const PERM_TAGS_VIEW = 'tags.view';

    /** Create, rename, recolour and delete tags. */
    public const PERM_TAGS_MANAGE = 'tags.manage';

    /** View the activity log. */
    public const PERM_ACTIVITY_VIEW = 'activity.view';

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
            self::PERM_USERS_VIEW,
            self::PERM_USERS_MANAGE,
            self::PERM_CLIENTS_VIEW,
            self::PERM_CLIENTS_MANAGE,
            self::PERM_SETTINGS_VIEW,
            self::PERM_SETTINGS_MANAGE,
            self::PERM_TAGS_VIEW,
            self::PERM_TAGS_MANAGE,
            self::PERM_ACTIVITY_VIEW,
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
                self::PERM_CLIENTS_VIEW,
                self::PERM_CLIENTS_MANAGE,
                self::PERM_SETTINGS_VIEW,
                self::PERM_SETTINGS_MANAGE,
                self::PERM_TAGS_VIEW,
                self::PERM_TAGS_MANAGE,
                self::PERM_ACTIVITY_VIEW,
            ],
        ];
    }
}
