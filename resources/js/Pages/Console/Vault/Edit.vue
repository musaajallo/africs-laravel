<script setup>
import { ref, watch } from 'vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import ConsoleLayout from '@/Layouts/ConsoleLayout.vue';
import PanelPageHeader from '@/Components/Panel/PanelPageHeader.vue';
import PanelButton from '@/Components/Panel/PanelButton.vue';
import VaultUnlockDialog from '@/Components/Panel/VaultUnlockDialog.vue';
import VaultForm from './Partials/VaultForm.vue';

const props = defineProps({
    entry: { type: Object, required: true },
    folders: { type: Array, default: () => [] },
    unlocked: { type: Boolean, default: false },
});

const showUnlock = ref(false);

function seed(entry) {
    return {
        title: entry.title,
        folder_id: entry.folder_id,
        username: entry.username ?? '',
        password: entry.password ?? '',
        url: entry.url ?? '',
        notes: entry.notes ?? '',
        totp_secret: entry.totp_secret ?? '',
        custom: entry.custom.map((f) => ({ label: f.label, value: f.value ?? '', secret: f.secret })),
    };
}

const form = useForm(seed(props.entry));

// After unlocking, the reloaded entry carries the real secret values.
watch(
    () => props.entry,
    (entry) => form.defaults(seed(entry)).reset(),
);

function submit() {
    form.put(route('console.vault.update', props.entry.id));
}
function onUnlocked() {
    router.reload({ only: ['entry', 'unlocked'] });
}
</script>

<template>
    <Head :title="`Edit ${entry.title}`" />

    <ConsoleLayout>
        <template #title>Secrets vault</template>

        <div class="panel-page">
            <PanelPageHeader :title="`Edit ${entry.title}`">
                <template #actions>
                    <Link :href="route('console.vault.show', entry.id)" class="panel-link">Cancel</Link>
                </template>
            </PanelPageHeader>

            <div v-if="!unlocked" class="panel-card">
                <p>Unlock the vault to edit this entry — you'll see its current secret values.</p>
                <PanelButton style="margin-top: 0.75rem" @click="showUnlock = true">Unlock</PanelButton>
            </div>

            <VaultForm v-else :form="form" :folders="folders" submit-label="Save changes" @submit="submit" />
        </div>

        <VaultUnlockDialog v-model:show="showUnlock" @unlocked="onUnlocked" />
    </ConsoleLayout>
</template>
