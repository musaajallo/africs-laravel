<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import ConsoleLayout from '@/Layouts/ConsoleLayout.vue';
import PanelPageHeader from '@/Components/Panel/PanelPageHeader.vue';
import AssetForm from './Partials/AssetForm.vue';

const props = defineProps({
    categories: { type: Object, default: () => ({}) },
    statuses: { type: Object, default: () => ({}) },
    conditions: { type: Object, default: () => ({}) },
    currencies: { type: Array, default: () => [] },
});

const form = useForm({
    name: '',
    category: 'laptop',
    make: '',
    model: '',
    serial_number: '',
    asset_tag: '',
    status: 'spare',
    condition: '',
    purchased_on: '',
    purchase_cost: '',
    purchase_currency: '',
    supplier: '',
    warranty_until: '',
    location: '',
    notes: '',
});

function submit() {
    form.post(route('console.assets.store'));
}
</script>

<template>
    <Head title="Add asset" />

    <ConsoleLayout>
        <template #title>Add asset</template>

        <div class="panel-page">
            <PanelPageHeader title="Add asset">
                <template #actions>
                    <Link :href="route('console.assets.index')" class="panel-link">Back to register</Link>
                </template>
            </PanelPageHeader>

            <AssetForm
                :form="form"
                :categories="categories"
                :statuses="statuses"
                :conditions="conditions"
                :currencies="currencies"
                submit-label="Add asset"
                @submit="submit"
            />
        </div>
    </ConsoleLayout>
</template>
