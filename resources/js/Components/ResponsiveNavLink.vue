<script setup>
import { Link } from '@inertiajs/vue3';

defineProps({
    href: {
        type: String,
        required: true,
    },
    active: {
        type: Boolean,
    },
});
</script>

<template>
    <Link
        :href="href"
        class="mobile-nav-link"
        :class="{ 'is-active': active }"
    >
        <slot />
    </Link>
</template>
