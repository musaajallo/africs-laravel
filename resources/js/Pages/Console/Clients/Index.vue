<script setup>
import { ref, watch } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import ConsoleLayout from '@/Layouts/ConsoleLayout.vue';
import PanelPageHeader from '@/Components/Panel/PanelPageHeader.vue';
import PanelTable from '@/Components/Panel/PanelTable.vue';
import PanelPagination from '@/Components/Panel/PanelPagination.vue';
import PanelButton from '@/Components/Panel/PanelButton.vue';
import PanelConfirm from '@/Components/Panel/PanelConfirm.vue';
import TagBadge from '@/Components/Panel/TagBadge.vue';
import { useAuth } from '@/Composables/useAuth.js';

const props = defineProps({
    clients: { type: Object, required: true },
    filters: { type: Object, default: () => ({}) },
    types: { type: Array, default: () => [] },
    tags: { type: Array, default: () => [] },
});

const { can } = useAuth();
const search = ref(props.filters.search ?? '');
const status = ref(props.filters.status ?? '');
const type = ref(props.filters.type ?? '');
const tag = ref(props.filters.tag ?? '');
let timer = null;

const typeLabels = {
    individual: 'Individual',
    organisation: 'Organisation',
    government: 'Government',
};

function apply() {
    router.get(
        route('console.clients.index'),
        {
            search: search.value || undefined,
            status: status.value || undefined,
            type: type.value || undefined,
            tag: tag.value || undefined,
        },
        { preserveState: true, preserveScroll: true, replace: true },
    );
}

watch(search, () => {
    clearTimeout(timer);
    timer = setTimeout(apply, 300);
});
watch([status, type, tag], apply);

function archive(client) {
    router.delete(route('console.clients.destroy', client.id), { preserveScroll: true });
}
</script>

<template>
    <Head title="Clients" />

    <ConsoleLayout>
        <template #title>Clients</template>

        <div class="panel-page">
            <PanelPageHeader
                title="Clients"
                subtitle="The companies and people Africs does work for."
            >
                <template #actions>
                    <PanelButton v-if="can('clients.manage')" :href="route('console.clients.create')">
                        New client
                    </PanelButton>
                </template>
            </PanelPageHeader>

            <div class="panel-toolbar">
                <input
                    v-model="search"
                    type="search"
                    class="field-input panel-toolbar-search"
                    placeholder="Search name, email or city"
                />
                <select v-model="type" class="field-input" style="max-width: 12rem">
                    <option value="">All types</option>
                    <option v-for="t in types" :key="t" :value="t">{{ typeLabels[t] }}</option>
                </select>
                <select v-model="status" class="field-input" style="max-width: 11rem">
                    <option value="">All statuses</option>
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                </select>
                <select v-if="tags.length" v-model="tag" class="field-input" style="max-width: 11rem">
                    <option value="">Any tag</option>
                    <option v-for="t in tags" :key="t.slug" :value="t.slug">{{ t.name }}</option>
                </select>
            </div>

            <PanelTable
                :columns="[
                    'Name',
                    'Location',
                    'Currency',
                    'Account manager',
                    { label: 'Contacts', align: 'right' },
                    { label: 'Actions', align: 'right' },
                ]"
                :rows="clients.data"
                empty="No clients match your search."
            >
                <template #row="{ row }">
                    <td>
                        <Link :href="route('console.clients.show', row.id)" class="panel-link" style="padding: 0">
                            {{ row.name }}
                        </Link>
                        <div class="panel-cell-muted">
                            {{ typeLabels[row.type] }}<span v-if="row.category"> &middot; {{ row.category }}</span>
                            <span v-if="row.status === 'inactive'"> &middot; inactive</span>
                        </div>
                        <div v-if="row.tags.length" class="tag-badge-list" style="margin-top: 0.35rem">
                            <TagBadge v-for="t in row.tags" :key="t.name" :name="t.name" :color="t.color" />
                        </div>
                    </td>
                    <td>
                        <span v-if="row.city || row.country">{{ [row.city, row.country].filter(Boolean).join(', ') }}</span>
                        <span v-else class="panel-cell-muted">—</span>
                    </td>
                    <td>{{ row.currency || '—' }}</td>
                    <td>{{ row.owner || '—' }}</td>
                    <td style="text-align: right">{{ row.contacts_count }}</td>
                    <td class="panel-row-actions">
                        <Link :href="route('console.clients.show', row.id)" class="panel-link">View</Link>
                        <Link
                            v-if="can('clients.manage')"
                            :href="route('console.clients.edit', row.id)"
                            class="panel-link"
                        >
                            Edit
                        </Link>
                        <PanelConfirm
                            v-if="can('clients.manage')"
                            title="Archive this client?"
                            :message="`${row.name} will be hidden from the list. History is kept and it can be restored.`"
                            confirm-label="Archive"
                            @confirm="archive(row)"
                        >
                            <template #trigger>
                                <button type="button" class="panel-link is-danger">Archive</button>
                            </template>
                        </PanelConfirm>
                    </td>
                </template>
            </PanelTable>

            <PanelPagination
                :links="clients.links"
                :from="clients.from"
                :to="clients.to"
                :total="clients.total"
            />
        </div>
    </ConsoleLayout>
</template>
