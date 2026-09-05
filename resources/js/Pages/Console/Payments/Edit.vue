<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import ConsoleLayout from '@/Layouts/ConsoleLayout.vue';
import PanelPageHeader from '@/Components/Panel/PanelPageHeader.vue';
import PaymentForm from './Partials/PaymentForm.vue';

const props = defineProps({
    payment: { type: Object, required: true },
    currencies: { type: Array, default: () => [] },
    baseCurrency: { type: String, required: true },
    methods: { type: Array, default: () => [] },
    clients: { type: Array, default: () => [] },
});

const form = useForm({
    client_id: props.payment.client_id,
    currency: props.payment.currency,
    fx_rate: props.payment.fx_rate,
    amount: props.payment.amount,
    method: props.payment.method,
    reference: props.payment.reference ?? '',
    paid_on: props.payment.paid_on,
    notes: props.payment.notes ?? '',
    allocations: props.payment.allocations.map((a) => ({
        invoice_id: a.invoice_id,
        amount: a.amount,
        _existing: true,
    })),
});

function submit() {
    form.put(route('console.payments.update', props.payment.id));
}
</script>

<template>
    <Head :title="`Edit ${payment.number}`" />

    <ConsoleLayout>
        <template #title>Edit {{ payment.number }}</template>

        <div class="panel-page">
            <PanelPageHeader :title="`Edit ${payment.number}`" subtitle="Changing the allocation re-settles the affected invoices.">
                <template #actions>
                    <Link :href="route('console.payments.show', payment.id)" class="panel-link">Cancel</Link>
                </template>
            </PanelPageHeader>

            <PaymentForm
                :form="form"
                :currencies="currencies"
                :base-currency="baseCurrency"
                :methods="methods"
                :clients="clients"
                submit-label="Save changes"
                @submit="submit"
            />
        </div>
    </ConsoleLayout>
</template>
