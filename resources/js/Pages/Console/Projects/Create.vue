<script setup>
import { Head } from '@inertiajs/vue3';
import ConsoleLayout from '@/Layouts/ConsoleLayout.vue';
import PanelPageHeader from '@/Components/Panel/PanelPageHeader.vue';
import ProjectForm from './Partials/ProjectForm.vue';

defineProps({
    clients: { type: Array, default: () => [] },
    users: { type: Array, default: () => [] },
    serviceLines: { type: Object, default: () => ({}) },
    statuses: { type: Object, default: () => ({}) },
    currencies: { type: Array, default: () => [] },
    baseCurrency: { type: String, default: 'GMD' },
    allTags: { type: Array, default: () => [] },
    presetClientId: { type: Number, default: null },
});
</script>

<template>
    <Head title="New project" />

    <ConsoleLayout>
        <template #title>Projects</template>

        <div class="panel-page">
            <PanelPageHeader title="New project" subtitle="Set up a client engagement." />
            <div class="panel-card">
                <ProjectForm
                    :clients="clients"
                    :users="users"
                    :service-lines="serviceLines"
                    :statuses="statuses"
                    :currencies="currencies"
                    :base-currency="baseCurrency"
                    :all-tags="allTags"
                    :preset-client-id="presetClientId"
                />
            </div>
        </div>
    </ConsoleLayout>
</template>
