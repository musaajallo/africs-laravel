<script setup>
import { ref, watch } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import ConsoleLayout from '@/Layouts/ConsoleLayout.vue';
import PanelPageHeader from '@/Components/Panel/PanelPageHeader.vue';
import PanelTable from '@/Components/Panel/PanelTable.vue';
import PanelPagination from '@/Components/Panel/PanelPagination.vue';
import PanelButton from '@/Components/Panel/PanelButton.vue';
import PanelConfirm from '@/Components/Panel/PanelConfirm.vue';
import AssetStatusBadge from '@/Components/Panel/AssetStatusBadge.vue';
import { useAuth } from '@/Composables/useAuth.js';

const props = defineProps({
    assets: { type: Object, required: true },
    filters: { type: Object, default: () => ({}) },
    categories: { type: Object, default: () => ({}) },
    statuses: { type: Object, default: () => ({}) },
    people: { type: Array, default: () => [] },
});

const { can } = useAuth();
const search = ref(props.filters.search ?? '');
const status = ref(props.filters.status ?? '');
const category = ref(props.filters.category ?? '');
const assignee = ref(props.filters.assignee ?? '');
let timer = null;

function apply() {
    router.get(
        route('console.assets.index'),
        {
            search: search.value || undefined,
            status: status.value || undefined,
            category: category.value || undefined,
            assignee: assignee.value || undefined,
        },
        { preserveState: true, preserveScroll: true, replace: true },
    );
}

watch(search, () => {
    clearTimeout(timer);
    timer = setTimeout(apply, 300);
});
watch([status, category, assignee], apply);

function remove(asset) {
    router.delete(route('console.assets.destroy', asset.id), { preserveScroll: true });
}
</script>

<template>
    <Head title="Assets" />

    <ConsoleLayout>
        <template #title>Assets</template>

        <div class="panel-page">
            <PanelPageHeader
                title="Asset register"
                subtitle="Equipment Africs owns — what it is, who has it, and its condition."
            >
                <template #actions>
                    <PanelButton v-if="can('assets.manage')" :href="route('console.assets.create')">
                        Add asset
                    </PanelButton>
                </template>
            </PanelPageHeader>

            <div class="panel-toolbar">
                <input
                    v-model="search"
                    type="search"
                    class="field-input panel-toolbar-search"
                    placeholder="Name, serial, tag…"
                />
                <select v-model="category" class="field-input" style="max-width: 11rem">
                    <option value="">All categories</option>
                    <option v-for="(label, key) in categories" :key="key" :value="key">{{ label }}</option>
                </select>
                <select v-model="status" class="field-input" style="max-width: 10rem">
                    <option value="">Active</option>
                    <option v-for="(label, key) in statuses" :key="key" :value="key">{{ label }}</option>
                </select>
                <select v-model="assignee" class="field-input" style="max-width: 12rem">
                    <option value="">Anyone</option>
                    <option v-for="p in people" :key="p.id" :value="p.id">{{ p.name }}</option>
                </select>
            </div>

            <PanelTable
                :columns="['Asset', 'Category', 'Serial / tag', 'Status', 'Assigned to', { label: 'Book value', align: 'right' }, { label: 'Actions', align: 'right' }]"
                :rows="assets.data"
                empty="No assets match your filter."
            >
                <template #row="{ row }">
                    <td>
                        <Link :href="route('console.assets.show', row.id)" class="panel-link" style="padding: 0">
                            {{ row.name }}
                        </Link>
                        <div v-if="row.make_model" class="panel-cell-muted" style="font-size: 0.8rem">{{ row.make_model }}</div>
                    </td>
                    <td>{{ categories[row.category] || row.category }}</td>
                    <td class="panel-cell-muted">
                        {{ row.serial_number || '—' }}<span v-if="row.asset_tag"> · {{ row.asset_tag }}</span>
                    </td>
                    <td><AssetStatusBadge :status="row.status" /></td>
                    <td>{{ row.assignee || '—' }}</td>
                    <td class="num" style="text-align: right">
                        <template v-if="row.book_value != null">
                            {{ row.book_currency }} {{ Number(row.book_value).toLocaleString() }}
                        </template>
                        <span v-else class="panel-cell-muted">—</span>
                    </td>
                    <td class="panel-row-actions">
                        <Link :href="route('console.assets.show', row.id)" class="panel-link">View</Link>
                        <Link v-if="can('assets.manage')" :href="route('console.assets.edit', row.id)" class="panel-link">Edit</Link>
                        <PanelConfirm
                            v-if="can('assets.manage')"
                            title="Remove this asset?"
                            :message="`${row.name} will be hidden from the register. It can be restored.`"
                            confirm-label="Remove"
                            @confirm="remove(row)"
                        >
                            <template #trigger>
                                <button type="button" class="panel-link is-danger">Remove</button>
                            </template>
                        </PanelConfirm>
                    </td>
                </template>
            </PanelTable>

            <PanelPagination
                :links="assets.links"
                :from="assets.from"
                :to="assets.to"
                :total="assets.total"
            />
        </div>
    </ConsoleLayout>
</template>
