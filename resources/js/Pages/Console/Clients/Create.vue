<script setup>
import { Head } from '@inertiajs/vue3';
import ConsoleLayout from '@/Layouts/ConsoleLayout.vue';
import PanelPageHeader from '@/Components/Panel/PanelPageHeader.vue';
import ClientForm from './Partials/ClientForm.vue';

defineProps({
    currencies: { type: Array, default: () => [] },
    owners: { type: Array, default: () => [] },
});
</script>

<template>
    <Head title="New client" />

    <ConsoleLayout>
        <template #title>Clients</template>

        <div class="panel-page">
            <PanelPageHeader
                title="New client"
                subtitle="Add a company or individual you do work for."
            />
            <div class="panel-card">
                <ClientForm :currencies="currencies" :owners="owners" />
            </div>
        </div>
    </ConsoleLayout>
</template>
