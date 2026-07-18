<script setup>
import DangerButton from '@/Components/DangerButton.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import Modal from '@/Components/Modal.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { useForm } from '@inertiajs/vue3';
import { nextTick, ref } from 'vue';

const confirmingUserDeletion = ref(false);
const passwordInput = ref(null);

const form = useForm({
    password: '',
});

const confirmUserDeletion = () => {
    confirmingUserDeletion.value = true;

    nextTick(() => passwordInput.value.focus());
};

const deleteUser = () => {
    form.delete(route('profile.destroy'), {
        preserveScroll: true,
        onSuccess: () => closeModal(),
        onError: () => passwordInput.value.focus(),
        onFinish: () => form.reset(),
    });
};

const closeModal = () => {
    confirmingUserDeletion.value = false;

    form.clearErrors();
    form.reset();
};
</script>

<template>
    <section>
        <header>
            <h2 class="panel-title">Delete Account</h2>
            <p class="panel-description">
                Once your account is deleted, all of its resources and data
                will be permanently deleted. Before deleting your account,
                please download any data or information that you wish to
                retain.
            </p>
        </header>

        <div style="margin-top: 1.5rem">
            <DangerButton @click="confirmUserDeletion">
                Delete Account
            </DangerButton>
        </div>

        <Modal :show="confirmingUserDeletion" @close="closeModal">
            <h2 class="panel-title">
                Are you sure you want to delete your account?
            </h2>

            <p class="panel-description">
                Once your account is deleted, all of its resources and data
                will be permanently deleted. Please enter your password to
                confirm you would like to permanently delete your account.
            </p>

            <div class="field">
                <InputLabel for="password" value="Password" class="sr-only" />

                <TextInput
                    id="password"
                    ref="passwordInput"
                    v-model="form.password"
                    type="password"
                    placeholder="Password"
                    @keyup.enter="deleteUser"
                />

                <InputError :message="form.errors.password" />
            </div>

            <div class="form-actions">
                <SecondaryButton @click="closeModal">Cancel</SecondaryButton>

                <DangerButton :disabled="form.processing" @click="deleteUser">
                    Delete Account
                </DangerButton>
            </div>
        </Modal>
    </section>
</template>
