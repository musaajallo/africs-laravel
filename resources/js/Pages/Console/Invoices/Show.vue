<script setup>
import { computed } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import ConsoleLayout from '@/Layouts/ConsoleLayout.vue';
import PanelPageHeader from '@/Components/Panel/PanelPageHeader.vue';
import PanelButton from '@/Components/Panel/PanelButton.vue';
import PanelConfirm from '@/Components/Panel/PanelConfirm.vue';
import DocumentStatusBadge from '@/Components/Panel/DocumentStatusBadge.vue';
import ActivityFeed from '@/Components/Panel/ActivityFeed.vue';
import { useAuth } from '@/Composables/useAuth.js';

const props = defineProps({
    invoice: { type: Object, required: true },
    manualStatuses: { type: Array, default: () => [] },
    activity: { type: Array, default: () => [] },
});

const { can } = useAuth();
const canManage = computed(() => can('invoices.manage') && !props.invoice.archived);

const money = (n) =>
    `${props.invoice.currency} ${Number(n).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;

const statusLabels = {
    sent: 'Mark sent',
    partially_paid: 'Mark partly paid',
    paid: 'Mark paid',
    overdue: 'Mark overdue',
    void: 'Void',
};

const details = computed(() => [
    { label: 'Client', value: props.invoice.client, href: route('console.clients.show', props.invoice.client_id) },
    { label: 'Project', value: props.invoice.project, href: props.invoice.project_id ? route('console.projects.show', props.invoice.project_id) : null },
    { label: 'From proforma', value: props.invoice.proforma?.number, href: props.invoice.proforma ? route('console.proformas.show', props.invoice.proforma.id) : null },
    { label: 'Issued', value: props.invoice.issue_date },
    { label: 'Due', value: props.invoice.due_date },
    { label: 'Exchange rate', value: `1 ${props.invoice.currency} = ${props.invoice.fx_rate}` },
    { label: 'Base total', value: `${props.invoice.base_total}` },
    { label: 'Created by', value: props.invoice.created_by },
]);

function setStatus(status) {
    router.put(route('console.invoices.status', props.invoice.id), { status }, { preserveScroll: true });
}
function archive() {
    router.delete(route('console.invoices.destroy', props.invoice.id));
}
function restore() {
    router.put(route('console.invoices.restore', props.invoice.id));
}
</script>

<template>
    <Head :title="invoice.number" />

    <ConsoleLayout>
        <template #title>Invoices</template>

        <div class="panel-page">
            <PanelPageHeader
                :title="invoice.number"
                :subtitle="invoice.archived ? 'Archived invoice' : null"
            >
                <template #actions>
                    <PanelButton variant="secondary" :href="route('console.invoices.index')">All invoices</PanelButton>
                    <a :href="route('console.invoices.pdf', invoice.id)" class="btn btn-secondary btn-sm">Download PDF</a>
                    <PanelButton
                        v-if="canManage && invoice.editable"
                        :href="route('console.invoices.edit', invoice.id)"
                    >Edit</PanelButton>
                    <PanelConfirm
                        v-if="canManage"
                        title="Archive this invoice?"
                        :message="`${invoice.number} will be hidden from the list. It can be restored.`"
                        confirm-label="Archive"
                        @confirm="archive"
                    />
                    <PanelButton v-if="can('invoices.manage') && invoice.archived" @click="restore">Restore</PanelButton>
                </template>
            </PanelPageHeader>

            <div style="margin: -0.25rem 0 1.25rem; display: flex; align-items: center; gap: 0.6rem; flex-wrap: wrap">
                <DocumentStatusBadge :status="invoice.status" />
            </div>

            <div v-if="canManage" class="panel-toolbar" style="margin-bottom: 1.25rem">
                <PanelButton
                    v-for="s in manualStatuses"
                    :key="s"
                    variant="secondary"
                    :disabled="invoice.status === s"
                    @click="setStatus(s)"
                >
                    {{ statusLabels[s] || s }}
                </PanelButton>
            </div>

            <div class="client-detail-grid">
                <section class="panel-card">
                    <h2 class="panel-card-title">Details</h2>
                    <dl class="client-dl">
                        <template v-for="item in details" :key="item.label">
                            <div v-if="item.value">
                                <dt>{{ item.label }}</dt>
                                <dd>
                                    <Link v-if="item.href" :href="item.href" class="panel-link" style="padding: 0">{{ item.value }}</Link>
                                    <span v-else>{{ item.value }}</span>
                                </dd>
                            </div>
                        </template>
                    </dl>
                </section>

                <section class="panel-card">
                    <h2 class="panel-card-title">Activity</h2>
                    <ActivityFeed :items="activity" empty="No changes recorded yet." />
                </section>
            </div>

            <section class="panel-card">
                <h2 class="panel-card-title">Line items</h2>
                <table class="doc-lines">
                    <thead>
                        <tr>
                            <th style="width: 55%">Description</th>
                            <th class="num">Qty</th>
                            <th class="num">Unit price</th>
                            <th class="num">Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="line in invoice.lines" :key="line.id">
                            <td>{{ line.description }}</td>
                            <td class="num">{{ line.quantity }}</td>
                            <td class="num">{{ money(line.unit_price) }}</td>
                            <td class="num">{{ money(line.line_total) }}</td>
                        </tr>
                    </tbody>
                </table>

                <div class="doc-totals">
                    <div class="doc-totals-row"><span>Subtotal</span><span>{{ money(invoice.subtotal) }}</span></div>
                    <div class="doc-totals-row">
                        <span>{{ invoice.tax_label }} ({{ invoice.tax_rate }}%)</span>
                        <span>{{ money(invoice.tax_total) }}</span>
                    </div>
                    <div class="doc-totals-row is-grand"><span>Total</span><span>{{ money(invoice.total) }}</span></div>
                    <div v-if="Number(invoice.amount_paid) > 0" class="doc-totals-row">
                        <span>Paid</span><span>{{ money(invoice.amount_paid) }}</span>
                    </div>
                </div>
            </section>

            <section v-if="invoice.notes || invoice.terms" class="panel-card">
                <h2 class="panel-card-title">Notes &amp; terms</h2>
                <p v-if="invoice.notes" style="white-space: pre-line">{{ invoice.notes }}</p>
                <p v-if="invoice.terms" class="panel-cell-muted" style="white-space: pre-line">{{ invoice.terms }}</p>
            </section>
        </div>
    </ConsoleLayout>
</template>
