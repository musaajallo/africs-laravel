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
        label: 'Leads',
        routeName: 'console.leads.index',
        activeMatch: 'console.leads.*',
        permission: 'leads.view',
    },
    {
        label: 'Clients',
        routeName: 'console.clients.index',
        activeMatch: 'console.clients.*',
        permission: 'clients.view',
    },
    {
        label: 'Projects',
        routeName: 'console.projects.index',
        activeMatch: 'console.projects.*',
        permission: 'projects.view',
    },
    soon('Subscriptions & infra', 'subscriptions'),
    soon('Assets', 'assets'),

    { heading: 'Finance' },
    {
        label: 'Proformas',
        routeName: 'console.proformas.index',
        activeMatch: 'console.proformas.*',
        permission: 'proformas.view',
    },
    {
        label: 'Invoices',
        routeName: 'console.invoices.index',
        activeMatch: 'console.invoices.*',
        permission: 'invoices.view',
    },
    {
        label: 'Payments',
        routeName: 'console.payments.index',
        activeMatch: 'console.payments.*',
        permission: 'payments.view',
    },
    {
        label: 'Receivables',
        routeName: 'console.receivables.index',
        activeMatch: 'console.receivables.*',
        permission: 'receivables.view',
    },

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
        label: 'Exchange rates',
        routeName: 'console.exchange-rates.index',
        activeMatch: 'console.exchange-rates.*',
        permission: 'exchange-rates.view',
    },
    {
        label: 'Tags',
        routeName: 'console.tags.index',
        activeMatch: 'console.tags.*',
        permission: 'tags.view',
    },
    {
        label: 'Settings',
        routeName: 'console.settings.edit',
        activeMatch: 'console.settings.*',
        permission: 'settings.view',
    },
    {
        label: 'API tokens',
        routeName: 'console.api-tokens.index',
        activeMatch: 'console.api-tokens.*',
        permission: 'api-tokens.manage',
    },
    {
        label: 'Activity log',
        routeName: 'console.activity.index',
        activeMatch: 'console.activity.*',
        permission: 'activity.view',
    },
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
