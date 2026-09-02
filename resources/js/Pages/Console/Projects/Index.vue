<script setup>
import { ref, watch } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import ConsoleLayout from '@/Layouts/ConsoleLayout.vue';
import PanelPageHeader from '@/Components/Panel/PanelPageHeader.vue';
import PanelTable from '@/Components/Panel/PanelTable.vue';
import PanelPagination from '@/Components/Panel/PanelPagination.vue';
import PanelButton from '@/Components/Panel/PanelButton.vue';
import PanelConfirm from '@/Components/Panel/PanelConfirm.vue';
import ProjectStatusBadge from '@/Components/Panel/ProjectStatusBadge.vue';
import { useAuth } from '@/Composables/useAuth.js';

const props = defineProps({
    projects: { type: Object, required: true },
    filters: { type: Object, default: () => ({}) },
    serviceLines: { type: Object, default: () => ({}) },
    statuses: { type: Object, default: () => ({}) },
    clients: { type: Array, default: () => [] },
});

const { can } = useAuth();
const search = ref(props.filters.search ?? '');
const status = ref(props.filters.status ?? '');
const serviceLine = ref(props.filters.service_line ?? '');
const client = ref(props.filters.client ?? '');
let timer = null;

function apply() {
    router.get(
        route('console.projects.index'),
        {
            search: search.value || undefined,
            status: status.value || undefined,
            service_line: serviceLine.value || undefined,
            client: client.value || undefined,
        },
        { preserveState: true, preserveScroll: true, replace: true },
    );
}

watch(search, () => {
    clearTimeout(timer);
    timer = setTimeout(apply, 300);
});
watch([status, serviceLine, client], apply);

function archive(project) {
    router.delete(route('console.projects.destroy', project.id), { preserveScroll: true });
}
</script>

<template>
    <Head title="Projects" />

    <ConsoleLayout>
        <template #title>Projects</template>

        <div class="panel-page">
            <PanelPageHeader
                title="Projects"
                subtitle="Client engagements across Business, Technology and Design."
            >
                <template #actions>
                    <PanelButton v-if="can('projects.manage')" :href="route('console.projects.create')">
                        New project
                    </PanelButton>
                </template>
            </PanelPageHeader>

            <div class="panel-toolbar">
                <input
                    v-model="search"
                    type="search"
                    class="field-input panel-toolbar-search"
                    placeholder="Search projects"
                />
                <select v-model="serviceLine" class="field-input" style="max-width: 10rem">
                    <option value="">All lines</option>
                    <option v-for="(label, key) in serviceLines" :key="key" :value="key">{{ label }}</option>
                </select>
                <select v-model="status" class="field-input" style="max-width: 10rem">
                    <option value="">Open projects</option>
                    <option v-for="(label, key) in statuses" :key="key" :value="key">{{ label }}</option>
                </select>
                <select v-model="client" class="field-input" style="max-width: 12rem">
                    <option value="">Any client</option>
                    <option v-for="c in clients" :key="c.id" :value="c.id">{{ c.name }}</option>
                </select>
            </div>

            <PanelTable
                :columns="['Project', 'Client', 'Line', 'Status', 'Target end', { label: 'Actions', align: 'right' }]"
                :rows="projects.data"
                empty="No projects match your filter."
            >
                <template #row="{ row }">
                    <td>
                        <Link :href="route('console.projects.show', row.id)" class="panel-link" style="padding: 0">
                            {{ row.name }}
                        </Link>
                    </td>
                    <td>
                        <Link :href="route('console.clients.show', row.client_id)" class="panel-link" style="padding: 0">
                            {{ row.client }}
                        </Link>
                    </td>
                    <td><span class="proj-line">{{ serviceLines[row.service_line] }}</span></td>
                    <td><ProjectStatusBadge :status="row.status" /></td>
                    <td class="panel-cell-muted">{{ row.ends_on || '—' }}</td>
                    <td class="panel-row-actions">
                        <Link :href="route('console.projects.show', row.id)" class="panel-link">View</Link>
                        <Link v-if="can('projects.manage')" :href="route('console.projects.edit', row.id)" class="panel-link">Edit</Link>
                        <PanelConfirm
                            v-if="can('projects.manage')"
                            title="Archive this project?"
                            :message="`${row.name} will be hidden from the list. It can be restored.`"
                            confirm-label="Archive"
                            @confirm="archive(row)"
                        >
                            <template #trigger>
                                <button type="button" class="panel-link is-danger">Archive</button>
                            </template>
                        </PanelConfirm>
                    </td>
                </template>
            </PanelTable>

            <PanelPagination
                :links="projects.links"
                :from="projects.from"
                :to="projects.to"
                :total="projects.total"
            />
        </div>
    </ConsoleLayout>
</template>
