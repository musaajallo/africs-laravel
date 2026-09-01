<script setup>
import { ref } from 'vue';
import Modal from '@/Components/Modal.vue';
import PanelButton from '@/Components/Panel/PanelButton.vue';

const props = defineProps({
    title: { type: String, default: 'Are you sure?' },
    message: { type: String, default: '' },
    confirmLabel: { type: String, default: 'Confirm' },
    confirmVariant: { type: String, default: 'danger' },
});

const emit = defineEmits(['confirm']);
const open = ref(false);

function confirm() {
    open.value = false;
    emit('confirm');
}
</script>

<template>
    <span class="panel-confirm">
        <span @click="open = true">
            <slot name="trigger">
                <PanelButton :variant="confirmVariant">{{ confirmLabel }}</PanelButton>
            </slot>
        </span>

        <Modal :show="open" @close="open = false">
            <div class="panel-confirm-body">
                <h2 class="panel-confirm-title">{{ title }}</h2>
                <p v-if="message" class="panel-confirm-message">{{ message }}</p>
                <div class="panel-confirm-actions">
                    <PanelButton variant="secondary" @click="open = false">
                        Cancel
                    </PanelButton>
                    <PanelButton :variant="confirmVariant" @click="confirm">
                        {{ confirmLabel }}
                    </PanelButton>
                </div>
            </div>
        </Modal>
    </span>
</template>
