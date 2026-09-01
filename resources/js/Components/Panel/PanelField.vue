<script setup>
defineProps({
    label: { type: String, default: null },
    error: { type: String, default: null },
    hint: { type: String, default: null },
    required: { type: Boolean, default: false },
});
</script>

<template>
    <div class="panel-field" :class="{ 'has-error': error }">
        <label v-if="label" class="panel-field-label">
            {{ label }}<span v-if="required" class="panel-field-req">*</span>
        </label>
        <slot />
        <p v-if="hint && !error" class="panel-field-hint">{{ hint }}</p>
        <p v-if="error" class="panel-field-error">{{ error }}</p>
    </div>
</template>
