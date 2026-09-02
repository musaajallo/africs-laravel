<script setup>
import { computed, watch } from 'vue';
import { useForm } from '@inertiajs/vue3';
import PanelField from '@/Components/Panel/PanelField.vue';
import PanelButton from '@/Components/Panel/PanelButton.vue';

const props = defineProps({
    client: { type: Object, default: null },
    currencies: { type: Array, default: () => [] },
    categories: { type: Object, default: () => ({}) },
    owners: { type: Array, default: () => [] },
});

const isEdit = !!props.client;

const typeLabels = {
    individual: 'Individual',
    organisation: 'Organisation',
    government: 'Government',
};

function blankContact() {
    return { id: null, name: '', title: '', email: '', phone: '', is_primary: false, notes: '' };
}

const form = useForm({
    name: props.client?.name ?? '',
    type: props.client?.type ?? 'organisation',
    category: props.client?.category ?? '',
    status: props.client?.status ?? 'active',
    email: props.client?.email ?? '',
    phone: props.client?.phone ?? '',
    website: props.client?.website ?? '',
    tax_number: props.client?.tax_number ?? '',
    currency: props.client?.currency ?? '',
    billing_address: props.client?.billing_address ?? '',
    city: props.client?.city ?? '',
    country: props.client?.country ?? '',
    owner_id: props.client?.owner_id ?? '',
    notes: props.client?.notes ?? '',
    contacts: props.client?.contacts?.length
        ? props.client.contacts.map((c) => ({ ...c }))
        : [blankContact()],
});

const categoryOptions = computed(() => props.categories[form.type] ?? []);

watch(
    () => form.type,
    () => {
        if (!categoryOptions.value.includes(form.category)) form.category = '';
    },
);

function addContact() {
    form.contacts.push(blankContact());
}

function removeContact(index) {
    form.contacts.splice(index, 1);
    if (!form.contacts.length) form.contacts.push(blankContact());
}

function setPrimary(index) {
    form.contacts.forEach((c, i) => (c.is_primary = i === index));
}

function contactError(index, field) {
    return form.errors[`contacts.${index}.${field}`];
}

function submit() {
    const payload = {
        onSuccess: () => {},
    };
    if (isEdit) {
        form.put(route('console.clients.update', props.client.id), payload);
    } else {
        form.post(route('console.clients.store'), payload);
    }
}
</script>

<template>
    <form class="panel-form" @submit.prevent="submit">
        <fieldset class="panel-form-section" style="border-top: none; margin-top: 0; padding-top: 0">
            <legend>Details</legend>
            <div class="panel-form-grid">
                <PanelField label="Name" :error="form.errors.name" required>
                    <input v-model="form.name" type="text" class="field-input" />
                </PanelField>

                <PanelField label="Type" :error="form.errors.type">
                    <select v-model="form.type" class="field-input">
                        <option v-for="(l, key) in typeLabels" :key="key" :value="key">{{ l }}</option>
                    </select>
                </PanelField>

                <PanelField
                    v-if="categoryOptions.length"
                    label="Category"
                    :error="form.errors.category"
                >
                    <select v-model="form.category" class="field-input">
                        <option value="">Unspecified</option>
                        <option v-for="c in categoryOptions" :key="c" :value="c">{{ c }}</option>
                    </select>
                </PanelField>

                <PanelField label="Status" :error="form.errors.status">
                    <select v-model="form.status" class="field-input">
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                </PanelField>

                <PanelField label="Account manager" :error="form.errors.owner_id">
                    <select v-model="form.owner_id" class="field-input">
                        <option value="">Unassigned</option>
                        <option v-for="o in owners" :key="o.id" :value="o.id">{{ o.name }}</option>
                    </select>
                </PanelField>
            </div>
        </fieldset>

        <fieldset class="panel-form-section">
            <legend>Contact &amp; billing</legend>
            <div class="panel-form-grid">
                <PanelField label="Email" :error="form.errors.email">
                    <input v-model="form.email" type="email" class="field-input" />
                </PanelField>
                <PanelField label="Phone" :error="form.errors.phone">
                    <input v-model="form.phone" type="text" class="field-input" />
                </PanelField>
                <PanelField label="Website" :error="form.errors.website">
                    <input v-model="form.website" type="url" class="field-input" placeholder="https://" />
                </PanelField>
                <PanelField label="Tax / TIN number" :error="form.errors.tax_number">
                    <input v-model="form.tax_number" type="text" class="field-input" />
                </PanelField>
                <PanelField label="Default currency" :error="form.errors.currency" hint="Used on this client's proformas and invoices.">
                    <select v-model="form.currency" class="field-input">
                        <option value="">Base currency</option>
                        <option v-for="c in currencies" :key="c" :value="c">{{ c }}</option>
                    </select>
                </PanelField>
                <PanelField label="City" :error="form.errors.city">
                    <input v-model="form.city" type="text" class="field-input" />
                </PanelField>
                <PanelField label="Country" :error="form.errors.country" hint="Two-letter code, e.g. GM.">
                    <input v-model="form.country" type="text" maxlength="2" class="field-input" style="text-transform: uppercase" />
                </PanelField>
            </div>
            <PanelField label="Billing address" :error="form.errors.billing_address">
                <textarea v-model="form.billing_address" rows="3" class="field-input"></textarea>
            </PanelField>
        </fieldset>

        <fieldset class="panel-form-section">
            <legend>People</legend>
            <div class="client-contacts">
                <div v-for="(contact, i) in form.contacts" :key="i" class="client-contact-row">
                    <div class="panel-form-grid">
                        <PanelField label="Name" :error="contactError(i, 'name')">
                            <input v-model="contact.name" type="text" class="field-input" />
                        </PanelField>
                        <PanelField label="Job title" :error="contactError(i, 'title')">
                            <input v-model="contact.title" type="text" class="field-input" />
                        </PanelField>
                        <PanelField label="Email" :error="contactError(i, 'email')">
                            <input v-model="contact.email" type="email" class="field-input" />
                        </PanelField>
                        <PanelField label="Phone" :error="contactError(i, 'phone')">
                            <input v-model="contact.phone" type="text" class="field-input" />
                        </PanelField>
                    </div>
                    <div class="client-contact-actions">
                        <label class="panel-checkbox">
                            <input type="checkbox" :checked="contact.is_primary" @change="setPrimary(i)" />
                            <span>Primary contact</span>
                        </label>
                        <button type="button" class="panel-link is-danger" @click="removeContact(i)">
                            Remove
                        </button>
                    </div>
                </div>
            </div>
            <button type="button" class="panel-link" @click="addContact">+ Add another contact</button>
        </fieldset>

        <fieldset class="panel-form-section">
            <legend>Notes</legend>
            <PanelField :error="form.errors.notes">
                <textarea v-model="form.notes" rows="4" class="field-input" placeholder="Internal notes about this client."></textarea>
            </PanelField>
        </fieldset>

        <div class="panel-form-actions">
            <PanelButton
                variant="secondary"
                :href="isEdit ? route('console.clients.show', client.id) : route('console.clients.index')"
            >
                Cancel
            </PanelButton>
            <PanelButton type="submit" :disabled="form.processing">
                {{ isEdit ? 'Save changes' : 'Create client' }}
            </PanelButton>
        </div>
    </form>
</template>
