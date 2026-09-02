<script setup>
import { computed } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import ConsoleLayout from '@/Layouts/ConsoleLayout.vue';
import PanelPageHeader from '@/Components/Panel/PanelPageHeader.vue';
import PanelButton from '@/Components/Panel/PanelButton.vue';
import PanelConfirm from '@/Components/Panel/PanelConfirm.vue';
import ProjectStatusBadge from '@/Components/Panel/ProjectStatusBadge.vue';
import TagBadge from '@/Components/Panel/TagBadge.vue';
import ActivityFeed from '@/Components/Panel/ActivityFeed.vue';
import { useAuth } from '@/Composables/useAuth.js';

const props = defineProps({
    project: { type: Object, required: true },
    activity: { type: Array, default: () => [] },
});

const { can } = useAuth();
const canManage = computed(() => can('projects.manage'));

const budget = computed(() =>
    props.project.budget_amount
        ? `${props.project.budget_currency} ${Number(props.project.budget_amount).toLocaleString()}`
        : null,
);

const details = computed(() => [
    { label: 'Client', value: props.project.client, href: route('console.clients.show', props.project.client_id) },
    { label: 'Service line', value: props.project.service_line_label },
    { label: 'Project lead', value: props.project.owner },
    { label: 'Budget', value: budget.value },
    { label: 'Start', value: props.project.starts_on },
    { label: 'Target end', value: props.project.ends_on },
    { label: 'Created by', value: props.project.created_by },
    { label: 'Created', value: props.project.created_at },
]);

function archive() {
    router.delete(route('console.projects.destroy', props.project.id));
}
function restore() {
    router.put(route('console.projects.restore', props.project.id));
}
</script>

<template>
    <Head :title="project.name" />

    <ConsoleLayout>
        <template #title>Projects</template>

        <div class="panel-page">
            <PanelPageHeader :title="project.name" :subtitle="project.archived ? 'Archived project' : null">
                <template #actions>
                    <PanelButton variant="secondary" :href="route('console.projects.index')">All projects</PanelButton>
                    <PanelButton v-if="canManage && !project.archived" :href="route('console.projects.edit', project.id)">Edit</PanelButton>
                    <PanelConfirm
                        v-if="canManage && !project.archived"
                        title="Archive this project?"
                        :message="`${project.name} will be hidden from the list. It can be restored.`"
                        confirm-label="Archive"
                        @confirm="archive"
                    />
                    <PanelButton v-if="canManage && project.archived" @click="restore">Restore</PanelButton>
                </template>
            </PanelPageHeader>

            <div style="margin: -0.25rem 0 1.25rem; display: flex; align-items: center; gap: 0.6rem; flex-wrap: wrap">
                <ProjectStatusBadge :status="project.status" />
                <TagBadge v-for="t in project.tags" :key="t.name" :name="t.name" :color="t.color" />
            </div>

            <div class="client-detail-grid">
                <section class="panel-card">
                    <h2 class="panel-card-title">Details</h2>
                    <dl class="client-dl">
                        <template v-for="item in details" :key="item.label">
                            <div v-if="item.value">
                                <dt>{{ item.label }}</dt>
                                <dd>
                                    <Link v-if="item.href" :href="item.href" class="panel-link" style="padding: 0">{{ item.value }}</Link>
                                    <span v-else>{{ item.value }}</span>
                                </dd>
                            </div>
                        </template>
                    </dl>
                    <div v-if="project.description" class="client-address">
                        <dt>Description</dt>
                        <dd style="white-space: pre-line">{{ project.description }}</dd>
                    </div>
                </section>

                <section class="panel-card">
                    <h2 class="panel-card-title">Team</h2>
                    <p v-if="!project.members.length" class="panel-cell-muted">No one assigned yet.</p>
                    <ul v-else class="client-contact-list">
                        <li v-for="m in project.members" :key="m.id">
                            <div class="client-contact-name">{{ m.name }}</div>
                            <div v-if="m.role" class="panel-cell-muted">{{ m.role }}</div>
                        </li>
                    </ul>
                </section>
            </div>

            <div class="client-detail-grid">
                <section class="panel-card">
                    <h2 class="panel-card-title">Activity</h2>
                    <ActivityFeed :items="activity" empty="No changes recorded yet." />
                </section>
                <section class="panel-card client-future">
                    <h2 class="panel-card-title">Proformas &amp; invoices</h2>
                    <p>Billing for this project appears here once the billing module is built.</p>
                </section>
            </div>
        </div>
    </ConsoleLayout>
</template>
