<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, useForm } from '@inertiajs/vue3';

defineProps({
    status: {
        type: String,
    },
});

const form = useForm({
    email: '',
});

const submit = () => {
    form.post(route('password.email'));
};
</script>

<template>
    <GuestLayout
        heading="Reset your password"
        subheading="Enter your email and we'll send you a link to choose a new password."
    >
        <Head title="Forgot Password" />

        <div v-if="status" class="status-message">
            {{ status }}
        </div>

        <form class="auth-form" @submit.prevent="submit">
            <div class="field">
                <InputLabel for="email" value="Email" />

                <TextInput
                    id="email"
                    type="email"
                    v-model="form.email"
                    required
                    autofocus
                    autocomplete="username"
                />

                <InputError :message="form.errors.email" />
            </div>

            <PrimaryButton class="auth-submit" :disabled="form.processing">
                Email password reset link
            </PrimaryButton>
        </form>
    </GuestLayout>
</template>
