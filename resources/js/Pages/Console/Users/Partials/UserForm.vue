<script setup>
import { useForm } from '@inertiajs/vue3';
import PanelField from '@/Components/Panel/PanelField.vue';
import PanelButton from '@/Components/Panel/PanelButton.vue';

const props = defineProps({
    // null for create, the user object for edit
    user: { type: Object, default: null },
    availableRoles: { type: Array, default: () => [] },
});

const isEdit = !!props.user;

const form = useForm({
    name: props.user?.name ?? '',
    username: props.user?.username ?? '',
    email: props.user?.email ?? '',
    password: '',
    password_confirmation: '',
    roles: props.user?.roles ? [...props.user.roles] : [],
});

function toggleRole(role) {
    const i = form.roles.indexOf(role);
    if (i === -1) form.roles.push(role);
    else form.roles.splice(i, 1);
}

function submit() {
    if (isEdit) {
        form.put(route('console.users.update', props.user.id));
    } else {
        form.post(route('console.users.store'));
    }
}
</script>

<template>
    <form class="panel-form" @submit.prevent="submit">
        <div class="panel-form-grid">
            <PanelField label="Full name" :error="form.errors.name" required>
                <input v-model="form.name" type="text" class="field-input" autocomplete="off" />
            </PanelField>

            <PanelField label="Username" :error="form.errors.username" required>
                <input v-model="form.username" type="text" class="field-input" autocomplete="off" />
            </PanelField>

            <PanelField label="Email" :error="form.errors.email" required>
                <input v-model="form.email" type="email" class="field-input" autocomplete="off" />
            </PanelField>
        </div>

        <fieldset class="panel-form-section">
            <legend>{{ isEdit ? 'Reset password' : 'Password' }}</legend>
            <p v-if="isEdit" class="panel-field-hint">
                Leave blank to keep the current password.
            </p>
            <div class="panel-form-grid">
                <PanelField label="Password" :error="form.errors.password" :required="!isEdit">
                    <input v-model="form.password" type="password" class="field-input" autocomplete="new-password" />
                </PanelField>
                <PanelField label="Confirm password" :error="form.errors.password_confirmation">
                    <input v-model="form.password_confirmation" type="password" class="field-input" autocomplete="new-password" />
                </PanelField>
            </div>
        </fieldset>

        <fieldset class="panel-form-section">
            <legend>Roles</legend>
            <PanelField :error="form.errors.roles" hint="Determines which panels and features the user can access.">
                <div class="panel-checkbox-list">
                    <label
                        v-for="role in availableRoles"
                        :key="role"
                        class="panel-checkbox"
                    >
                        <input
                            type="checkbox"
                            :checked="form.roles.includes(role)"
                            @change="toggleRole(role)"
                        />
                        <span>{{ role }}</span>
                    </label>
                </div>
            </PanelField>
        </fieldset>

        <div class="panel-form-actions">
            <PanelButton variant="secondary" :href="route('console.users.index')">
                Cancel
            </PanelButton>
            <PanelButton type="submit" :disabled="form.processing">
                {{ isEdit ? 'Save changes' : 'Create user' }}
            </PanelButton>
        </div>
    </form>
</template>
