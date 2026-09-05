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
    payment: { type: Object, required: true },
    activity: { type: Array, default: () => [] },
});

const { can } = useAuth();
const canManage = computed(() => can('payments.manage') && !props.payment.archived);
const money = (n) =>
    `${props.payment.currency} ${Number(n).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;

const details = computed(() => [
    { label: 'Client', value: props.payment.client, href: route('console.clients.show', props.payment.client_id) },
    { label: 'Date received', value: props.payment.paid_on },
    { label: 'Method', value: props.payment.method },
    { label: 'Reference', value: props.payment.reference },
    { label: 'Exchange rate', value: `1 ${props.payment.currency} = ${props.payment.fx_rate}` },
    { label: 'Recorded by', value: props.payment.created_by },
]);

const confirmRemove = ref(false);

function remove() {
    router.delete(route('console.payments.destroy', props.payment.id));
}
function restore() {
    router.put(route('console.payments.restore', props.payment.id));
}
</script>

<template>
    <Head :title="payment.number" />

    <ConsoleLayout>
        <template #title>Payments</template>

        <div class="panel-page">
            <PanelPageHeader
                :title="payment.number"
                :subtitle="payment.archived ? 'Removed payment' : null"
            >
                <template #actions>
                    <PanelButton variant="secondary" :href="route('console.payments.index')">All payments</PanelButton>

                    <PanelActions>
                        <a :href="route('console.payments.pdf', payment.id)" class="panel-actions-item">Download receipt</a>
                        <Link
                            v-if="canManage"
                            :href="route('console.payments.edit', payment.id)"
                            class="panel-actions-item"
                        >Edit</Link>
                        <button
                            v-if="can('payments.manage') && payment.archived"
                            type="button"
                            class="panel-actions-item"
                            @click="restore"
                        >Restore</button>
                        <template v-if="canManage">
                            <div class="panel-actions-sep"></div>
                            <button type="button" class="panel-actions-item is-danger" @click="confirmRemove = true">
                                Remove
                            </button>
                        </template>
                    </PanelActions>
                </template>
            </PanelPageHeader>

            <PanelConfirm
                v-model="confirmRemove"
                title="Remove this payment?"
                message="The invoices it was applied to will be re-opened by the amount released."
                confirm-label="Remove"
                @confirm="remove"
            />

            <div class="client-detail-grid">
                <section class="panel-card">
                    <h2 class="panel-card-title">Payment</h2>
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
                    <div class="doc-totals" style="margin-top: 0.5rem">
                        <div class="doc-totals-row"><span>Received</span><span>{{ money(payment.amount) }}</span></div>
                        <div class="doc-totals-row"><span>Applied</span><span>{{ money(payment.allocated_amount) }}</span></div>
                        <div v-if="Number(payment.unallocated_amount) > 0" class="doc-totals-row">
                            <span>Unapplied credit</span><span>{{ money(payment.unallocated_amount) }}</span>
                        </div>
                    </div>
                </section>

                <section class="panel-card">
                    <h2 class="panel-card-title">Activity</h2>
                    <ActivityFeed :items="activity" empty="No changes recorded yet." />
                </section>
            </div>

            <section class="panel-card">
                <h2 class="panel-card-title">Applied to</h2>
                <p v-if="!payment.allocations.length" class="panel-cell-muted">Not applied to any invoice.</p>
                <table v-else class="doc-lines">
                    <thead>
                        <tr><th>Invoice</th><th>Status</th><th class="num">Amount</th></tr>
                    </thead>
                    <tbody>
                        <tr v-for="a in payment.allocations" :key="a.invoice_id">
                            <td>
                                <Link :href="route('console.invoices.show', a.invoice_id)" class="panel-link" style="padding: 0">
                                    {{ a.invoice_number }}
                                </Link>
                            </td>
                            <td><DocumentStatusBadge v-if="a.invoice_status" :status="a.invoice_status" /></td>
                            <td class="num">{{ money(a.amount) }}</td>
                        </tr>
                    </tbody>
                </table>
            </section>

            <section v-if="payment.notes" class="panel-card">
                <h2 class="panel-card-title">Notes</h2>
                <p style="white-space: pre-line">{{ payment.notes }}</p>
            </section>
        </div>
    </ConsoleLayout>
</template>
