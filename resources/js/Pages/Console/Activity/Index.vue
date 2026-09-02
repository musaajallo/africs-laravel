<script setup>
import { Head } from '@inertiajs/vue3';
import ConsoleLayout from '@/Layouts/ConsoleLayout.vue';
import PanelPageHeader from '@/Components/Panel/PanelPageHeader.vue';
import PanelPagination from '@/Components/Panel/PanelPagination.vue';
import ActivityFeed from '@/Components/Panel/ActivityFeed.vue';

defineProps({
    activities: { type: Object, required: true },
});
</script>

<template>
    <Head title="Activity log" />

    <ConsoleLayout>
        <template #title>Activity log</template>

        <div class="panel-page">
            <PanelPageHeader
                title="Activity log"
                subtitle="Who changed what across the Console."
            />

            <div class="panel-card">
                <ActivityFeed :items="activities.data" show-subject empty="No activity recorded yet." />
            </div>

            <PanelPagination
                :links="activities.links"
                :from="activities.from"
                :to="activities.to"
                :total="activities.total"
            />
        </div>
    </ConsoleLayout>
</template>
