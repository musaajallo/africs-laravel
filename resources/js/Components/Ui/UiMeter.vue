<script setup>
import { computed } from 'vue';

const props = defineProps({
    // 0..1
    value: { type: Number, default: 0 },
    // optional 0..1 target — drawn as a tick
    target: { type: Number, default: null },
    tone: { type: String, default: 'brand' },
    height: { type: Number, default: 8 },
});

const pct = computed(() => Math.max(0, Math.min(1, props.value)) * 100);

const fill = computed(
    () =>
        ({
            brand: 'bg-brand',
            positive: 'bg-positive',
            negative: 'bg-negative',
            gold: 'bg-gold',
            info: 'bg-info',
        })[props.tone] || 'bg-brand',
);
</script>

<template>
    <div
        class="relative overflow-hidden rounded-full bg-line-soft"
        :style="{ height: height + 'px' }"
    >
        <div
            class="h-full rounded-full transition-[width] duration-500"
            :class="fill"
            :style="{ width: pct + '%' }"
        />
        <span
            v-if="target !== null"
            class="absolute top-0 h-full w-[2px] bg-ink/40"
            :style="{ left: `calc(${Math.min(100, target * 100)}% - 1px)` }"
        />
    </div>
</template>
