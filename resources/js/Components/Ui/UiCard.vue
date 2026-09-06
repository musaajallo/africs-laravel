<script setup>
defineProps({
    title: { type: String, default: '' },
    subtitle: { type: String, default: '' },
    // false removes the body padding (for edge-to-edge tables/lists)
    flush: { type: Boolean, default: false },
});
</script>

<template>
    <section
        class="flex flex-col rounded-card border border-line bg-surface shadow-card"
    >
        <header
            v-if="title || $slots.actions || $slots.title"
            class="flex items-start justify-between gap-3 px-4 pb-2 pt-3.5"
        >
            <div class="min-w-0">
                <slot name="title">
                    <h3 class="truncate text-[13px] font-semibold text-ink">
                        {{ title }}
                    </h3>
                </slot>
                <p v-if="subtitle" class="mt-0.5 text-xs text-ink-faint">
                    {{ subtitle }}
                </p>
            </div>
            <div v-if="$slots.actions" class="shrink-0"><slot name="actions" /></div>
        </header>

        <div :class="flush ? '' : 'px-4 pb-4 pt-1'">
            <slot />
        </div>
    </section>
</template>
