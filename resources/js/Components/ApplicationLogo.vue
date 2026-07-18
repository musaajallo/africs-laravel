<script setup>
defineProps({
    alt: {
        type: String,
        default: 'Africs',
    },
});
</script>

<template>
    <img src="/images/logo.svg" :alt="alt" />
</template>
