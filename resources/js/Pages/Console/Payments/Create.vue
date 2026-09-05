<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import ConsoleLayout from '@/Layouts/ConsoleLayout.vue';
import PanelPageHeader from '@/Components/Panel/PanelPageHeader.vue';
import PaymentForm from './Partials/PaymentForm.vue';

const props = defineProps({
    currencies: { type: Array, default: () => [] },
    baseCurrency: { type: String, required: true },
    methods: { type: Array, default: () => [] },
    clients: { type: Array, default: () => [] },
    nextNumber: { type: String, default: '' },
    presetInvoice: { type: Object, default: null },
});

const today = new Date().toISOString().slice(0, 10);
const preset = props.presetInvoice;

const form = useForm({
    client_id: preset?.client_id ?? '',
    currency: preset?.currency ?? props.baseCurrency,
    fx_rate: '1',
    amount: preset ? preset.balance : '',
    method: props.methods[0] ?? '',
    reference: '',
    paid_on: today,
    notes: '',
    allocations: preset ? [{ invoice_id: preset.id, amount: preset.balance }] : [],
});

function submit() {
    form.post(route('console.payments.store'));
}
</script>

<template>
    <Head title="Record payment" />

    <ConsoleLayout>
        <template #title>Record payment</template>

        <div class="panel-page">
            <PanelPageHeader title="Record payment" :subtitle="nextNumber ? `Receipt ${nextNumber}` : null">
                <template #actions>
                    <Link :href="route('console.payments.index')" class="panel-link">Back to payments</Link>
                </template>
            </PanelPageHeader>

            <PaymentForm
                :form="form"
                :currencies="currencies"
                :base-currency="baseCurrency"
                :methods="methods"
                :clients="clients"
                :lock-client="!!preset"
                submit-label="Record payment"
                @submit="submit"
            />
        </div>
    </ConsoleLayout>
</template>
