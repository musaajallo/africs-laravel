<script setup>
import PanelShell from '@/Components/PanelShell.vue';

// ERP navigation. Add module entries here as they are built.
const nav = [
    { label: 'Dashboard', routeName: 'console.dashboard' },
];
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
