<script setup>
import { Head } from '@inertiajs/vue3';
import ConsoleLayout from '@/Layouts/ConsoleLayout.vue';
import PanelPageHeader from '@/Components/Panel/PanelPageHeader.vue';
import UserForm from './Partials/UserForm.vue';

defineProps({
    availableRoles: { type: Array, default: () => [] },
});
</script>

<template>
    <Head title="New user" />

    <ConsoleLayout>
        <template #title>Users &amp; access</template>

        <div class="panel-page">
            <PanelPageHeader
                title="New user"
                subtitle="Create an internal account and assign its roles."
            />
            <div class="panel-card">
                <UserForm :available-roles="availableRoles" />
            </div>
        </div>
    </ConsoleLayout>
</template>
