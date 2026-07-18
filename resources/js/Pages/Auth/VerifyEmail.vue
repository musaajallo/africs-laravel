<script setup>
import { computed } from 'vue';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps({
    status: {
        type: String,
    },
});

const form = useForm({});

const submit = () => {
    form.post(route('verification.send'));
};

const verificationLinkSent = computed(
    () => props.status === 'verification-link-sent',
);
</script>

<template>
    <GuestLayout>
        <Head title="Email Verification" />

        <p class="panel-description" style="margin-bottom: 1rem">
            Thanks for signing up! Before getting started, could you verify
            your email address by clicking on the link we just emailed to
            you? If you didn't receive the email, we will gladly send you
            another.
        </p>

        <div v-if="verificationLinkSent" class="status-message">
            A new verification link has been sent to the email address you
            provided during registration.
        </div>

        <form @submit.prevent="submit">
            <div class="form-actions" style="justify-content: space-between">
                <PrimaryButton :disabled="form.processing">
                    Resend Verification Email
                </PrimaryButton>

                <Link :href="route('logout')" method="post" as="button" class="link">
                    Log Out
                </Link>
            </div>
        </form>
    </GuestLayout>
</template>
