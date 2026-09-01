<script setup>
import { ref, watch } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import ConsoleLayout from '@/Layouts/ConsoleLayout.vue';
import PanelPageHeader from '@/Components/Panel/PanelPageHeader.vue';
import PanelTable from '@/Components/Panel/PanelTable.vue';
import PanelPagination from '@/Components/Panel/PanelPagination.vue';
import PanelButton from '@/Components/Panel/PanelButton.vue';
import PanelConfirm from '@/Components/Panel/PanelConfirm.vue';
import { useAuth } from '@/Composables/useAuth.js';

const props = defineProps({
    users: { type: Object, required: true },
    filters: { type: Object, default: () => ({}) },
});

const { can } = useAuth();
const search = ref(props.filters.search ?? '');
let timer = null;

watch(search, (value) => {
    clearTimeout(timer);
    timer = setTimeout(() => {
        router.get(
            route('console.users.index'),
            { search: value || undefined },
            { preserveState: true, preserveScroll: true, replace: true },
        );
    }, 300);
});

function toggleActive(user) {
    router.delete(route('console.users.destroy', user.id), { preserveScroll: true });
}
</script>

<template>
    <Head title="Users & access" />

    <ConsoleLayout>
        <template #title>Users &amp; access</template>

        <div class="panel-page">
            <PanelPageHeader
                title="Users &amp; access"
                subtitle="Internal accounts and the roles that grant them access."
            >
                <template #actions>
                    <PanelButton v-if="can('users.manage')" :href="route('console.users.create')">
                        New user
                    </PanelButton>
                </template>
            </PanelPageHeader>

            <div class="panel-toolbar">
                <input
                    v-model="search"
                    type="search"
                    class="field-input panel-search"
                    placeholder="Search name, username or email"
                />
            </div>

            <PanelTable
                :columns="['Name', 'Email', 'Roles', 'Status', { label: 'Actions', align: 'right' }]"
                :rows="users.data"
                empty="No users match your search."
            >
                <template #row="{ row }">
                    <td>
                        <div class="panel-cell-strong">{{ row.name }}</div>
                        <div class="panel-cell-muted">@{{ row.username }}</div>
                    </td>
                    <td>{{ row.email }}</td>
                    <td>
                        <span v-if="!row.roles.length" class="panel-cell-muted">—</span>
                        <span v-for="role in row.roles" :key="role" class="panel-badge">
                            {{ role }}
                        </span>
                    </td>
                    <td>
                        <span
                            class="panel-status"
                            :class="row.deactivated ? 'is-off' : 'is-on'"
                        >
                            {{ row.deactivated ? 'Deactivated' : 'Active' }}
                        </span>
                    </td>
                    <td class="panel-row-actions">
                        <Link
                            v-if="can('users.manage')"
                            :href="route('console.users.edit', row.id)"
                            class="panel-link"
                        >
                            Edit
                        </Link>
                        <PanelConfirm
                            v-if="can('users.manage') && !row.is_self"
                            :title="row.deactivated ? 'Reactivate user?' : 'Deactivate user?'"
                            :message="row.deactivated
                                ? `${row.name} will be able to sign in again.`
                                : `${row.name} will be signed out and blocked from signing in.`"
                            :confirm-label="row.deactivated ? 'Reactivate' : 'Deactivate'"
                            :confirm-variant="row.deactivated ? 'primary' : 'danger'"
                            @confirm="toggleActive(row)"
                        >
                            <template #trigger>
                                <button type="button" class="panel-link is-danger">
                                    {{ row.deactivated ? 'Reactivate' : 'Deactivate' }}
                                </button>
                            </template>
                        </PanelConfirm>
                    </td>
                </template>
            </PanelTable>

            <PanelPagination
                :links="users.links"
                :from="users.from"
                :to="users.to"
                :total="users.total"
            />
        </div>
    </ConsoleLayout>
</template>
