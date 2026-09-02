<script setup>
import { computed } from 'vue';
import PanelShell from '@/Components/PanelShell.vue';
import { useAuth } from '@/Composables/useAuth.js';

const { can } = useAuth();

// `heading` rows are section labels. `permission: null` = visible to any
// Console user. `soon` items link to a placeholder — remove as each ships.
const soon = (label, key) => ({
    label,
    routeName: 'console.roadmap',
    routeParams: { module: key },
    activeMatch: 'console.roadmap',
    permission: null,
    soon: true,
});

const allNav = [
    { heading: 'Operations' },
    { label: 'Dashboard', routeName: 'console.dashboard', permission: null },
    {
        label: 'Clients',
        routeName: 'console.clients.index',
        activeMatch: 'console.clients.*',
        permission: 'clients.view',
    },
    soon('Projects', 'projects'),
    soon('Subscriptions & infra', 'subscriptions'),
    soon('Assets', 'assets'),

    { heading: 'Finance' },
    soon('Proformas', 'proformas'),
    soon('Invoices', 'invoices'),
    soon('Payments & receipts', 'payments'),

    { heading: 'Security' },
    soon('Secrets vault', 'secrets'),

    { heading: 'Administration' },
    {
        label: 'Users & access',
        routeName: 'console.users.index',
        activeMatch: 'console.users.*',
        permission: 'users.view',
    },
    {
        label: 'Settings',
        routeName: 'console.settings.edit',
        activeMatch: 'console.settings.*',
        permission: 'settings.view',
    },
    soon('API tokens', 'api-tokens'),
    soon('Activity log', 'activity'),
];

const nav = computed(() => {
    const visible = allNav.filter(
        (item) => item.heading || !item.permission || can(item.permission),
    );
    // drop a heading that ends up with no items under it
    return visible.filter((item, i) => {
        if (!item.heading) return true;
        const next = visible[i + 1];
        return next && !next.heading;
    });
});
</script>

<template>
    <PanelShell
        panel="console"
        label="Africs Console"
        :home-route="route('console.dashboard')"
        :nav="nav"
    >
        <template v-if="$slots.title" #title><slot name="title" /></template>
        <slot />
    </PanelShell>
</template>
