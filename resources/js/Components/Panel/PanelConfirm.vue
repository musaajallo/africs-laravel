<script setup>
import { computed, ref } from 'vue';
import Modal from '@/Components/Modal.vue';
import PanelButton from '@/Components/Panel/PanelButton.vue';

const props = defineProps({
    title: { type: String, default: 'Are you sure?' },
    message: { type: String, default: '' },
    confirmLabel: { type: String, default: 'Confirm' },
    confirmVariant: { type: String, default: 'danger' },
    // Optional v-model. When bound, the parent owns visibility and no trigger
    // is rendered — used when the trigger lives elsewhere (e.g. an actions
    // menu). When unbound, the component shows its own trigger.
    modelValue: { default: undefined },
});

const emit = defineEmits(['confirm', 'update:modelValue']);

const internal = ref(false);
const controlled = computed(() => props.modelValue !== undefined);
const show = computed(() => (controlled.value ? !!props.modelValue : internal.value));

function setOpen(value) {
    if (controlled.value) emit('update:modelValue', value);
    else internal.value = value;
}

function confirm() {
    setOpen(false);
    emit('confirm');
}
</script>

<template>
    <span class="panel-confirm">
        <span v-if="!controlled" @click="setOpen(true)">
            <slot name="trigger">
                <PanelButton :variant="confirmVariant">{{ confirmLabel }}</PanelButton>
            </slot>
        </span>

        <Modal :show="show" @close="setOpen(false)">
            <div class="panel-confirm-body">
                <h2 class="panel-confirm-title">{{ title }}</h2>
                <p v-if="message" class="panel-confirm-message">{{ message }}</p>
                <div class="panel-confirm-actions">
                    <PanelButton variant="secondary" @click="setOpen(false)">
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
