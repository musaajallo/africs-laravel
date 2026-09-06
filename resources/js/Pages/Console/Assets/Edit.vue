<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import ConsoleLayout from '@/Layouts/ConsoleLayout.vue';
import PanelPageHeader from '@/Components/Panel/PanelPageHeader.vue';
import AssetForm from './Partials/AssetForm.vue';

const props = defineProps({
    asset: { type: Object, required: true },
    categories: { type: Object, default: () => ({}) },
    statuses: { type: Object, default: () => ({}) },
    conditions: { type: Object, default: () => ({}) },
    currencies: { type: Array, default: () => [] },
});

const form = useForm({
    name: props.asset.name,
    category: props.asset.category,
    make: props.asset.make ?? '',
    model: props.asset.model ?? '',
    serial_number: props.asset.serial_number ?? '',
    asset_tag: props.asset.asset_tag ?? '',
    status: props.asset.status,
    condition: props.asset.condition ?? '',
    purchased_on: props.asset.purchased_on ?? '',
    purchase_cost: props.asset.purchase_cost ?? '',
    purchase_currency: props.asset.purchase_currency ?? '',
    supplier: props.asset.supplier ?? '',
    warranty_until: props.asset.warranty_until ?? '',
    location: props.asset.location ?? '',
    notes: props.asset.notes ?? '',
});

function submit() {
    form.put(route('console.assets.update', props.asset.id));
}
</script>

<template>
    <Head :title="`Edit ${asset.name}`" />

    <ConsoleLayout>
        <template #title>Edit asset</template>

        <div class="panel-page">
            <PanelPageHeader :title="`Edit ${asset.name}`">
                <template #actions>
                    <Link :href="route('console.assets.show', asset.id)" class="panel-link">Cancel</Link>
                </template>
            </PanelPageHeader>

            <AssetForm
                :form="form"
                :categories="categories"
                :statuses="statuses"
                :conditions="conditions"
                :currencies="currencies"
                submit-label="Save changes"
                @submit="submit"
            />
        </div>
    </ConsoleLayout>
</template>
