<script setup>
import { computed, ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import ConsoleLayout from '@/Layouts/ConsoleLayout.vue';
import PanelPageHeader from '@/Components/Panel/PanelPageHeader.vue';
import PanelButton from '@/Components/Panel/PanelButton.vue';
import PanelActions from '@/Components/Panel/PanelActions.vue';
import PanelConfirm from '@/Components/Panel/PanelConfirm.vue';
import DocumentStatusBadge from '@/Components/Panel/DocumentStatusBadge.vue';
import ActivityFeed from '@/Components/Panel/ActivityFeed.vue';
import { useAuth } from '@/Composables/useAuth.js';

const props = defineProps({
    proforma: { type: Object, required: true },
    manualStatuses: { type: Array, default: () => [] },
    activity: { type: Array, default: () => [] },
});

const { can } = useAuth();
const canManage = computed(() => can('proformas.manage') && !props.proforma.archived);
const locked = computed(() => props.proforma.status === 'converted');

const money = (n) =>
    `${props.proforma.currency} ${Number(n).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;

const statusLabels = {
    sent: 'Mark sent',
    accepted: 'Mark accepted',
    declined: 'Mark declined',
    expired: 'Mark expired',
};

const details = computed(() => [
    { label: 'Client', value: props.proforma.client, href: route('console.clients.show', props.proforma.client_id) },
    { label: 'Project', value: props.proforma.project, href: props.proforma.project_id ? route('console.projects.show', props.proforma.project_id) : null },
    { label: 'Issued', value: props.proforma.issue_date },
    { label: 'Valid until', value: props.proforma.valid_until },
    { label: 'Exchange rate', value: `1 ${props.proforma.currency} = ${props.proforma.fx_rate}` },
    { label: 'Base total', value: money(props.proforma.base_total).replace(props.proforma.currency, '') },
    { label: 'Created by', value: props.proforma.created_by },
]);

const confirmConvert = ref(false);
const confirmArchive = ref(false);

function setStatus(status) {
    router.put(route('console.proformas.status', props.proforma.id), { status }, { preserveScroll: true });
}
function convert() {
    router.post(route('console.proformas.convert', props.proforma.id));
}
function archive() {
    router.delete(route('console.proformas.destroy', props.proforma.id));
}
function restore() {
    router.put(route('console.proformas.restore', props.proforma.id));
}
</script>

<template>
    <Head :title="proforma.number" />

    <ConsoleLayout>
        <template #title>Proformas</template>

        <div class="panel-page">
            <PanelPageHeader
                :title="proforma.number"
                :subtitle="proforma.archived ? 'Archived proforma' : null"
            >
                <template #actions>
                    <PanelButton variant="secondary" :href="route('console.proformas.index')">All proformas</PanelButton>

                    <PanelActions>
                        <button
                            v-if="canManage && proforma.can_convert"
                            type="button"
                            class="panel-actions-item"
                            @click="confirmConvert = true"
                        >Convert to invoice</button>
                        <a :href="route('console.proformas.pdf', proforma.id)" class="panel-actions-item">Download PDF</a>
                        <Link
                            v-if="canManage && proforma.editable"
                            :href="route('console.proformas.edit', proforma.id)"
                            class="panel-actions-item"
                        >Edit</Link>
                        <button
                            v-if="can('proformas.manage') && proforma.archived"
                            type="button"
                            class="panel-actions-item"
                            @click="restore"
                        >Restore</button>
                        <template v-if="canManage">
                            <div class="panel-actions-sep"></div>
                            <button type="button" class="panel-actions-item is-danger" @click="confirmArchive = true">
                                Archive
                            </button>
                        </template>
                    </PanelActions>
                </template>
            </PanelPageHeader>

            <PanelConfirm
                v-model="confirmConvert"
                title="Convert to an invoice?"
                :message="`A draft invoice will be created from ${proforma.number}. This proforma is then locked.`"
                confirm-label="Convert"
                confirm-variant="primary"
                @confirm="convert"
            />
            <PanelConfirm
                v-model="confirmArchive"
                title="Archive this proforma?"
                :message="`${proforma.number} will be hidden from the list. It can be restored.`"
                confirm-label="Archive"
                @confirm="archive"
            />

            <div style="margin: -0.25rem 0 1.25rem; display: flex; align-items: center; gap: 0.6rem; flex-wrap: wrap">
                <DocumentStatusBadge :status="proforma.status" />
                <span v-if="proforma.converted_invoice" class="panel-cell-muted">
                    Converted to
                    <Link :href="route('console.invoices.show', proforma.converted_invoice.id)" class="panel-link" style="padding: 0">
                        {{ proforma.converted_invoice.number }}
                    </Link>
                </span>
            </div>

            <div v-if="canManage && !locked" class="panel-toolbar" style="margin-bottom: 1.25rem">
                <PanelButton
                    v-for="s in manualStatuses"
                    :key="s"
                    variant="secondary"
                    :disabled="proforma.status === s"
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
                        <tr v-for="line in proforma.lines" :key="line.id">
                            <td>{{ line.description }}</td>
                            <td class="num">{{ line.quantity }}</td>
                            <td class="num">{{ money(line.unit_price) }}</td>
                            <td class="num">{{ money(line.line_total) }}</td>
                        </tr>
                    </tbody>
                </table>

                <div class="doc-totals">
                    <div class="doc-totals-row"><span>Subtotal</span><span>{{ money(proforma.subtotal) }}</span></div>
                    <div class="doc-totals-row">
                        <span>{{ proforma.tax_label }} ({{ proforma.tax_rate }}%)</span>
                        <span>{{ money(proforma.tax_total) }}</span>
                    </div>
                    <div class="doc-totals-row is-grand"><span>Total</span><span>{{ money(proforma.total) }}</span></div>
                </div>
            </section>

            <section v-if="proforma.notes || proforma.terms" class="panel-card">
                <h2 class="panel-card-title">Notes &amp; terms</h2>
                <p v-if="proforma.notes" style="white-space: pre-line">{{ proforma.notes }}</p>
                <p v-if="proforma.terms" class="panel-cell-muted" style="white-space: pre-line">{{ proforma.terms }}</p>
            </section>
        </div>
    </ConsoleLayout>
</template>
