<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import ConsoleLayout from '@/Layouts/ConsoleLayout.vue';
import PanelPageHeader from '@/Components/Panel/PanelPageHeader.vue';
import DocumentForm from '@/Components/Panel/DocumentForm.vue';

const props = defineProps({
    proforma: { type: Object, required: true },
    currencies: { type: Array, default: () => [] },
    baseCurrency: { type: String, required: true },
    defaultTax: { type: Object, default: () => ({ label: 'VAT', rate: 0 }) },
    clients: { type: Array, default: () => [] },
    projects: { type: Array, default: () => [] },
});

const form = useForm({
    client_id: props.proforma.client_id,
    project_id: props.proforma.project_id ?? '',
    currency: props.proforma.currency,
    fx_rate: props.proforma.fx_rate,
    issue_date: props.proforma.issue_date,
    valid_until: props.proforma.valid_until ?? '',
    tax_label: props.proforma.tax_label,
    tax_rate: props.proforma.tax_rate,
    notes: props.proforma.notes ?? '',
    terms: props.proforma.terms ?? '',
    lines: props.proforma.lines.length
        ? props.proforma.lines.map((l) => ({
              description: l.description,
              quantity: l.quantity,
              unit_price: l.unit_price,
          }))
        : [{ description: '', quantity: '1', unit_price: '0' }],
});

function submit() {
    form.put(route('console.proformas.update', props.proforma.id));
}
</script>

<template>
    <Head :title="`Edit ${proforma.number}`" />

    <ConsoleLayout>
        <template #title>Edit {{ proforma.number }}</template>

        <div class="panel-page">
            <PanelPageHeader :title="`Edit ${proforma.number}`" subtitle="Only drafts can be edited.">
                <template #actions>
                    <Link :href="route('console.proformas.show', proforma.id)" class="panel-link">Cancel</Link>
                </template>
            </PanelPageHeader>

            <DocumentForm
                :form="form"
                :currencies="currencies"
                :base-currency="baseCurrency"
                :clients="clients"
                :projects="projects"
                date-field="valid_until"
                date-label="Valid until"
                submit-label="Save changes"
                @submit="submit"
            />
        </div>
    </ConsoleLayout>
</template>
