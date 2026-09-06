<script setup>
import { computed } from 'vue';

const props = defineProps({
    // [{ label, value, caption? }]
    data: { type: Array, default: () => [] },
    tone: { type: String, default: 'brand' },
    empty: { type: String, default: 'No data for this period.' },
});

const max = computed(() =>
    Math.max(1, ...props.data.map((d) => Number(d.value) || 0)),
);

const barClass = computed(
    () =>
        ({
            brand: 'bg-brand',
            positive: 'bg-positive',
            info: 'bg-info',
            gold: 'bg-gold',
        })[props.tone] || 'bg-brand',
);
</script>

<template>
    <div v-if="data.length" class="flex flex-col gap-2.5">
        <div v-for="(d, i) in data" :key="i" class="flex flex-col gap-1">
            <div class="flex items-baseline justify-between gap-2 text-xs">
                <span class="truncate font-medium text-ink">{{ d.label }}</span>
                <span class="shrink-0 tabular-nums text-ink-soft">
                    {{ d.caption ?? d.value }}
                </span>
            </div>
            <div class="h-1.5 overflow-hidden rounded-full bg-line-soft">
                <div
                    class="h-full rounded-full"
                    :class="barClass"
                    :style="{ width: `${Math.max(2, ((Number(d.value) || 0) / max) * 100)}%` }"
                />
            </div>
        </div>
    </div>
    <p v-else class="text-xs text-ink-faint">{{ empty }}</p>
</template>
