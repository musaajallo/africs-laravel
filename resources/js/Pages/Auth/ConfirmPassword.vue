<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, useForm } from '@inertiajs/vue3';

const form = useForm({
    password: '',
});

const submit = () => {
    form.post(route('password.confirm'), {
        onFinish: () => form.reset(),
    });
};
</script>

<template>
    <GuestLayout
        heading="Confirm your password"
        subheading="This is a secure area — please re-enter your password to continue."
    >
        <Head title="Confirm Password" />

        <form class="auth-form" @submit.prevent="submit">
            <div class="field">
                <InputLabel for="password" value="Password" />
                <TextInput
                    id="password"
                    type="password"
                    v-model="form.password"
                    required
                    autocomplete="current-password"
                    autofocus
                />
                <InputError :message="form.errors.password" />
            </div>

            <PrimaryButton class="auth-submit" :disabled="form.processing">
                Confirm
            </PrimaryButton>
        </form>
    </GuestLayout>
</template>
