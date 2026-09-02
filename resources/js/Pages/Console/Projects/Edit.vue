<script setup>
import { Head } from '@inertiajs/vue3';
import ConsoleLayout from '@/Layouts/ConsoleLayout.vue';
import PanelPageHeader from '@/Components/Panel/PanelPageHeader.vue';
import ProjectForm from './Partials/ProjectForm.vue';

defineProps({
    project: { type: Object, required: true },
    clients: { type: Array, default: () => [] },
    users: { type: Array, default: () => [] },
    serviceLines: { type: Object, default: () => ({}) },
    statuses: { type: Object, default: () => ({}) },
    currencies: { type: Array, default: () => [] },
    baseCurrency: { type: String, default: 'GMD' },
    allTags: { type: Array, default: () => [] },
});
</script>

<template>
    <Head :title="`Edit ${project.name}`" />

    <ConsoleLayout>
        <template #title>Projects</template>

        <div class="panel-page">
            <PanelPageHeader :title="project.name" subtitle="Edit project" />
            <div class="panel-card">
                <ProjectForm
                    :project="project"
                    :clients="clients"
                    :users="users"
                    :service-lines="serviceLines"
                    :statuses="statuses"
                    :currencies="currencies"
                    :base-currency="baseCurrency"
                    :all-tags="allTags"
                />
            </div>
        </div>
    </ConsoleLayout>
</template>
