<script setup>
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import { Link } from '@inertiajs/vue3';
</script>

<template>
    <div class="guest-layout">
        <Link href="/">
            <ApplicationLogo class="guest-logo" />
        </Link>

        <div class="auth-card">
            <slot />
        </div>
    </div>
</template>
