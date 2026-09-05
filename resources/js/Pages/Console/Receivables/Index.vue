<script setup>
import { ref } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import ConsoleLayout from '@/Layouts/ConsoleLayout.vue';
import PanelPageHeader from '@/Components/Panel/PanelPageHeader.vue';
import PanelTable from '@/Components/Panel/PanelTable.vue';

const props = defineProps({
    base: { type: String, required: true },
    clients: { type: Array, default: () => [] },
    totals: { type: Object, default: () => ({}) },
    buckets: { type: Array, default: () => [] },
});

const bucketLabels = {
    not_due: 'Not due',
    d1_30: '1–30 days',
    d31_60: '31–60 days',
    d60_plus: '60+ days',
};

const expanded = ref(new Set());
function toggle(id) {
    expanded.value.has(id) ? expanded.value.delete(id) : expanded.value.add(id);
    expanded.value = new Set(expanded.value);
}

const money = (n) => Number(n).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
</script>

<template>
    <Head title="Receivables" />

    <ConsoleLayout>
        <template #title>Receivables</template>

        <div class="panel-page">
            <PanelPageHeader
                title="Accounts receivable"
                :subtitle="`Outstanding invoice balances, converted to ${base} at each invoice's snapshotted rate.`"
            />

            <p v-if="!clients.length" class="panel-card panel-page-lead">Nothing outstanding. Every issued invoice is settled.</p>

            <template v-else>
                <div class="doc-totals" style="width: 100%; margin-bottom: 1.5rem">
                    <div v-for="b in buckets" :key="b" class="doc-totals-row">
                        <span>{{ bucketLabels[b] }}</span><span>{{ base }} {{ money(totals[b]) }}</span>
                    </div>
                    <div class="doc-totals-row is-grand"><span>Total owed</span><span>{{ base }} {{ money(totals.total) }}</span></div>
                </div>

                <PanelTable
                    :columns="['Client', ...buckets.map((b) => ({ label: bucketLabels[b], align: 'right' })), { label: 'Total', align: 'right' }]"
                    :rows="clients"
                    empty="Nothing outstanding."
                >
                    <template #row="{ row }">
                        <td>
                            <button type="button" class="panel-link" style="padding: 0" @click="toggle(row.client_id)">
                                {{ expanded.has(row.client_id) ? '▾' : '▸' }} {{ row.client }}
                            </button>
                            <div v-if="expanded.has(row.client_id)" style="margin-top: 0.5rem">
                                <div v-for="inv in row.invoices" :key="inv.id" class="panel-cell-muted" style="font-size: 0.8rem">
                                    <Link :href="route('console.invoices.show', inv.id)" class="panel-link" style="padding: 0">{{ inv.number }}</Link>
                                    — {{ inv.currency }} {{ money(inv.balance) }}
                                    <span v-if="inv.currency !== base">({{ base }} {{ money(inv.base_balance) }})</span>
                                    · due {{ inv.due_date || '—' }}
                                </div>
                            </div>
                        </td>
                        <td v-for="b in buckets" :key="b" class="num" style="text-align: right">
                            {{ Number(row[b]) ? money(row[b]) : '—' }}
                        </td>
                        <td class="num" style="text-align: right; font-weight: 600">{{ money(row.total) }}</td>
                    </template>
                </PanelTable>
            </template>
        </div>
    </ConsoleLayout>
</template>
