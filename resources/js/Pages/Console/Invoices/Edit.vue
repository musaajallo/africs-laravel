<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import ConsoleLayout from '@/Layouts/ConsoleLayout.vue';
import PanelPageHeader from '@/Components/Panel/PanelPageHeader.vue';
import DocumentForm from '@/Components/Panel/DocumentForm.vue';

const props = defineProps({
    invoice: { type: Object, required: true },
    currencies: { type: Array, default: () => [] },
    baseCurrency: { type: String, required: true },
    defaultTax: { type: Object, default: () => ({ label: 'VAT', rate: 0 }) },
    paymentTermsDays: { type: Number, default: 30 },
    clients: { type: Array, default: () => [] },
    projects: { type: Array, default: () => [] },
});

const form = useForm({
    client_id: props.invoice.client_id,
    project_id: props.invoice.project_id ?? '',
    currency: props.invoice.currency,
    fx_rate: props.invoice.fx_rate,
    issue_date: props.invoice.issue_date,
    due_date: props.invoice.due_date ?? '',
    tax_label: props.invoice.tax_label,
    tax_rate: props.invoice.tax_rate,
    notes: props.invoice.notes ?? '',
    terms: props.invoice.terms ?? '',
    lines: props.invoice.lines.length
        ? props.invoice.lines.map((l) => ({
              description: l.description,
              quantity: l.quantity,
              unit_price: l.unit_price,
          }))
        : [{ description: '', quantity: '1', unit_price: '0' }],
});

function submit() {
    form.put(route('console.invoices.update', props.invoice.id));
}
</script>

<template>
    <Head :title="`Edit ${invoice.number}`" />

    <ConsoleLayout>
        <template #title>Edit {{ invoice.number }}</template>

        <div class="panel-page">
            <PanelPageHeader :title="`Edit ${invoice.number}`" subtitle="Only drafts can be edited.">
                <template #actions>
                    <Link :href="route('console.invoices.show', invoice.id)" class="panel-link">Cancel</Link>
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
                submit-label="Save changes"
                @submit="submit"
            />
        </div>
    </ConsoleLayout>
</template>
