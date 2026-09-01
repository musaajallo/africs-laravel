<script setup>
import PanelShell from '@/Components/PanelShell.vue';

// Website content navigation. Add sections here as they are built.
const nav = [
    { label: 'Dashboard', routeName: 'cms.dashboard' },
];
</script>

<template>
    <PanelShell
        panel="cms"
        label="Africs CMS"
        :home-route="route('cms.dashboard')"
        :nav="nav"
    >
        <template v-if="$slots.title" #title><slot name="title" /></template>
        <slot />
    </PanelShell>
</template>
