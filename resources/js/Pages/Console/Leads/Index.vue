<script setup>
import { ref, watch } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import ConsoleLayout from '@/Layouts/ConsoleLayout.vue';
import PanelPageHeader from '@/Components/Panel/PanelPageHeader.vue';
import PanelTable from '@/Components/Panel/PanelTable.vue';
import PanelPagination from '@/Components/Panel/PanelPagination.vue';
import PanelButton from '@/Components/Panel/PanelButton.vue';
import LeadStatusBadge from '@/Components/Panel/LeadStatusBadge.vue';
import { useAuth } from '@/Composables/useAuth.js';

const props = defineProps({
    leads: { type: Object, required: true },
    filters: { type: Object, default: () => ({}) },
    statuses: { type: Array, default: () => [] },
});

const { can } = useAuth();
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
                subtitle="Enquiries waiting to be worked — from the website, referrals, events and outreach."
            >
                <template #actions>
                    <PanelButton v-if="can('leads.manage')" :href="route('console.leads.create')">
                        New lead
                    </PanelButton>
                </template>
            </PanelPageHeader>

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
                :columns="['Lead', 'Channel', 'Status', 'Owner', 'Received', { label: 'Actions', align: 'right' }]"
                :rows="leads.data"
                empty="No leads match your filter."
            >
                <template #row="{ row }">
                    <td>
                        <Link :href="route('console.leads.show', row.id)" class="panel-link" style="padding: 0">
                            {{ row.company || row.name }}
                        </Link>
                        <div class="panel-cell-muted">{{ row.company ? row.name : row.email }}</div>
                    </td>
                    <td class="panel-cell-muted">{{ row.channel }}</td>
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
