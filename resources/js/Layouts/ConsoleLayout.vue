<script setup>
import { computed } from 'vue';
import PanelShell from '@/Components/PanelShell.vue';
import { useAuth } from '@/Composables/useAuth.js';

const { can } = useAuth();

// ERP navigation. `permission: null` means always visible to panel users.
const allNav = [
    { label: 'Dashboard', routeName: 'console.dashboard', permission: null },
    {
        label: 'Users & access',
        routeName: 'console.users.index',
        activeMatch: 'console.users.*',
        permission: 'users.view',
    },
];

const nav = computed(() =>
    allNav.filter((item) => !item.permission || can(item.permission)),
);
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
