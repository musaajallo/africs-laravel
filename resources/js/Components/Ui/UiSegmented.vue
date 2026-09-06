<script setup>
defineProps({
    // [{ key, label }]
    options: { type: Array, required: true },
    modelValue: { type: [String, Number], required: true },
});

defineEmits(['update:modelValue']);
</script>

<template>
    <div class="inline-flex rounded-md border border-line bg-raised p-0.5">
        <button
            v-for="o in options"
            :key="o.key"
            type="button"
            class="rounded-[6px] px-2.5 py-1 text-xs font-semibold transition-colors"
            :class="
                modelValue === o.key
                    ? 'bg-surface text-ink shadow-card'
                    : 'text-ink-soft hover:text-ink'
            "
            @click="$emit('update:modelValue', o.key)"
        >
            {{ o.label }}
        </button>
    </div>
</template>
