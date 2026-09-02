<script setup>
import { computed, ref } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import ConsoleLayout from '@/Layouts/ConsoleLayout.vue';
import PanelPageHeader from '@/Components/Panel/PanelPageHeader.vue';
import PanelButton from '@/Components/Panel/PanelButton.vue';
import PanelConfirm from '@/Components/Panel/PanelConfirm.vue';
import ContactDialog from './Partials/ContactDialog.vue';
import { useAuth } from '@/Composables/useAuth.js';

const props = defineProps({
    client: { type: Object, required: true },
});

const { can } = useAuth();
const canManage = computed(() => can('clients.manage'));

const dialogOpen = ref(false);
const editingContact = ref(null);

function addContact() {
    editingContact.value = null;
    dialogOpen.value = true;
}

function editContact(contact) {
    editingContact.value = contact;
    dialogOpen.value = true;
}

function deleteContact(contact) {
    router.delete(route('console.clients.contacts.destroy', [props.client.id, contact.id]), {
        preserveScroll: true,
    });
}

function archive() {
    router.delete(route('console.clients.destroy', props.client.id));
}

function restore() {
    router.put(route('console.clients.restore', props.client.id));
}

const typeLabels = {
    individual: 'Individual',
    organisation: 'Organisation',
    government: 'Government',
};

const details = computed(() => [
    {
        label: 'Type',
        value: [typeLabels[props.client.type], props.client.category].filter(Boolean).join(' · '),
    },
    { label: 'Status', value: props.client.status === 'active' ? 'Active' : 'Inactive' },
    { label: 'Email', value: props.client.email },
    { label: 'Phone', value: props.client.phone },
    { label: 'Website', value: props.client.website },
    { label: 'Default currency', value: props.client.currency || 'Base currency' },
    { label: 'Tax / TIN', value: props.client.tax_number },
    { label: 'Location', value: [props.client.city, props.client.country].filter(Boolean).join(', ') },
    { label: 'Account manager', value: props.client.owner },
    { label: 'Added by', value: props.client.created_by },
    { label: 'Added on', value: props.client.created_at },
]);
</script>

<template>
    <Head :title="client.name" />

    <ConsoleLayout>
        <template #title>Clients</template>

        <div class="panel-page">
            <PanelPageHeader :title="client.name" :subtitle="client.archived ? 'Archived client' : null">
                <template #actions>
                    <PanelButton variant="secondary" :href="route('console.clients.index')">
                        All clients
                    </PanelButton>
                    <PanelButton
                        v-if="canManage && !client.archived"
                        :href="route('console.clients.edit', client.id)"
                    >
                        Edit
                    </PanelButton>
                    <PanelConfirm
                        v-if="canManage && !client.archived"
                        title="Archive this client?"
                        :message="`${client.name} will be hidden from the client list. History is kept and it can be restored.`"
                        confirm-label="Archive"
                        @confirm="archive"
                    />
                    <PanelButton v-if="canManage && client.archived" @click="restore">
                        Restore
                    </PanelButton>
                </template>
            </PanelPageHeader>

            <div class="client-detail-grid">
                <section class="panel-card">
                    <h2 class="panel-card-title">Details</h2>
                    <dl class="client-dl">
                        <template v-for="item in details" :key="item.label">
                            <div v-if="item.value">
                                <dt>{{ item.label }}</dt>
                                <dd>{{ item.value }}</dd>
                            </div>
                        </template>
                    </dl>
                    <div v-if="client.billing_address" class="client-address">
                        <dt>Billing address</dt>
                        <dd style="white-space: pre-line">{{ client.billing_address }}</dd>
                    </div>
                    <div v-if="client.notes" class="client-address">
                        <dt>Notes</dt>
                        <dd style="white-space: pre-line">{{ client.notes }}</dd>
                    </div>
                </section>

                <section class="panel-card">
                    <div class="client-card-head">
                        <h2 class="panel-card-title">People</h2>
                        <button v-if="canManage" type="button" class="panel-link" @click="addContact">
                            + Add contact
                        </button>
                    </div>

                    <p v-if="!client.contacts.length" class="panel-cell-muted">No contacts recorded.</p>
                    <ul v-else class="client-contact-list">
                        <li v-for="contact in client.contacts" :key="contact.id">
                            <div class="client-contact-name">
                                {{ contact.name }}
                                <span v-if="contact.is_primary" class="panel-badge">Primary</span>
                            </div>
                            <div v-if="contact.title" class="panel-cell-muted">{{ contact.title }}</div>
                            <div class="client-contact-meta">
                                <a v-if="contact.email" :href="`mailto:${contact.email}`" class="panel-link" style="padding: 0">{{ contact.email }}</a>
                                <span v-if="contact.phone">{{ contact.phone }}</span>
                            </div>
                            <div v-if="canManage" class="client-contact-row-actions">
                                <button type="button" class="panel-link" @click="editContact(contact)">Edit</button>
                                <PanelConfirm
                                    :title="`Remove ${contact.name}?`"
                                    message="This contact will be deleted from the client."
                                    confirm-label="Remove"
                                    @confirm="deleteContact(contact)"
                                >
                                    <template #trigger>
                                        <button type="button" class="panel-link is-danger">Delete</button>
                                    </template>
                                </PanelConfirm>
                            </div>
                        </li>
                    </ul>
                </section>
            </div>

            <section class="panel-card client-future">
                <h2 class="panel-card-title">Projects, proformas &amp; invoices</h2>
                <p>These appear here as the billing and projects modules come online.</p>
            </section>
        </div>

        <ContactDialog
            :client-id="client.id"
            :contact="editingContact"
            :show="dialogOpen"
            @close="dialogOpen = false"
        />
    </ConsoleLayout>
</template>
