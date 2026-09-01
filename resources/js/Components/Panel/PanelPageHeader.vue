<script setup>
defineProps({
    title: { type: String, required: true },
    subtitle: { type: String, default: null },
});
</script>

<template>
    <div class="panel-page-header">
        <div>
            <h1 class="panel-page-title">{{ title }}</h1>
            <p v-if="subtitle" class="panel-page-lead">{{ subtitle }}</p>
        </div>
        <div v-if="$slots.actions" class="panel-page-header-actions">
            <slot name="actions" />
        </div>
    </div>
</template>
