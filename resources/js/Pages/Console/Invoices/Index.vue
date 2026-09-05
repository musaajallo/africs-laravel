<script setup>
import { ref, watch } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import ConsoleLayout from '@/Layouts/ConsoleLayout.vue';
import PanelPageHeader from '@/Components/Panel/PanelPageHeader.vue';
import PanelTable from '@/Components/Panel/PanelTable.vue';
import PanelPagination from '@/Components/Panel/PanelPagination.vue';
import PanelButton from '@/Components/Panel/PanelButton.vue';
import PanelConfirm from '@/Components/Panel/PanelConfirm.vue';
import DocumentStatusBadge from '@/Components/Panel/DocumentStatusBadge.vue';
import { useAuth } from '@/Composables/useAuth.js';

const props = defineProps({
    invoices: { type: Object, required: true },
    filters: { type: Object, default: () => ({}) },
    statuses: { type: Object, default: () => ({}) },
    clients: { type: Array, default: () => [] },
});

const { can } = useAuth();
const search = ref(props.filters.search ?? '');
const status = ref(props.filters.status ?? '');
const client = ref(props.filters.client ?? '');
let timer = null;

function apply() {
    router.get(
        route('console.invoices.index'),
        {
            search: search.value || undefined,
            status: status.value || undefined,
            client: client.value || undefined,
        },
        { preserveState: true, preserveScroll: true, replace: true },
    );
}

watch(search, () => {
    clearTimeout(timer);
    timer = setTimeout(apply, 300);
});
watch([status, client], apply);

function archive(invoice) {
    router.delete(route('console.invoices.destroy', invoice.id), { preserveScroll: true });
}
</script>

<template>
    <Head title="Invoices" />

    <ConsoleLayout>
        <template #title>Invoices</template>

        <div class="panel-page">
            <PanelPageHeader
                title="Invoices"
                subtitle="Tax invoices with their own numbering and lifecycle."
            >
                <template #actions>
                    <PanelButton v-if="can('invoices.manage')" :href="route('console.invoices.create')">
                        New invoice
                    </PanelButton>
                </template>
            </PanelPageHeader>

            <div class="panel-toolbar">
                <input
                    v-model="search"
                    type="search"
                    class="field-input panel-toolbar-search"
                    placeholder="Search by number or client"
                />
                <select v-model="status" class="field-input" style="max-width: 11rem">
                    <option value="">Open invoices</option>
                    <option v-for="(label, key) in statuses" :key="key" :value="key">{{ label }}</option>
                </select>
                <select v-model="client" class="field-input" style="max-width: 12rem">
                    <option value="">Any client</option>
                    <option v-for="c in clients" :key="c.id" :value="c.id">{{ c.name }}</option>
                </select>
            </div>

            <PanelTable
                :columns="['Number', 'Client', 'Status', { label: 'Total', align: 'right' }, 'Issued', 'Due', { label: 'Actions', align: 'right' }]"
                :rows="invoices.data"
                empty="No invoices match your filter."
            >
                <template #row="{ row }">
                    <td>
                        <Link :href="route('console.invoices.show', row.id)" class="panel-link" style="padding: 0">
                            {{ row.number }}
                        </Link>
                    </td>
                    <td>{{ row.client }}</td>
                    <td><DocumentStatusBadge :status="row.status" /></td>
                    <td class="num" style="text-align: right">{{ row.currency }} {{ Number(row.total).toLocaleString() }}</td>
                    <td class="panel-cell-muted">{{ row.issue_date }}</td>
                    <td class="panel-cell-muted">{{ row.due_date || '—' }}</td>
                    <td class="panel-row-actions">
                        <Link :href="route('console.invoices.show', row.id)" class="panel-link">View</Link>
                        <Link
                            v-if="can('invoices.manage') && row.status === 'draft'"
                            :href="route('console.invoices.edit', row.id)"
                            class="panel-link"
                        >Edit</Link>
                        <PanelConfirm
                            v-if="can('invoices.manage')"
                            title="Archive this invoice?"
                            :message="`${row.number} will be hidden from the list. It can be restored.`"
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
                :links="invoices.links"
                :from="invoices.from"
                :to="invoices.to"
                :total="invoices.total"
            />
        </div>
    </ConsoleLayout>
</template>
