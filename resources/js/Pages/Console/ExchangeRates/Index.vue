<script setup>
import { Head, router, useForm } from '@inertiajs/vue3';
import ConsoleLayout from '@/Layouts/ConsoleLayout.vue';
import PanelPageHeader from '@/Components/Panel/PanelPageHeader.vue';
import PanelButton from '@/Components/Panel/PanelButton.vue';
import PanelField from '@/Components/Panel/PanelField.vue';
import PanelTable from '@/Components/Panel/PanelTable.vue';

const props = defineProps({
    base: { type: String, required: true },
    rates: { type: Array, default: () => [] },
    canManage: { type: Boolean, default: false },
});

const today = new Date().toISOString().slice(0, 10);

const forms = Object.fromEntries(
    props.rates.map((r) => [
        r.currency,
        useForm({ currency: r.currency, rate: '', rate_date: today }),
    ]),
);

function save(currency) {
    forms[currency].post(route('console.exchange-rates.store'), {
        preserveScroll: true,
        onSuccess: () => forms[currency].reset('rate'),
    });
}

function refresh() {
    router.post(route('console.exchange-rates.refresh'), {}, { preserveScroll: true });
}
</script>

<template>
    <Head title="Exchange rates" />

    <ConsoleLayout>
        <template #title>Exchange rates</template>

        <div class="panel-page">
            <PanelPageHeader
                title="Exchange rates"
                :subtitle="`Value of one unit of each currency in ${base}. Documents snapshot the rate they use, so past totals never move.`"
            >
                <template v-if="canManage" #actions>
                    <PanelButton variant="secondary" @click="refresh">Fetch latest</PanelButton>
                </template>
            </PanelPageHeader>

            <p v-if="!rates.length" class="panel-card panel-page-lead">
                Only the base currency ({{ base }}) is enabled. Enable more currencies in Settings to track rates.
            </p>

            <section v-for="row in rates" :key="row.currency" class="panel-card">
                <h2 class="panel-card-title">1 {{ row.currency }} &rarr; {{ base }}</h2>

                <form
                    v-if="canManage"
                    class="panel-form-grid"
                    style="margin-bottom: 1rem"
                    @submit.prevent="save(row.currency)"
                >
                    <PanelField label="Rate" :error="forms[row.currency].errors.rate" required>
                        <input
                            v-model="forms[row.currency].rate"
                            type="number"
                            step="0.0001"
                            min="0"
                            class="field-input"
                            placeholder="e.g. 72.5000"
                        />
                    </PanelField>
                    <PanelField label="As of" :error="forms[row.currency].errors.rate_date" required>
                        <input v-model="forms[row.currency].rate_date" type="date" :max="today" class="field-input" />
                    </PanelField>
                    <div style="display: flex; align-items: flex-end">
                        <PanelButton type="submit" :disabled="forms[row.currency].processing">Save rate</PanelButton>
                    </div>
                </form>

                <PanelTable
                    :columns="['Date', { label: `Rate (${base})`, align: 'right' }, 'Source']"
                    :rows="row.history"
                    empty="No rates recorded yet."
                >
                    <template #row="{ row: entry }">
                        <td>{{ entry.rate_date }}</td>
                        <td style="text-align: right">{{ entry.rate }}</td>
                        <td class="panel-cell-muted">{{ entry.source }}</td>
                    </template>
                </PanelTable>
            </section>
        </div>
    </ConsoleLayout>
</template>
