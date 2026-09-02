<script setup>
defineProps({
    name: { type: String, required: true },
    color: { type: String, default: 'gray' },
});
</script>

<template>
    <span class="tag-badge" :data-color="color">{{ name }}</span>
</template>
