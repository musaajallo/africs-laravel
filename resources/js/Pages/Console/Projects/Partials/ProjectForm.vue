<script setup>
import { useForm } from '@inertiajs/vue3';
import PanelField from '@/Components/Panel/PanelField.vue';
import PanelButton from '@/Components/Panel/PanelButton.vue';
import TagChipsInput from '@/Components/Panel/TagChipsInput.vue';

const props = defineProps({
    project: { type: Object, default: null },
    clients: { type: Array, default: () => [] },
    users: { type: Array, default: () => [] },
    serviceLines: { type: Object, default: () => ({}) },
    statuses: { type: Object, default: () => ({}) },
    currencies: { type: Array, default: () => [] },
    baseCurrency: { type: String, default: 'GMD' },
    allTags: { type: Array, default: () => [] },
    presetClientId: { type: Number, default: null },
});

const isEdit = !!props.project;

function blankMember() {
    return { user_id: '', role: '' };
}

const form = useForm({
    name: props.project?.name ?? '',
    client_id: props.project?.client_id ?? props.presetClientId ?? '',
    service_line: props.project?.service_line ?? 'business',
    status: props.project?.status ?? 'proposed',
    description: props.project?.description ?? '',
    starts_on: props.project?.starts_on ?? '',
    ends_on: props.project?.ends_on ?? '',
    budget_amount: props.project?.budget_amount ?? '',
    budget_currency: props.project?.budget_currency ?? props.baseCurrency,
    owner_id: props.project?.owner_id ?? '',
    members: props.project?.members?.length
        ? props.project.members.map((m) => ({ user_id: m.id, role: m.role ?? '' }))
        : [],
    tags: props.project?.tags?.map((t) => t.name) ?? [],
});

function addMember() {
    form.members.push(blankMember());
}

function removeMember(i) {
    form.members.splice(i, 1);
}

function memberError(i, field) {
    return form.errors[`members.${i}.${field}`];
}

function submit() {
    if (isEdit) {
        form.put(route('console.projects.update', props.project.id));
    } else {
        form.post(route('console.projects.store'));
    }
}
</script>

<template>
    <form class="panel-form" @submit.prevent="submit">
        <fieldset class="panel-form-section" style="border-top: none; margin-top: 0; padding-top: 0">
            <legend>Basics</legend>
            <div class="panel-form-grid">
                <PanelField label="Project name" :error="form.errors.name" required>
                    <input v-model="form.name" type="text" class="field-input" />
                </PanelField>
                <PanelField label="Client" :error="form.errors.client_id" required>
                    <select v-model="form.client_id" class="field-input">
                        <option value="" disabled>Choose a client…</option>
                        <option v-for="c in clients" :key="c.id" :value="c.id">{{ c.name }}</option>
                    </select>
                </PanelField>
                <PanelField label="Service line" :error="form.errors.service_line" required>
                    <select v-model="form.service_line" class="field-input">
                        <option v-for="(label, key) in serviceLines" :key="key" :value="key">{{ label }}</option>
                    </select>
                </PanelField>
                <PanelField label="Status" :error="form.errors.status" required>
                    <select v-model="form.status" class="field-input">
                        <option v-for="(label, key) in statuses" :key="key" :value="key">{{ label }}</option>
                    </select>
                </PanelField>
                <PanelField label="Project lead" :error="form.errors.owner_id">
                    <select v-model="form.owner_id" class="field-input">
                        <option value="">Unassigned</option>
                        <option v-for="u in users" :key="u.id" :value="u.id">{{ u.name }}</option>
                    </select>
                </PanelField>
            </div>
        </fieldset>

        <fieldset class="panel-form-section">
            <legend>Timeline &amp; budget</legend>
            <div class="panel-form-grid">
                <PanelField label="Start date" :error="form.errors.starts_on">
                    <input v-model="form.starts_on" type="date" class="field-input" />
                </PanelField>
                <PanelField label="Target end date" :error="form.errors.ends_on">
                    <input v-model="form.ends_on" type="date" class="field-input" />
                </PanelField>
                <PanelField label="Budget" :error="form.errors.budget_amount">
                    <input v-model="form.budget_amount" type="number" step="0.01" min="0" class="field-input" />
                </PanelField>
                <PanelField label="Budget currency" :error="form.errors.budget_currency">
                    <select v-model="form.budget_currency" class="field-input">
                        <option v-for="c in currencies" :key="c" :value="c">{{ c }}</option>
                    </select>
                </PanelField>
            </div>
        </fieldset>

        <fieldset class="panel-form-section">
            <legend>Team</legend>
            <div class="client-contacts">
                <div v-for="(member, i) in form.members" :key="i" class="project-member-row">
                    <PanelField label="Team member" :error="memberError(i, 'user_id')">
                        <select v-model="member.user_id" class="field-input">
                            <option value="" disabled>Choose…</option>
                            <option v-for="u in users" :key="u.id" :value="u.id">{{ u.name }}</option>
                        </select>
                    </PanelField>
                    <PanelField label="Role on project" :error="memberError(i, 'role')">
                        <input v-model="member.role" type="text" class="field-input" placeholder="e.g. Designer, PM" />
                    </PanelField>
                    <button type="button" class="panel-link is-danger" style="padding-bottom: 0.7rem" @click="removeMember(i)">
                        Remove
                    </button>
                </div>
            </div>
            <button type="button" class="panel-link" @click="addMember">+ Add team member</button>
        </fieldset>

        <fieldset class="panel-form-section">
            <legend>Description</legend>
            <PanelField :error="form.errors.description">
                <textarea v-model="form.description" rows="4" class="field-input" placeholder="Scope, goals, notes."></textarea>
            </PanelField>
        </fieldset>

        <fieldset class="panel-form-section">
            <legend>Tags</legend>
            <PanelField :error="form.errors.tags">
                <TagChipsInput v-model="form.tags" :suggestions="allTags" />
            </PanelField>
        </fieldset>

        <div class="panel-form-actions">
            <PanelButton
                variant="secondary"
                :href="isEdit ? route('console.projects.show', project.id) : route('console.projects.index')"
            >
                Cancel
            </PanelButton>
            <PanelButton type="submit" :disabled="form.processing">
                {{ isEdit ? 'Save changes' : 'Create project' }}
            </PanelButton>
        </div>
    </form>
</template>
