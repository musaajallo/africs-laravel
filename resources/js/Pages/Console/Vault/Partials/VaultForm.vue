<script setup>
import { ref } from 'vue';
import PanelField from '@/Components/Panel/PanelField.vue';
import PanelButton from '@/Components/Panel/PanelButton.vue';

const props = defineProps({
    form: { type: Object, required: true },
    folders: { type: Array, default: () => [] },
    submitLabel: { type: String, default: 'Save' },
});
const emit = defineEmits(['submit']);

const showPassword = ref(false);

function err(key) {
    return props.form.errors[key];
}

function generate() {
    const chars = 'ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnpqrstuvwxyz23456789!@#$%^&*-_=+';
    const bytes = new Uint32Array(20);
    crypto.getRandomValues(bytes);
    props.form.password = Array.from(bytes, (b) => chars[b % chars.length]).join('');
    showPassword.value = true;
}

function addCustom() {
    props.form.custom.push({ label: '', value: '', secret: true });
}
function removeCustom(i) {
    props.form.custom.splice(i, 1);
}
</script>

<template>
    <form class="panel-form" @submit.prevent="emit('submit')">
        <div class="panel-card">
            <h2 class="panel-card-title">Entry</h2>
            <div class="panel-form-grid">
                <PanelField label="Title" :error="err('title')" required>
                    <input v-model="form.title" type="text" class="field-input" placeholder="e.g. Acme cPanel" />
                </PanelField>
                <PanelField label="Folder" :error="err('folder_id')">
                    <select v-model="form.folder_id" class="field-input">
                        <option :value="null">Unfiled</option>
                        <option v-for="f in folders" :key="f.id" :value="f.id">{{ f.name }}</option>
                    </select>
                </PanelField>
                <PanelField label="Username" :error="err('username')">
                    <input v-model="form.username" type="text" class="field-input" autocomplete="off" />
                </PanelField>
                <PanelField label="URL" :error="err('url')">
                    <input v-model="form.url" type="text" class="field-input" placeholder="https://" />
                </PanelField>
            </div>

            <PanelField label="Password" :error="err('password')">
                <div class="vault-pw-row">
                    <input
                        v-model="form.password"
                        :type="showPassword ? 'text' : 'password'"
                        class="field-input"
                        autocomplete="new-password"
                    />
                    <button type="button" class="panel-link" @click="showPassword = !showPassword">
                        {{ showPassword ? 'hide' : 'show' }}
                    </button>
                    <button type="button" class="panel-link" @click="generate">generate</button>
                </div>
            </PanelField>

            <PanelField label="2FA seed (TOTP)" :error="err('totp_secret')" hint="Base32 secret; imports as an OTP field.">
                <input v-model="form.totp_secret" type="text" class="field-input" autocomplete="off" />
            </PanelField>

            <PanelField label="Notes" :error="err('notes')">
                <textarea v-model="form.notes" rows="3" class="field-input"></textarea>
            </PanelField>
        </div>

        <div class="panel-card">
            <h2 class="panel-card-title">Custom fields</h2>
            <p v-if="err('custom')" class="panel-field-error">{{ err('custom') }}</p>
            <div v-for="(f, i) in form.custom" :key="i" class="vault-custom-row">
                <input v-model="f.label" type="text" class="field-input" placeholder="Label" style="max-width: 12rem" />
                <input v-model="f.value" type="text" class="field-input" placeholder="Value" />
                <label class="vault-custom-secret">
                    <input v-model="f.secret" type="checkbox" /> secret
                </label>
                <button type="button" class="panel-link is-danger" @click="removeCustom(i)">Remove</button>
            </div>
            <PanelButton type="button" variant="secondary" @click="addCustom">Add field</PanelButton>
        </div>

        <div class="panel-form-actions">
            <PanelButton type="submit" :disabled="form.processing">{{ submitLabel }}</PanelButton>
            <slot name="secondary-action" />
        </div>
    </form>
</template>
