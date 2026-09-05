<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import ConsoleLayout from '@/Layouts/ConsoleLayout.vue';
import PanelPageHeader from '@/Components/Panel/PanelPageHeader.vue';
import DocumentForm from '@/Components/Panel/DocumentForm.vue';

const props = defineProps({
    currencies: { type: Array, default: () => [] },
    baseCurrency: { type: String, required: true },
    defaultTax: { type: Object, default: () => ({ label: 'VAT', rate: 0 }) },
    paymentTermsDays: { type: Number, default: 30 },
    clients: { type: Array, default: () => [] },
    projects: { type: Array, default: () => [] },
    presetClientId: { type: Number, default: null },
    presetProjectId: { type: Number, default: null },
    nextNumber: { type: String, default: '' },
});

const today = new Date();
const iso = (d) => d.toISOString().slice(0, 10);
const due = new Date(today);
due.setDate(due.getDate() + props.paymentTermsDays);

const form = useForm({
    client_id: props.presetClientId ?? '',
    project_id: props.presetProjectId ?? '',
    currency: props.baseCurrency,
    fx_rate: '1',
    issue_date: iso(today),
    due_date: iso(due),
    tax_label: props.defaultTax.label,
    tax_rate: String(props.defaultTax.rate ?? 0),
    notes: '',
    terms: '',
    lines: [{ description: '', quantity: '1', unit_price: '0' }],
});

function submit() {
    form.post(route('console.invoices.store'));
}
</script>

<template>
    <Head title="New invoice" />

    <ConsoleLayout>
        <template #title>New invoice</template>

        <div class="panel-page">
            <PanelPageHeader title="New invoice" :subtitle="nextNumber ? `Will be numbered ${nextNumber}` : null">
                <template #actions>
                    <Link :href="route('console.invoices.index')" class="panel-link">Back to invoices</Link>
                </template>
            </PanelPageHeader>

            <DocumentForm
                :form="form"
                :currencies="currencies"
                :base-currency="baseCurrency"
                :clients="clients"
                :projects="projects"
                date-field="due_date"
                date-label="Due date"
                submit-label="Create invoice"
                @submit="submit"
            />
        </div>
    </ConsoleLayout>
</template>
