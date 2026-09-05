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

    /** View the leads inbox. */
    public const PERM_LEADS_VIEW = 'leads.view';

    /** Triage leads — assign, set status, add notes, convert to a client. */
    public const PERM_LEADS_MANAGE = 'leads.manage';

    /** View the client register. */
    public const PERM_CLIENTS_VIEW = 'clients.view';

    /** Create, edit, archive clients and manage their contacts. */
    public const PERM_CLIENTS_MANAGE = 'clients.manage';

    /** View projects. */
    public const PERM_PROJECTS_VIEW = 'projects.view';

    /** Create, edit, archive projects and manage their team. */
    public const PERM_PROJECTS_MANAGE = 'projects.manage';

    /** View proformas. */
    public const PERM_PROFORMAS_VIEW = 'proformas.view';

    /** Create, edit, send, archive proformas and convert them to invoices. */
    public const PERM_PROFORMAS_MANAGE = 'proformas.manage';

    /** View the exchange-rate screen. */
    public const PERM_EXCHANGE_RATES_VIEW = 'exchange-rates.view';

    /** Enter and override FX rates manually. */
    public const PERM_EXCHANGE_RATES_MANAGE = 'exchange-rates.manage';

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

    /** Issue and revoke API tokens. */
    public const PERM_API_TOKENS_MANAGE = 'api-tokens.manage';

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
            self::PERM_LEADS_VIEW,
            self::PERM_LEADS_MANAGE,
            self::PERM_CLIENTS_VIEW,
            self::PERM_CLIENTS_MANAGE,
            self::PERM_PROJECTS_VIEW,
            self::PERM_PROJECTS_MANAGE,
            self::PERM_PROFORMAS_VIEW,
            self::PERM_PROFORMAS_MANAGE,
            self::PERM_EXCHANGE_RATES_VIEW,
            self::PERM_EXCHANGE_RATES_MANAGE,
            self::PERM_SETTINGS_VIEW,
            self::PERM_SETTINGS_MANAGE,
            self::PERM_TAGS_VIEW,
            self::PERM_TAGS_MANAGE,
            self::PERM_ACTIVITY_VIEW,
            self::PERM_API_TOKENS_MANAGE,
        ];
    }

    /**
     * Permissions that can be granted to an API token as an "ability".
     * Panel-access gates are excluded — a token targets resources, not panels.
     *
     * @return list<string>
     */
    public static function apiAbilities(): array
    {
        return array_values(array_diff(self::permissions(), [
            self::PERM_CMS_ACCESS,
            self::PERM_CONSOLE_ACCESS,
        ]));
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
                self::PERM_LEADS_VIEW,
                self::PERM_LEADS_MANAGE,
                self::PERM_CLIENTS_VIEW,
                self::PERM_CLIENTS_MANAGE,
                self::PERM_PROJECTS_VIEW,
                self::PERM_PROJECTS_MANAGE,
                self::PERM_PROFORMAS_VIEW,
                self::PERM_PROFORMAS_MANAGE,
                self::PERM_EXCHANGE_RATES_VIEW,
                self::PERM_EXCHANGE_RATES_MANAGE,
                self::PERM_SETTINGS_VIEW,
                self::PERM_SETTINGS_MANAGE,
                self::PERM_TAGS_VIEW,
                self::PERM_TAGS_MANAGE,
                self::PERM_ACTIVITY_VIEW,
                self::PERM_API_TOKENS_MANAGE,
            ],
        ];
    }
}
