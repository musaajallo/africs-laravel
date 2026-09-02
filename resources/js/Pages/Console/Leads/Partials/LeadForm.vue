<script setup>
import { useForm } from '@inertiajs/vue3';
import PanelField from '@/Components/Panel/PanelField.vue';
import PanelButton from '@/Components/Panel/PanelButton.vue';

const props = defineProps({
    lead: { type: Object, default: null },
    channels: { type: Object, default: () => ({}) },
    clients: { type: Array, default: () => [] },
});

const isEdit = !!props.lead;

const form = useForm({
    name: props.lead?.name ?? '',
    email: props.lead?.email ?? '',
    company: props.lead?.company ?? '',
    phone: props.lead?.phone ?? '',
    message: props.lead?.message ?? '',
    source: props.lead?.source ?? 'outbound',
    referred_by_client_id: props.lead?.referred_by_client_id ?? '',
    referral_source: props.lead?.referral_source ?? '',
});

function submit() {
    if (isEdit) {
        form.put(route('console.leads.update', props.lead.id));
    } else {
        form.post(route('console.leads.store'));
    }
}
</script>

<template>
    <form class="panel-form" @submit.prevent="submit">
        <fieldset class="panel-form-section" style="border-top: none; margin-top: 0; padding-top: 0">
            <legend>Who</legend>
            <div class="panel-form-grid">
                <PanelField label="Contact name" :error="form.errors.name" required>
                    <input v-model="form.name" type="text" class="field-input" />
                </PanelField>
                <PanelField label="Company" :error="form.errors.company" hint="Leave blank for an individual.">
                    <input v-model="form.company" type="text" class="field-input" />
                </PanelField>
                <PanelField label="Email" :error="form.errors.email" required>
                    <input v-model="form.email" type="email" class="field-input" />
                </PanelField>
                <PanelField label="Phone" :error="form.errors.phone">
                    <input v-model="form.phone" type="text" class="field-input" />
                </PanelField>
            </div>
        </fieldset>

        <fieldset class="panel-form-section">
            <legend>Channel</legend>
            <PanelField label="How did this lead reach us?" :error="form.errors.source" style="max-width: 22rem">
                <select v-model="form.source" class="field-input">
                    <option v-for="(label, key) in channels" :key="key" :value="key">{{ label }}</option>
                </select>
            </PanelField>

            <div v-if="form.source === 'referral'" class="panel-form-grid" style="margin-top: 1rem">
                <PanelField label="Referred by a client" :error="form.errors.referred_by_client_id">
                    <select v-model="form.referred_by_client_id" class="field-input">
                        <option value="">— none —</option>
                        <option v-for="c in clients" :key="c.id" :value="c.id">{{ c.name }}</option>
                    </select>
                </PanelField>
                <PanelField
                    label="…or a partner / other"
                    :error="form.errors.referral_source"
                    hint="A partner company or person's name."
                >
                    <input v-model="form.referral_source" type="text" class="field-input" />
                </PanelField>
            </div>
        </fieldset>

        <fieldset class="panel-form-section">
            <legend>What they want</legend>
            <PanelField :error="form.errors.message">
                <textarea v-model="form.message" rows="4" class="field-input" placeholder="What are they after? Any context."></textarea>
            </PanelField>
        </fieldset>

        <div class="panel-form-actions">
            <PanelButton
                variant="secondary"
                :href="isEdit ? route('console.leads.show', lead.id) : route('console.leads.index')"
            >
                Cancel
            </PanelButton>
            <PanelButton type="submit" :disabled="form.processing">
                {{ isEdit ? 'Save changes' : 'Add lead' }}
            </PanelButton>
        </div>
    </form>
</template>
