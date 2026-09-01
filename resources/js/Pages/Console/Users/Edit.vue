<script setup>
import { Head } from '@inertiajs/vue3';
import ConsoleLayout from '@/Layouts/ConsoleLayout.vue';
import PanelPageHeader from '@/Components/Panel/PanelPageHeader.vue';
import UserForm from './Partials/UserForm.vue';

defineProps({
    user: { type: Object, required: true },
    availableRoles: { type: Array, default: () => [] },
});
</script>

<template>
    <Head :title="`Edit ${user.name}`" />

    <ConsoleLayout>
        <template #title>Users &amp; access</template>

        <div class="panel-page">
            <PanelPageHeader
                :title="user.name"
                :subtitle="user.deactivated ? 'This account is deactivated.' : `@${user.username}`"
            />
            <div class="panel-card">
                <UserForm :user="user" :available-roles="availableRoles" />
            </div>
        </div>
    </ConsoleLayout>
</template>
