import { computed } from 'vue';
import { usePage } from '@inertiajs/vue3';

/**
 * Thin helpers over the shared `auth` Inertia prop.
 *
 * `can()` mirrors the backend: a super-admin passes every check, otherwise
 * the named permission must be in the user's resolved permission list.
 */
export function useAuth() {
    const page = usePage();

    const user = computed(() => page.props.auth?.user ?? null);
    const roles = computed(() => user.value?.roles ?? []);
    const permissions = computed(() => user.value?.permissions ?? []);

    const isSuperAdmin = computed(() => roles.value.includes('super-admin'));

    function hasRole(role) {
        return roles.value.includes(role);
    }

    function can(permission) {
        return isSuperAdmin.value || permissions.value.includes(permission);
    }

    return { user, roles, permissions, isSuperAdmin, hasRole, can };
}
