<script setup>
defineProps({
    message: {
        type: String,
    },
});
</script>

<template>
    <p v-show="message" class="field-error">
        {{ message }}
    </p>
</template>
