<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import ConsoleLayout from '@/Layouts/ConsoleLayout.vue';
import PanelPageHeader from '@/Components/Panel/PanelPageHeader.vue';
import VaultForm from './Partials/VaultForm.vue';

defineProps({
    folders: { type: Array, default: () => [] },
});

const form = useForm({
    title: '',
    folder_id: null,
    username: '',
    password: '',
    url: '',
    notes: '',
    totp_secret: '',
    custom: [],
});

function submit() {
    form.post(route('console.vault.store'));
}
</script>

<template>
    <Head title="New vault entry" />

    <ConsoleLayout>
        <template #title>Secrets vault</template>

        <div class="panel-page">
            <PanelPageHeader title="New entry">
                <template #actions>
                    <Link :href="route('console.vault.index')" class="panel-link">Back to vault</Link>
                </template>
            </PanelPageHeader>

            <VaultForm :form="form" :folders="folders" submit-label="Save entry" @submit="submit" />
        </div>
    </ConsoleLayout>
</template>
