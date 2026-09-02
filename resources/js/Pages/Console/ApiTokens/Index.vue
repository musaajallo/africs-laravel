<script setup>
import { computed, ref } from 'vue';
import { Head, router, useForm, usePage } from '@inertiajs/vue3';
import ConsoleLayout from '@/Layouts/ConsoleLayout.vue';
import PanelPageHeader from '@/Components/Panel/PanelPageHeader.vue';
import PanelButton from '@/Components/Panel/PanelButton.vue';
import PanelConfirm from '@/Components/Panel/PanelConfirm.vue';
import PanelTable from '@/Components/Panel/PanelTable.vue';

defineProps({
    tokens: { type: Array, default: () => [] },
    availableAbilities: { type: Array, default: () => [] },
});

const page = usePage();
const plainTextToken = computed(() => page.props.flash?.plainTextToken ?? null);
const copied = ref(false);

const form = useForm({ name: '', abilities: [] });

function toggle(ability) {
    const i = form.abilities.indexOf(ability);
    if (i === -1) form.abilities.push(ability);
    else form.abilities.splice(i, 1);
}

function create() {
    form.post(route('console.api-tokens.store'), {
        onSuccess: () => form.reset(),
    });
}

function copyToken() {
    navigator.clipboard?.writeText(plainTextToken.value).then(() => {
        copied.value = true;
        setTimeout(() => (copied.value = false), 2000);
    });
}

function revoke(token) {
    router.delete(route('console.api-tokens.destroy', token.id), { preserveScroll: true });
}
</script>

<template>
    <Head title="API tokens" />

    <ConsoleLayout>
        <template #title>API tokens</template>

        <div class="panel-page">
            <PanelPageHeader
                title="API tokens"
                subtitle="Grant other applications scoped access to the Africs API at /api/v1."
            />

            <div v-if="plainTextToken" class="panel-card token-reveal">
                <h2 class="panel-card-title">Copy your new token</h2>
                <p>This is the only time it will be shown. Store it somewhere safe.</p>
                <div class="token-reveal-value">
                    <code>{{ plainTextToken }}</code>
                    <PanelButton variant="secondary" @click="copyToken">
                        {{ copied ? 'Copied' : 'Copy' }}
                    </PanelButton>
                </div>
            </div>

            <form class="panel-card" style="margin-bottom: 1rem" @submit.prevent="create">
                <h2 class="panel-card-title">New token</h2>
                <div class="panel-field" style="max-width: 24rem">
                    <label class="panel-field-label">Name</label>
                    <input v-model="form.name" type="text" class="field-input" placeholder="e.g. Website sync, Zapier" />
                    <p v-if="form.errors.name" class="panel-field-error">{{ form.errors.name }}</p>
                </div>
                <div class="panel-field" style="margin-top: 1rem">
                    <label class="panel-field-label">Abilities</label>
                    <div class="panel-checkbox-list">
                        <label v-for="a in availableAbilities" :key="a" class="panel-checkbox">
                            <input type="checkbox" :checked="form.abilities.includes(a)" @change="toggle(a)" />
                            <span><code>{{ a }}</code></span>
                        </label>
                    </div>
                    <p v-if="form.errors.abilities" class="panel-field-error">{{ form.errors.abilities }}</p>
                </div>
                <div class="panel-form-actions">
                    <PanelButton type="submit" :disabled="form.processing">Create token</PanelButton>
                </div>
            </form>

            <PanelTable
                :columns="['Name', 'Abilities', 'Last used', 'Created', { label: 'Actions', align: 'right' }]"
                :rows="tokens"
                empty="No tokens yet."
            >
                <template #row="{ row }">
                    <td class="panel-cell-strong">{{ row.name }}</td>
                    <td>
                        <span v-for="a in row.abilities" :key="a" class="panel-badge">{{ a }}</span>
                    </td>
                    <td class="panel-cell-muted">{{ row.last_used || 'Never' }}</td>
                    <td class="panel-cell-muted">{{ row.created }}</td>
                    <td class="panel-row-actions">
                        <PanelConfirm
                            :title="`Revoke “${row.name}”?`"
                            message="Applications using this token will immediately lose access."
                            confirm-label="Revoke"
                            @confirm="revoke(row)"
                        >
                            <template #trigger>
                                <button type="button" class="panel-link is-danger">Revoke</button>
                            </template>
                        </PanelConfirm>
                    </td>
                </template>
            </PanelTable>
        </div>
    </ConsoleLayout>
</template>
