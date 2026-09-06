<script setup>
defineProps({
    // [{ key, label }]
    tabs: { type: Array, required: true },
    modelValue: { type: String, required: true },
});

defineEmits(['update:modelValue']);
</script>

<template>
    <div class="flex items-center gap-1 border-b border-line">
        <button
            v-for="t in tabs"
            :key="t.key"
            type="button"
            class="relative -mb-px px-3 py-2.5 text-sm font-medium transition-colors"
            :class="
                modelValue === t.key
                    ? 'text-brand'
                    : 'text-ink-soft hover:text-ink'
            "
            @click="$emit('update:modelValue', t.key)"
        >
            {{ t.label }}
            <span
                v-if="modelValue === t.key"
                class="absolute inset-x-2 -bottom-px h-[2px] rounded-full bg-brand"
            />
        </button>
    </div>
</template>
