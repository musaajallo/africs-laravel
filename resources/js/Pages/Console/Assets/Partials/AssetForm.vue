<script setup>
import { computed, watch } from 'vue';
import PanelField from '@/Components/Panel/PanelField.vue';
import PanelButton from '@/Components/Panel/PanelButton.vue';

const props = defineProps({
    form: { type: Object, required: true },
    categories: { type: Object, default: () => ({}) },
    statuses: { type: Object, default: () => ({}) },
    conditions: { type: Object, default: () => ({}) },
    depreciationMethods: { type: Object, default: () => ({}) },
    currencies: { type: Array, default: () => [] },
    submitLabel: { type: String, default: 'Save' },
});

const emit = defineEmits(['submit']);

const hasCost = computed(() => props.form.purchase_cost !== '' && props.form.purchase_cost != null);
watch(hasCost, (has) => {
    if (has && !props.form.purchase_currency) props.form.purchase_currency = props.currencies[0] ?? '';
});

function err(key) {
    return props.form.errors[key];
}
</script>

<template>
    <form class="panel-form" @submit.prevent="emit('submit')">
        <div class="panel-card">
            <h2 class="panel-card-title">Item</h2>
            <div class="panel-form-grid">
                <PanelField label="Name" :error="err('name')" required>
                    <input v-model="form.name" type="text" class="field-input" placeholder="e.g. MacBook Pro 14 — Design" />
                </PanelField>
                <PanelField label="Category" :error="err('category')" required>
                    <select v-model="form.category" class="field-input">
                        <option v-for="(label, key) in categories" :key="key" :value="key">{{ label }}</option>
                    </select>
                </PanelField>
                <PanelField label="Make" :error="err('make')">
                    <input v-model="form.make" type="text" class="field-input" />
                </PanelField>
                <PanelField label="Model" :error="err('model')">
                    <input v-model="form.model" type="text" class="field-input" />
                </PanelField>
                <PanelField label="Manufactured" :error="err('manufactured_on')" hint="Date of manufacture, if known.">
                    <input v-model="form.manufactured_on" type="date" class="field-input" />
                </PanelField>
                <PanelField label="Serial number" :error="err('serial_number')">
                    <input v-model="form.serial_number" type="text" class="field-input" />
                </PanelField>
                <PanelField label="Asset tag" :error="err('asset_tag')" hint="Your internal inventory label.">
                    <input v-model="form.asset_tag" type="text" class="field-input" />
                </PanelField>
                <PanelField label="Status" :error="err('status')" required>
                    <select v-model="form.status" class="field-input">
                        <option v-for="(label, key) in statuses" :key="key" :value="key">{{ label }}</option>
                    </select>
                </PanelField>
                <PanelField label="Condition" :error="err('condition')">
                    <select v-model="form.condition" class="field-input">
                        <option value="">Not set</option>
                        <option v-for="(label, key) in conditions" :key="key" :value="key">{{ label }}</option>
                    </select>
                </PanelField>
                <PanelField label="Location" :error="err('location')" hint="Where it usually lives.">
                    <input v-model="form.location" type="text" class="field-input" placeholder="Head office" />
                </PanelField>
            </div>
        </div>

        <div class="panel-card">
            <h2 class="panel-card-title">Purchase</h2>
            <div class="panel-form-grid">
                <PanelField label="Purchased on" :error="err('purchased_on')">
                    <input v-model="form.purchased_on" type="date" class="field-input" />
                </PanelField>
                <PanelField label="Cost" :error="err('purchase_cost')">
                    <input v-model="form.purchase_cost" type="number" step="0.01" min="0" class="field-input" />
                </PanelField>
                <PanelField label="Currency" :error="err('purchase_currency')" :required="hasCost">
                    <select v-model="form.purchase_currency" class="field-input">
                        <option value="">—</option>
                        <option v-for="code in currencies" :key="code" :value="code">{{ code }}</option>
                    </select>
                </PanelField>
                <PanelField label="Supplier" :error="err('supplier')">
                    <input v-model="form.supplier" type="text" class="field-input" />
                </PanelField>
                <PanelField label="Warranty until" :error="err('warranty_until')">
                    <input v-model="form.warranty_until" type="date" class="field-input" />
                </PanelField>
            </div>
        </div>

        <div class="panel-card">
            <h2 class="panel-card-title">Depreciation</h2>
            <div class="panel-form-grid">
                <PanelField label="Method" :error="err('depreciation_method')" required>
                    <select v-model="form.depreciation_method" class="field-input">
                        <option v-for="(label, key) in depreciationMethods" :key="key" :value="key">{{ label }}</option>
                    </select>
                </PanelField>
                <PanelField label="In service from" :error="err('in_service_on')" hint="Depreciation start. Defaults to the purchase date.">
                    <input v-model="form.in_service_on" type="date" class="field-input" />
                </PanelField>
                <PanelField
                    v-if="form.depreciation_method === 'straight_line'"
                    label="Useful life (months)"
                    :error="err('useful_life_months')"
                    required
                >
                    <input v-model="form.useful_life_months" type="number" min="1" max="600" class="field-input" placeholder="e.g. 36" />
                </PanelField>
                <PanelField
                    v-if="form.depreciation_method === 'reducing_balance'"
                    label="Rate (% per year)"
                    :error="err('depreciation_rate')"
                    required
                >
                    <input v-model="form.depreciation_rate" type="number" step="0.001" min="0" max="100" class="field-input" placeholder="e.g. 25" />
                </PanelField>
                <PanelField
                    v-if="form.depreciation_method !== 'none'"
                    label="Salvage value"
                    :error="err('salvage_value')"
                    hint="Residual value at end of life."
                >
                    <input v-model="form.salvage_value" type="number" step="0.01" min="0" class="field-input" />
                </PanelField>
            </div>
        </div>

        <div class="panel-card">
            <h2 class="panel-card-title">Notes</h2>
            <PanelField :error="err('notes')">
                <textarea v-model="form.notes" rows="3" class="field-input"></textarea>
            </PanelField>
        </div>

        <div class="panel-form-actions">
            <PanelButton type="submit" :disabled="form.processing">{{ submitLabel }}</PanelButton>
            <slot name="secondary-action" />
        </div>
    </form>
</template>
