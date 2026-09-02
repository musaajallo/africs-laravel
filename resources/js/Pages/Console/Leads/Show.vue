<script setup>
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import ConsoleLayout from '@/Layouts/ConsoleLayout.vue';
import PanelPageHeader from '@/Components/Panel/PanelPageHeader.vue';
import PanelField from '@/Components/Panel/PanelField.vue';
import PanelButton from '@/Components/Panel/PanelButton.vue';
import PanelConfirm from '@/Components/Panel/PanelConfirm.vue';
import LeadStatusBadge from '@/Components/Panel/LeadStatusBadge.vue';
import ActivityFeed from '@/Components/Panel/ActivityFeed.vue';

const props = defineProps({
    lead: { type: Object, required: true },
    owners: { type: Array, default: () => [] },
    canConvert: { type: Boolean, default: false },
    activity: { type: Array, default: () => [] },
});

const converted = props.lead.is_converted;

const form = useForm({
    status: props.lead.status === 'converted' ? 'qualified' : props.lead.status,
    owner_id: props.lead.owner_id ?? '',
    notes: props.lead.notes ?? '',
});

function save() {
    form.put(route('console.leads.triage', props.lead.id), { preserveScroll: true });
}

function convert() {
    router.post(route('console.leads.convert', props.lead.id));
}

function destroy() {
    router.delete(route('console.leads.destroy', props.lead.id));
}
</script>

<template>
    <Head :title="lead.company || lead.name" />

    <ConsoleLayout>
        <template #title>Leads</template>

        <div class="panel-page">
            <PanelPageHeader :title="lead.company || lead.name" :subtitle="lead.company ? lead.name : null">
                <template #actions>
                    <PanelButton variant="secondary" :href="route('console.leads.index')">All leads</PanelButton>
                    <PanelButton v-if="!converted" variant="secondary" :href="route('console.leads.edit', lead.id)">Edit</PanelButton>
                    <PanelButton v-if="canConvert" @click="convert">Convert to client</PanelButton>
                    <PanelConfirm
                        title="Delete this lead?"
                        message="Use this for spam. The submission will be permanently removed."
                        confirm-label="Delete"
                        @confirm="destroy"
                    />
                </template>
            </PanelPageHeader>

            <div v-if="converted" class="panel-card lead-converted">
                <span class="lead-status" data-status="converted">Converted</span>
                <p>
                    This lead became the client
                    <Link :href="route('console.clients.show', lead.converted_client.id)" class="panel-link" style="padding: 0">
                        {{ lead.converted_client.name }}
                    </Link>.
                </p>
            </div>

            <div class="client-detail-grid">
                <section class="panel-card">
                    <h2 class="panel-card-title">Enquiry</h2>
                    <dl class="client-dl">
                        <div><dt>Status</dt><dd><LeadStatusBadge :status="lead.status" /></dd></div>
                        <div><dt>Email</dt><dd>{{ lead.email }}</dd></div>
                        <div v-if="lead.phone"><dt>Phone</dt><dd>{{ lead.phone }}</dd></div>
                        <div><dt>Channel</dt><dd>{{ lead.channel_label }}</dd></div>
                        <div v-if="lead.referred_by_client">
                            <dt>Referred by</dt>
                            <dd>{{ lead.referred_by_client }} <span class="panel-cell-muted">(client)</span></dd>
                        </div>
                        <div v-else-if="lead.referral_source">
                            <dt>Referred by</dt>
                            <dd>{{ lead.referral_source }}</dd>
                        </div>
                        <div><dt>Received</dt><dd>{{ lead.received_at }}</dd></div>
                    </dl>
                    <div v-if="lead.message" class="client-address">
                        <dt>Message</dt>
                        <dd style="white-space: pre-line">{{ lead.message }}</dd>
                    </div>
                </section>

                <section class="panel-card">
                    <h2 class="panel-card-title">Triage</h2>
                    <form class="panel-form" @submit.prevent="save">
                        <PanelField label="Status" :error="form.errors.status">
                            <select v-model="form.status" class="field-input" :disabled="converted">
                                <option value="new">New</option>
                                <option value="contacted">Contacted</option>
                                <option value="qualified">Qualified</option>
                                <option value="lost">Lost</option>
                            </select>
                        </PanelField>
                        <PanelField label="Owner" :error="form.errors.owner_id" style="margin-top: 1rem">
                            <select v-model="form.owner_id" class="field-input" :disabled="converted">
                                <option value="">Unassigned</option>
                                <option v-for="o in owners" :key="o.id" :value="o.id">{{ o.name }}</option>
                            </select>
                        </PanelField>
                        <PanelField label="Notes" :error="form.errors.notes" style="margin-top: 1rem">
                            <textarea v-model="form.notes" rows="4" class="field-input" :disabled="converted"></textarea>
                        </PanelField>
                        <div v-if="!converted" class="panel-form-actions">
                            <PanelButton type="submit" :disabled="form.processing">Save</PanelButton>
                        </div>
                    </form>
                </section>
            </div>

            <section class="panel-card" style="margin-top: 1rem">
                <h2 class="panel-card-title">Activity</h2>
                <ActivityFeed :items="activity" empty="No activity yet." />
            </section>
        </div>
    </ConsoleLayout>
</template>
