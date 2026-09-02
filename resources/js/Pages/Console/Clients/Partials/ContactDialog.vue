<script setup>
import { ref, watch } from 'vue';
import { useForm } from '@inertiajs/vue3';
import Modal from '@/Components/Modal.vue';
import PanelField from '@/Components/Panel/PanelField.vue';
import PanelButton from '@/Components/Panel/PanelButton.vue';

const props = defineProps({
    clientId: { type: Number, required: true },
    // null → add a new contact; an object → edit that contact
    contact: { type: Object, default: null },
    show: { type: Boolean, default: false },
});

const emit = defineEmits(['close']);

const form = useForm({
    name: '',
    title: '',
    email: '',
    phone: '',
    is_primary: false,
    notes: '',
});

watch(
    () => props.show,
    (open) => {
        if (!open) return;
        form.clearErrors();
        form.defaults({
            name: props.contact?.name ?? '',
            title: props.contact?.title ?? '',
            email: props.contact?.email ?? '',
            phone: props.contact?.phone ?? '',
            is_primary: props.contact?.is_primary ?? false,
            notes: props.contact?.notes ?? '',
        });
        form.reset();
    },
);

const isEdit = ref(false);
watch(() => props.contact, (c) => (isEdit.value = !!c), { immediate: true });

function submit() {
    const opts = { preserveScroll: true, onSuccess: () => emit('close') };
    if (props.contact) {
        form.put(route('console.clients.contacts.update', [props.clientId, props.contact.id]), opts);
    } else {
        form.post(route('console.clients.contacts.store', props.clientId), opts);
    }
}
</script>

<template>
    <Modal :show="show" @close="emit('close')">
        <form class="panel-confirm-body" @submit.prevent="submit">
            <h2 class="panel-confirm-title">{{ isEdit ? 'Edit contact' : 'Add contact' }}</h2>

            <div class="panel-form-grid" style="margin-top: 1.1rem">
                <PanelField label="Name" :error="form.errors.name" required>
                    <input v-model="form.name" type="text" class="field-input" autofocus />
                </PanelField>
                <PanelField label="Job title" :error="form.errors.title">
                    <input v-model="form.title" type="text" class="field-input" />
                </PanelField>
                <PanelField label="Email" :error="form.errors.email">
                    <input v-model="form.email" type="email" class="field-input" />
                </PanelField>
                <PanelField label="Phone" :error="form.errors.phone">
                    <input v-model="form.phone" type="text" class="field-input" />
                </PanelField>
            </div>

            <PanelField :error="form.errors.notes" style="margin-top: 1rem">
                <textarea v-model="form.notes" rows="2" class="field-input" placeholder="Notes about this contact"></textarea>
            </PanelField>

            <label class="panel-checkbox" style="margin-top: 1rem">
                <input v-model="form.is_primary" type="checkbox" />
                <span>Primary contact for this client</span>
            </label>

            <div class="panel-confirm-actions">
                <PanelButton variant="secondary" @click="emit('close')">Cancel</PanelButton>
                <PanelButton type="submit" :disabled="form.processing">
                    {{ isEdit ? 'Save' : 'Add contact' }}
                </PanelButton>
            </div>
        </form>
    </Modal>
</template>
