<script setup>
import { Head } from '@inertiajs/vue3';
import ConsoleLayout from '@/Layouts/ConsoleLayout.vue';
import PanelPageHeader from '@/Components/Panel/PanelPageHeader.vue';
import LeadForm from './Partials/LeadForm.vue';

defineProps({
    channels: { type: Object, default: () => ({}) },
    clients: { type: Array, default: () => [] },
});
</script>

<template>
    <Head title="New lead" />

    <ConsoleLayout>
        <template #title>Leads</template>

        <div class="panel-page">
            <PanelPageHeader
                title="New lead"
                subtitle="Log an enquiry that didn't come through the website."
            />
            <div class="panel-card">
                <LeadForm :channels="channels" :clients="clients" />
            </div>
        </div>
    </ConsoleLayout>
</template>
