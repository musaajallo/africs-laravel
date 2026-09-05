<script setup>
import { computed, watch } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';
import ConsoleLayout from '@/Layouts/ConsoleLayout.vue';
import PanelPageHeader from '@/Components/Panel/PanelPageHeader.vue';
import PanelField from '@/Components/Panel/PanelField.vue';
import PanelButton from '@/Components/Panel/PanelButton.vue';
import { useAuth } from '@/Composables/useAuth.js';

const props = defineProps({
    settings: { type: Object, required: true },
    supportedCurrencies: { type: Array, default: () => [] },
});

const { can } = useAuth();
const canManage = computed(() => can('settings.manage'));

const form = useForm({
    company: { ...props.settings.company },
    currency: {
        enabled: [...props.settings.currency.enabled],
        base: props.settings.currency.base,
    },
    billing: {
        ...props.settings.billing,
        payment_methods: [...(props.settings.billing.payment_methods ?? [])],
    },
});

const paymentMethodsText = computed({
    get: () => (form.billing.payment_methods ?? []).join('\n'),
    set: (value) => {
        form.billing.payment_methods = value
            .split('\n')
            .map((m) => m.trim())
            .filter(Boolean);
    },
});

function toggleCurrency(code) {
    const i = form.currency.enabled.indexOf(code);
    if (i === -1) form.currency.enabled.push(code);
    else form.currency.enabled.splice(i, 1);
}

// keep the base currency valid when the enabled set changes
watch(
    () => form.currency.enabled.slice(),
    (enabled) => {
        if (!enabled.includes(form.currency.base)) {
            form.currency.base = enabled[0] ?? '';
        }
    },
);

function err(key) {
    return form.errors[key];
}

function submit() {
    form.put(route('console.settings.update'), { preserveScroll: true });
}
</script>

<template>
    <Head title="Settings" />

    <ConsoleLayout>
        <template #title>Settings</template>

        <div class="panel-page">
            <PanelPageHeader
                title="Settings"
                subtitle="Company details and the defaults every other module reads."
            />

            <form class="panel-form" @submit.prevent="submit">
                <div class="panel-card">
                    <h2 class="panel-card-title">Company</h2>
                    <div class="panel-form-grid">
                        <PanelField label="Trading name" :error="err('company.name')" required>
                            <input v-model="form.company.name" type="text" class="field-input" :disabled="!canManage" />
                        </PanelField>
                        <PanelField label="Legal name" :error="err('company.legal_name')">
                            <input v-model="form.company.legal_name" type="text" class="field-input" :disabled="!canManage" />
                        </PanelField>
                        <PanelField label="Email" :error="err('company.email')">
                            <input v-model="form.company.email" type="email" class="field-input" :disabled="!canManage" />
                        </PanelField>
                        <PanelField label="Phone" :error="err('company.phone')">
                            <input v-model="form.company.phone" type="text" class="field-input" :disabled="!canManage" />
                        </PanelField>
                        <PanelField label="Tax / TIN number" :error="err('company.tax_number')">
                            <input v-model="form.company.tax_number" type="text" class="field-input" :disabled="!canManage" />
                        </PanelField>
                        <PanelField label="City" :error="err('company.city')">
                            <input v-model="form.company.city" type="text" class="field-input" :disabled="!canManage" />
                        </PanelField>
                        <PanelField label="Country" :error="err('company.country')" hint="Two-letter code, e.g. GM.">
                            <input v-model="form.company.country" type="text" maxlength="2" class="field-input" style="text-transform: uppercase" :disabled="!canManage" />
                        </PanelField>
                    </div>
                    <PanelField label="Address" :error="err('company.address')">
                        <textarea v-model="form.company.address" rows="3" class="field-input" :disabled="!canManage"></textarea>
                    </PanelField>
                </div>

                <div class="panel-card" style="margin-top: 1rem">
                    <h2 class="panel-card-title">Currencies</h2>
                    <PanelField
                        label="Enabled currencies"
                        :error="err('currency.enabled')"
                        hint="Currencies you can invoice clients in."
                    >
                        <div class="panel-checkbox-list">
                            <label v-for="code in supportedCurrencies" :key="code" class="panel-checkbox">
                                <input
                                    type="checkbox"
                                    :checked="form.currency.enabled.includes(code)"
                                    :disabled="!canManage"
                                    @change="toggleCurrency(code)"
                                />
                                <span>{{ code }}</span>
                            </label>
                        </div>
                    </PanelField>
                    <PanelField
                        label="Base currency"
                        :error="err('currency.base')"
                        hint="Reporting and consolidated totals are shown in this currency."
                        style="margin-top: 1rem; max-width: 16rem"
                    >
                        <select v-model="form.currency.base" class="field-input" :disabled="!canManage">
                            <option v-for="code in form.currency.enabled" :key="code" :value="code">{{ code }}</option>
                        </select>
                    </PanelField>
                </div>

                <div class="panel-card" style="margin-top: 1rem">
                    <h2 class="panel-card-title">Invoicing defaults</h2>
                    <div class="panel-form-grid">
                        <PanelField label="Tax label" :error="err('billing.tax_label')" hint="e.g. VAT, GST, NHIL.">
                            <input v-model="form.billing.tax_label" type="text" class="field-input" :disabled="!canManage" />
                        </PanelField>
                        <PanelField label="Default tax rate (%)" :error="err('billing.tax_rate')">
                            <input v-model="form.billing.tax_rate" type="number" step="0.01" min="0" max="100" class="field-input" :disabled="!canManage" />
                        </PanelField>
                        <PanelField label="Payment terms (days)" :error="err('billing.payment_terms_days')" hint="Default due date on new invoices.">
                            <input v-model="form.billing.payment_terms_days" type="number" min="0" max="365" class="field-input" :disabled="!canManage" />
                        </PanelField>
                    </div>
                    <PanelField
                        label="Payment methods"
                        :error="err('billing.payment_methods') || err('billing.payment_methods.0')"
                        hint="One per line. Offered when recording a payment."
                        style="margin-top: 1rem; max-width: 24rem"
                    >
                        <textarea v-model="paymentMethodsText" rows="5" class="field-input" :disabled="!canManage"></textarea>
                    </PanelField>
                </div>

                <div v-if="canManage" class="panel-form-actions">
                    <PanelButton type="submit" :disabled="form.processing">
                        {{ form.processing ? 'Saving…' : 'Save settings' }}
                    </PanelButton>
                </div>
            </form>
        </div>
    </ConsoleLayout>
</template>
