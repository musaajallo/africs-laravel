<script setup>
import { ref, watch } from 'vue';
import Modal from '@/Components/Modal.vue';
import PanelButton from '@/Components/Panel/PanelButton.vue';
import PanelField from '@/Components/Panel/PanelField.vue';

const props = defineProps({
    show: { type: Boolean, default: false },
});
const emit = defineEmits(['update:show', 'unlocked']);

const password = ref('');
const error = ref('');
const busy = ref(false);

watch(
    () => props.show,
    (open) => {
        if (open) {
            password.value = '';
            error.value = '';
        }
    },
);

async function submit() {
    busy.value = true;
    error.value = '';
    try {
        await window.axios.post(route('console.vault.unlock'), { password: password.value });
        emit('unlocked');
        emit('update:show', false);
    } catch (e) {
        error.value = e.response?.data?.errors?.password?.[0] || 'That password did not match.';
    } finally {
        busy.value = false;
    }
}
</script>

<template>
    <Modal :show="show" @close="emit('update:show', false)">
        <form class="panel-confirm-body" @submit.prevent="submit">
            <h2 class="panel-confirm-title">Unlock the vault</h2>
            <p class="panel-confirm-message">
                Confirm your password to reveal secrets. It stays unlocked for a few minutes.
            </p>
            <PanelField label="Your password" :error="error">
                <input
                    v-model="password"
                    type="password"
                    class="field-input"
                    autocomplete="current-password"
                    autofocus
                />
            </PanelField>
            <div class="panel-confirm-actions">
                <PanelButton type="button" variant="secondary" @click="emit('update:show', false)">Cancel</PanelButton>
                <PanelButton type="submit" :disabled="busy || !password">Unlock</PanelButton>
            </div>
        </form>
    </Modal>
</template>
