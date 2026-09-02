<script setup>
import { Head } from '@inertiajs/vue3';
import ConsoleLayout from '@/Layouts/ConsoleLayout.vue';
import PanelPageHeader from '@/Components/Panel/PanelPageHeader.vue';
import ClientForm from './Partials/ClientForm.vue';

defineProps({
    client: { type: Object, required: true },
    currencies: { type: Array, default: () => [] },
    categories: { type: Object, default: () => ({}) },
    owners: { type: Array, default: () => [] },
});
</script>

<template>
    <Head :title="`Edit ${client.name}`" />

    <ConsoleLayout>
        <template #title>Clients</template>

        <div class="panel-page">
            <PanelPageHeader :title="client.name" subtitle="Edit client" />
            <div class="panel-card">
                <ClientForm :client="client" :currencies="currencies" :categories="categories" :owners="owners" />
            </div>
        </div>
    </ConsoleLayout>
</template>
