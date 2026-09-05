<script setup>
import { ref, watch } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import ConsoleLayout from '@/Layouts/ConsoleLayout.vue';
import PanelPageHeader from '@/Components/Panel/PanelPageHeader.vue';
import PanelTable from '@/Components/Panel/PanelTable.vue';
import PanelPagination from '@/Components/Panel/PanelPagination.vue';
import PanelButton from '@/Components/Panel/PanelButton.vue';
import { useAuth } from '@/Composables/useAuth.js';

const props = defineProps({
    payments: { type: Object, required: true },
    filters: { type: Object, default: () => ({}) },
    methods: { type: Array, default: () => [] },
    clients: { type: Array, default: () => [] },
});

const { can } = useAuth();
const search = ref(props.filters.search ?? '');
const method = ref(props.filters.method ?? '');
const client = ref(props.filters.client ?? '');
let timer = null;

function apply() {
    router.get(
        route('console.payments.index'),
        {
            search: search.value || undefined,
            method: method.value || undefined,
            client: client.value || undefined,
        },
        { preserveState: true, preserveScroll: true, replace: true },
    );
}

watch(search, () => {
    clearTimeout(timer);
    timer = setTimeout(apply, 300);
});
watch([method, client], apply);
</script>

<template>
    <Head title="Payments" />

    <ConsoleLayout>
        <template #title>Payments</template>

        <div class="panel-page">
            <PanelPageHeader title="Payments" subtitle="Money received, applied to invoices. Each is a numbered receipt.">
                <template #actions>
                    <PanelButton v-if="can('payments.manage')" :href="route('console.payments.create')">
                        Record payment
                    </PanelButton>
                </template>
            </PanelPageHeader>

            <div class="panel-toolbar">
                <input
                    v-model="search"
                    type="search"
                    class="field-input panel-toolbar-search"
                    placeholder="Search by receipt, reference or client"
                />
                <select v-model="method" class="field-input" style="max-width: 11rem">
                    <option value="">Any method</option>
                    <option v-for="m in methods" :key="m" :value="m">{{ m }}</option>
                </select>
                <select v-model="client" class="field-input" style="max-width: 12rem">
                    <option value="">Any client</option>
                    <option v-for="c in clients" :key="c.id" :value="c.id">{{ c.name }}</option>
                </select>
            </div>

            <PanelTable
                :columns="['Receipt', 'Client', 'Method', { label: 'Amount', align: 'right' }, { label: 'Applied', align: 'right' }, 'Date', { label: '', align: 'right' }]"
                :rows="payments.data"
                empty="No payments match your filter."
            >
                <template #row="{ row }">
                    <td>
                        <Link :href="route('console.payments.show', row.id)" class="panel-link" style="padding: 0">
                            {{ row.number }}
                        </Link>
                    </td>
                    <td>{{ row.client }}</td>
                    <td>{{ row.method }}</td>
                    <td class="num" style="text-align: right">{{ row.currency }} {{ Number(row.amount).toLocaleString() }}</td>
                    <td class="num" style="text-align: right">
                        {{ Number(row.allocated_amount).toLocaleString() }}
                        <span class="panel-cell-muted">({{ row.allocations_count }})</span>
                    </td>
                    <td class="panel-cell-muted">{{ row.paid_on }}</td>
                    <td class="panel-row-actions">
                        <Link :href="route('console.payments.show', row.id)" class="panel-link">View</Link>
                    </td>
                </template>
            </PanelTable>

            <PanelPagination
                :links="payments.links"
                :from="payments.from"
                :to="payments.to"
                :total="payments.total"
            />
        </div>
    </ConsoleLayout>
</template>
