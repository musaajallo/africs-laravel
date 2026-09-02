<script setup>
import { Head } from '@inertiajs/vue3';
import ConsoleLayout from '@/Layouts/ConsoleLayout.vue';
import PanelPageHeader from '@/Components/Panel/PanelPageHeader.vue';
import LeadForm from './Partials/LeadForm.vue';

defineProps({
    lead: { type: Object, required: true },
    channels: { type: Object, default: () => ({}) },
    clients: { type: Array, default: () => [] },
});
</script>

<template>
    <Head :title="`Edit ${lead.company || lead.name}`" />

    <ConsoleLayout>
        <template #title>Leads</template>

        <div class="panel-page">
            <PanelPageHeader :title="lead.company || lead.name" subtitle="Edit lead" />
            <div class="panel-card">
                <LeadForm :lead="lead" :channels="channels" :clients="clients" />
            </div>
        </div>
    </ConsoleLayout>
</template>
