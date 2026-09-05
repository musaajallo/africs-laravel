<script setup>
import { computed, ref, watch } from 'vue';
import PanelField from '@/Components/Panel/PanelField.vue';
import PanelButton from '@/Components/Panel/PanelButton.vue';

const props = defineProps({
    form: { type: Object, required: true },
    currencies: { type: Array, default: () => [] },
    baseCurrency: { type: String, required: true },
    methods: { type: Array, default: () => [] },
    clients: { type: Array, default: () => [] },
    lockClient: { type: Boolean, default: false },
    submitLabel: { type: String, default: 'Save' },
});

const emit = defineEmits(['submit']);

const openInvoices = ref([]);
const loading = ref(false);

const isBase = computed(() => props.form.currency === props.baseCurrency);
watch(isBase, (base) => {
    if (base) props.form.fx_rate = '1';
});

async function loadInvoices() {
    if (!props.form.client_id || !props.form.currency) {
        openInvoices.value = [];
        return;
    }
    loading.value = true;
    try {
        const { data } = await window.axios.get(route('console.payments.invoices-for-client'), {
            params: { client: props.form.client_id, currency: props.form.currency },
        });
        openInvoices.value = data;
        // keep any existing allocation the payment already had, even if the
        // invoice now shows a zero balance
        const known = new Set(data.map((i) => i.id));
        props.form.allocations = props.form.allocations.filter(
            (a) => known.has(a.invoice_id) || a._existing,
        );
    } finally {
        loading.value = false;
    }
}

watch(() => [props.form.client_id, props.form.currency], loadInvoices, { immediate: true });

function allocationFor(id) {
    return props.form.allocations.find((a) => a.invoice_id === id);
}

function toggle(invoice) {
    const existing = allocationFor(invoice.id);
    if (existing) {
        props.form.allocations = props.form.allocations.filter((a) => a.invoice_id !== invoice.id);
    } else {
        const remaining = Math.max(0, unallocated.value);
        const amount = Math.min(remaining, Number(invoice.balance) || 0);
        props.form.allocations.push({ invoice_id: invoice.id, amount: amount ? String(amount.toFixed(2)) : '' });
    }
}

const allocated = computed(() =>
    props.form.allocations.reduce((sum, a) => sum + (Number(a.amount) || 0), 0),
);
const unallocated = computed(() => (Number(props.form.amount) || 0) - allocated.value);
const money = (n) => Number(n).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });

function allocError(id) {
    const i = props.form.allocations.findIndex((a) => a.invoice_id === id);
    return i === -1 ? null : props.form.errors[`allocations.${i}.amount`] || props.form.errors[`allocations.${i}.invoice_id`];
}
</script>

<template>
    <form class="panel-form" @submit.prevent="emit('submit')">
        <div class="panel-card">
            <h2 class="panel-card-title">Payment</h2>
            <div class="panel-form-grid">
                <PanelField label="Client" :error="form.errors.client_id" required>
                    <select v-model="form.client_id" class="field-input" :disabled="lockClient">
                        <option value="" disabled>Choose a client</option>
                        <option v-for="c in clients" :key="c.id" :value="c.id">{{ c.name }}</option>
                    </select>
                </PanelField>
                <PanelField label="Date received" :error="form.errors.paid_on" required>
                    <input v-model="form.paid_on" type="date" class="field-input" />
                </PanelField>
                <PanelField label="Currency" :error="form.errors.currency" required>
                    <select v-model="form.currency" class="field-input">
                        <option v-for="code in currencies" :key="code" :value="code">{{ code }}</option>
                    </select>
                </PanelField>
                <PanelField
                    label="Exchange rate"
                    :error="form.errors.fx_rate"
                    :hint="isBase ? 'Base currency — always 1' : `1 ${form.currency} in ${baseCurrency}`"
                    required
                >
                    <input v-model="form.fx_rate" type="number" step="0.0001" min="0" class="field-input" :disabled="isBase" />
                </PanelField>
                <PanelField label="Amount received" :error="form.errors.amount" required>
                    <input v-model="form.amount" type="number" step="0.01" min="0" class="field-input" />
                </PanelField>
                <PanelField label="Method" :error="form.errors.method" required>
                    <select v-model="form.method" class="field-input">
                        <option value="" disabled>Choose a method</option>
                        <option v-for="m in methods" :key="m" :value="m">{{ m }}</option>
                    </select>
                </PanelField>
                <PanelField label="Reference" :error="form.errors.reference" hint="Transfer ID, cheque no.">
                    <input v-model="form.reference" type="text" class="field-input" />
                </PanelField>
            </div>
            <PanelField label="Notes" :error="form.errors.notes">
                <textarea v-model="form.notes" rows="2" class="field-input"></textarea>
            </PanelField>
        </div>

        <div class="panel-card">
            <h2 class="panel-card-title">Apply to invoices</h2>
            <p v-if="form.errors.allocations" class="panel-field-error">{{ form.errors.allocations }}</p>

            <p v-if="!form.client_id" class="panel-cell-muted">Choose a client to see their open invoices.</p>
            <p v-else-if="loading" class="panel-cell-muted">Loading…</p>
            <p v-else-if="!openInvoices.length" class="panel-cell-muted">
                No open invoices for this client in {{ form.currency }}. The payment will sit as an unapplied credit.
            </p>

            <table v-else class="doc-lines">
                <thead>
                    <tr>
                        <th></th>
                        <th>Invoice</th>
                        <th class="num">Balance</th>
                        <th class="num">Apply</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="inv in openInvoices" :key="inv.id">
                        <td>
                            <input type="checkbox" :checked="!!allocationFor(inv.id)" @change="toggle(inv)" />
                        </td>
                        <td>
                            {{ inv.number }}
                            <span class="panel-cell-muted"> · due {{ inv.due_date || '—' }}</span>
                        </td>
                        <td class="num">{{ form.currency }} {{ money(inv.balance) }}</td>
                        <td class="num">
                            <input
                                v-if="allocationFor(inv.id)"
                                v-model="allocationFor(inv.id).amount"
                                type="number"
                                step="0.01"
                                min="0"
                                class="field-input"
                                style="width: 8rem"
                            />
                            <span v-else>—</span>
                            <p v-if="allocError(inv.id)" class="panel-field-error">{{ allocError(inv.id) }}</p>
                        </td>
                    </tr>
                </tbody>
            </table>

            <div class="doc-totals" style="margin-top: 1rem">
                <div class="doc-totals-row"><span>Amount received</span><span>{{ form.currency }} {{ money(form.amount || 0) }}</span></div>
                <div class="doc-totals-row"><span>Applied</span><span>{{ form.currency }} {{ money(allocated) }}</span></div>
                <div class="doc-totals-row is-grand" :style="unallocated < 0 ? 'color: var(--color-danger, #b3261e)' : ''">
                    <span>{{ unallocated < 0 ? 'Over-applied' : 'Unapplied credit' }}</span>
                    <span>{{ form.currency }} {{ money(Math.abs(unallocated)) }}</span>
                </div>
            </div>
        </div>

        <div class="panel-form-actions">
            <PanelButton type="submit" :disabled="form.processing || unallocated < 0">{{ submitLabel }}</PanelButton>
            <slot name="secondary-action" />
        </div>
    </form>
</template>
