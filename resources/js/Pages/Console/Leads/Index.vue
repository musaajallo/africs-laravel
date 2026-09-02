<script setup>
import { ref, watch } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import ConsoleLayout from '@/Layouts/ConsoleLayout.vue';
import PanelPageHeader from '@/Components/Panel/PanelPageHeader.vue';
import PanelTable from '@/Components/Panel/PanelTable.vue';
import PanelPagination from '@/Components/Panel/PanelPagination.vue';
import LeadStatusBadge from '@/Components/Panel/LeadStatusBadge.vue';

const props = defineProps({
    leads: { type: Object, required: true },
    filters: { type: Object, default: () => ({}) },
    statuses: { type: Array, default: () => [] },
});

const search = ref(props.filters.search ?? '');
const status = ref(props.filters.status ?? '');
let timer = null;

function apply() {
    router.get(
        route('console.leads.index'),
        { search: search.value || undefined, status: status.value || undefined },
        { preserveState: true, preserveScroll: true, replace: true },
    );
}

watch(search, () => {
    clearTimeout(timer);
    timer = setTimeout(apply, 300);
});
watch(status, apply);
</script>

<template>
    <Head title="Leads" />

    <ConsoleLayout>
        <template #title>Leads</template>

        <div class="panel-page">
            <PanelPageHeader
                title="Leads"
                subtitle="Enquiries from the website's contact form, waiting to be worked."
            />

            <div class="panel-toolbar">
                <input
                    v-model="search"
                    type="search"
                    class="field-input panel-toolbar-search"
                    placeholder="Search name, company or email"
                />
                <select v-model="status" class="field-input" style="max-width: 12rem">
                    <option value="">Open leads</option>
                    <option v-for="s in statuses" :key="s" :value="s">{{ s }}</option>
                </select>
            </div>

            <PanelTable
                :columns="['Lead', 'Email', 'Status', 'Owner', 'Received', { label: 'Actions', align: 'right' }]"
                :rows="leads.data"
                empty="No leads match your filter."
            >
                <template #row="{ row }">
                    <td>
                        <Link :href="route('console.leads.show', row.id)" class="panel-link" style="padding: 0">
                            {{ row.company || row.name }}
                        </Link>
                        <div v-if="row.company" class="panel-cell-muted">{{ row.name }}</div>
                    </td>
                    <td>{{ row.email }}</td>
                    <td><LeadStatusBadge :status="row.status" /></td>
                    <td>{{ row.owner || '—' }}</td>
                    <td class="panel-cell-muted">{{ row.received }}</td>
                    <td class="panel-row-actions">
                        <Link :href="route('console.leads.show', row.id)" class="panel-link">View</Link>
                    </td>
                </template>
            </PanelTable>

            <PanelPagination
                :links="leads.links"
                :from="leads.from"
                :to="leads.to"
                :total="leads.total"
            />
        </div>
    </ConsoleLayout>
</template>
