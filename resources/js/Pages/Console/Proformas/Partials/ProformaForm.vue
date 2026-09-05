<script setup>
import { computed, watch } from 'vue';
import PanelField from '@/Components/Panel/PanelField.vue';
import PanelButton from '@/Components/Panel/PanelButton.vue';

const props = defineProps({
    form: { type: Object, required: true },
    currencies: { type: Array, default: () => [] },
    baseCurrency: { type: String, required: true },
    clients: { type: Array, default: () => [] },
    projects: { type: Array, default: () => [] },
    submitLabel: { type: String, default: 'Save' },
});

const emit = defineEmits(['submit']);

const clientProjects = computed(() =>
    props.projects.filter((p) => p.client_id === Number(props.form.client_id)),
);

// Clear a project that no longer belongs to the chosen client.
watch(
    () => props.form.client_id,
    () => {
        if (props.form.project_id && !clientProjects.value.some((p) => p.id === Number(props.form.project_id))) {
            props.form.project_id = '';
        }
    },
);

const isBase = computed(() => props.form.currency === props.baseCurrency);
watch(isBase, (base) => {
    if (base) props.form.fx_rate = '1';
});

function addLine() {
    props.form.lines.push({ description: '', quantity: '1', unit_price: '0' });
}

function removeLine(i) {
    props.form.lines.splice(i, 1);
    if (props.form.lines.length === 0) addLine();
}

const lineTotal = (line) => (Number(line.quantity) || 0) * (Number(line.unit_price) || 0);
const subtotal = computed(() => props.form.lines.reduce((sum, l) => sum + lineTotal(l), 0));
const taxTotal = computed(() => (subtotal.value * (Number(props.form.tax_rate) || 0)) / 100);
const grandTotal = computed(() => subtotal.value + taxTotal.value);
const money = (n) => n.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });

function lineError(i, field) {
    return props.form.errors[`lines.${i}.${field}`];
}
</script>

<template>
    <form class="panel-form" @submit.prevent="emit('submit')">
        <div class="panel-card">
            <h2 class="panel-card-title">Details</h2>
            <div class="panel-form-grid">
                <PanelField label="Client" :error="form.errors.client_id" required>
                    <select v-model="form.client_id" class="field-input">
                        <option value="" disabled>Choose a client</option>
                        <option v-for="c in clients" :key="c.id" :value="c.id">{{ c.name }}</option>
                    </select>
                </PanelField>
                <PanelField label="Project" :error="form.errors.project_id" hint="Optional">
                    <select v-model="form.project_id" class="field-input" :disabled="!clientProjects.length">
                        <option value="">None</option>
                        <option v-for="p in clientProjects" :key="p.id" :value="p.id">{{ p.name }}</option>
                    </select>
                </PanelField>
                <PanelField label="Issue date" :error="form.errors.issue_date" required>
                    <input v-model="form.issue_date" type="date" class="field-input" />
                </PanelField>
                <PanelField label="Valid until" :error="form.errors.valid_until" hint="Optional">
                    <input v-model="form.valid_until" type="date" class="field-input" />
                </PanelField>
                <PanelField label="Currency" :error="form.errors.currency" required>
                    <select v-model="form.currency" class="field-input">
                        <option v-for="code in currencies" :key="code" :value="code">{{ code }}</option>
                    </select>
                </PanelField>
                <PanelField
                    label="Exchange rate"
                    :error="form.errors.fx_rate"
                    :hint="isBase ? 'Base currency — always 1' : `1 ${form.currency} in ${baseCurrency}, snapshotted on this proforma`"
                    required
                >
                    <input v-model="form.fx_rate" type="number" step="0.0001" min="0" class="field-input" :disabled="isBase" />
                </PanelField>
                <PanelField label="Tax label" :error="form.errors.tax_label">
                    <input v-model="form.tax_label" type="text" class="field-input" placeholder="VAT" />
                </PanelField>
                <PanelField label="Tax rate (%)" :error="form.errors.tax_rate" required>
                    <input v-model="form.tax_rate" type="number" step="0.001" min="0" max="100" class="field-input" />
                </PanelField>
            </div>
        </div>

        <div class="panel-card">
            <h2 class="panel-card-title">Line items</h2>
            <p v-if="form.errors.lines" class="panel-field-error">{{ form.errors.lines }}</p>

            <table class="doc-lines">
                <thead>
                    <tr>
                        <th style="width: 50%">Description</th>
                        <th class="num">Qty</th>
                        <th class="num">Unit price</th>
                        <th class="num">Amount</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="(line, i) in form.lines" :key="i">
                        <td>
                            <input v-model="line.description" type="text" class="field-input" placeholder="What is being quoted" />
                            <p v-if="lineError(i, 'description')" class="panel-field-error">{{ lineError(i, 'description') }}</p>
                        </td>
                        <td class="num">
                            <input v-model="line.quantity" type="number" step="0.001" min="0" class="field-input" style="width: 6rem" />
                            <p v-if="lineError(i, 'quantity')" class="panel-field-error">{{ lineError(i, 'quantity') }}</p>
                        </td>
                        <td class="num">
                            <input v-model="line.unit_price" type="number" step="0.01" min="0" class="field-input" style="width: 8rem" />
                            <p v-if="lineError(i, 'unit_price')" class="panel-field-error">{{ lineError(i, 'unit_price') }}</p>
                        </td>
                        <td class="num">{{ money(lineTotal(line)) }}</td>
                        <td class="num">
                            <button type="button" class="panel-link is-danger" @click="removeLine(i)">Remove</button>
                        </td>
                    </tr>
                </tbody>
            </table>

            <PanelButton variant="secondary" @click="addLine">Add line</PanelButton>

            <div class="doc-totals" style="margin-top: 1rem">
                <div class="doc-totals-row"><span>Subtotal</span><span>{{ form.currency }} {{ money(subtotal) }}</span></div>
                <div class="doc-totals-row">
                    <span>{{ form.tax_label || 'Tax' }} ({{ form.tax_rate || 0 }}%)</span>
                    <span>{{ form.currency }} {{ money(taxTotal) }}</span>
                </div>
                <div class="doc-totals-row is-grand"><span>Total</span><span>{{ form.currency }} {{ money(grandTotal) }}</span></div>
            </div>
        </div>

        <div class="panel-card">
            <h2 class="panel-card-title">Notes &amp; terms</h2>
            <div class="panel-form-grid">
                <PanelField label="Notes" :error="form.errors.notes" hint="Shown on the PDF">
                    <textarea v-model="form.notes" rows="3" class="field-input"></textarea>
                </PanelField>
                <PanelField label="Terms" :error="form.errors.terms" hint="Payment terms, small print">
                    <textarea v-model="form.terms" rows="3" class="field-input"></textarea>
                </PanelField>
            </div>
        </div>

        <div class="panel-form-actions">
            <PanelButton type="submit" :disabled="form.processing">{{ submitLabel }}</PanelButton>
            <slot name="secondary-action" />
        </div>
    </form>
</template>
