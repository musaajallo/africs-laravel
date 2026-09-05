<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import ConsoleLayout from '@/Layouts/ConsoleLayout.vue';
import PanelPageHeader from '@/Components/Panel/PanelPageHeader.vue';
import ProformaForm from './Partials/ProformaForm.vue';

const props = defineProps({
    currencies: { type: Array, default: () => [] },
    baseCurrency: { type: String, required: true },
    defaultTax: { type: Object, default: () => ({ label: 'VAT', rate: 0 }) },
    clients: { type: Array, default: () => [] },
    projects: { type: Array, default: () => [] },
    presetClientId: { type: Number, default: null },
    presetProjectId: { type: Number, default: null },
    nextNumber: { type: String, default: '' },
});

const today = new Date().toISOString().slice(0, 10);

const form = useForm({
    client_id: props.presetClientId ?? '',
    project_id: props.presetProjectId ?? '',
    currency: props.baseCurrency,
    fx_rate: '1',
    issue_date: today,
    valid_until: '',
    tax_label: props.defaultTax.label,
    tax_rate: String(props.defaultTax.rate ?? 0),
    notes: '',
    terms: '',
    lines: [{ description: '', quantity: '1', unit_price: '0' }],
});

function submit() {
    form.post(route('console.proformas.store'));
}
</script>

<template>
    <Head title="New proforma" />

    <ConsoleLayout>
        <template #title>New proforma</template>

        <div class="panel-page">
            <PanelPageHeader title="New proforma" :subtitle="nextNumber ? `Will be numbered ${nextNumber}` : null">
                <template #actions>
                    <Link :href="route('console.proformas.index')" class="panel-link">Back to proformas</Link>
                </template>
            </PanelPageHeader>

            <ProformaForm
                :form="form"
                :currencies="currencies"
                :base-currency="baseCurrency"
                :clients="clients"
                :projects="projects"
                submit-label="Create proforma"
                @submit="submit"
            />
        </div>
    </ConsoleLayout>
</template>
